<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers;

use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

class NikicFileReferenceVisitor extends NodeVisitorAbstract
{
    /** @var NikicReferenceExtractorInterface<Node>[] */
    private readonly array $dependencyResolvers;

    private TypeScope $currentTypeScope;
    private readonly Lexer $lexer;
    private readonly PhpDocParser $docParser;

    private ReferenceBuilder $currentReference;

    /**
     * @param NikicReferenceExtractorInterface<Node> ...$dependencyResolvers
     */
    public function __construct(
        private readonly FileReferenceBuilder $fileReferenceBuilder,
        NikicReferenceExtractorInterface ...$dependencyResolvers,
    ) {
        $this->currentTypeScope = new TypeScope('');
        $config = new ParserConfig(usedAttributes: ['lines' => true, 'indexes' => true]);
        $this->lexer = new Lexer($config);
        $constExprParser = new ConstExprParser($config);
        $this->docParser = new PhpDocParser($config, new TypeParser($config, $constExprParser), $constExprParser);
        $this->dependencyResolvers = $dependencyResolvers;
        $this->currentReference = $fileReferenceBuilder;
    }

    /**
     * @return list<string>
     */
    private function templateLikesFromDocs(Node $node): array
    {
        $docComment = $node->getDocComment();
        if (null === $docComment) {
            return [];
        }
        $docText = $docComment->getText();

        // prevent expensive parsing, when not templates involved.
        if (!str_contains($docText, '@template')
            && !str_contains($docText, '@template-covariant')
            && !str_contains($docText, '@phpstan-type')
        ) {
            return [];
        }

        $tokens = new TokenIterator($this->lexer->tokenize($docText));
        $docNode = $this->docParser->parse($tokens);

        $templateNames =
            array_map(static fn (TemplateTagValueNode $tag): string => $tag->name,
                $docNode->getTemplateTagValues() + $docNode->getTemplateTagValues('@template-covariant'));
        $importNames = array_map(static fn (TypeAliasTagValueNode $tag): string => $tag->alias, $docNode->getTypeAliasTagValues());

        return array_values($templateNames + $importNames);
    }

    public function enterNode(Node $node)
    {
        match (true) {
            $node instanceof Namespace_ => $this->currentTypeScope = new TypeScope($node->name ? $node->name->toCodeString() : ''),
            $node instanceof ClassLike, $node instanceof Node\Stmt\Function_ => $this->enterReferenceChangingNode($node),
            $node instanceof Node\FunctionLike => $this->enterFunctionLike($node),
            $node instanceof Use_ && Use_::TYPE_NORMAL === $node->type => $this->enterUse($node),
            $node instanceof GroupUse => $this->enterGroupUse($node),
            default => null,
        };

        return null;
    }

    public function leaveNode(Node $node)
    {
        foreach ($this->dependencyResolvers as $resolver) {
            if ($node instanceof ($resolver->getNodeType())) {
                $resolver->processNode($node, $this->currentReference, $this->currentTypeScope);
            }
        }

        if ($node instanceof Node\FunctionLike) {
            foreach ($this->templateLikesFromDocs($node) as $template) {
                $this->currentReference->removeTokenTemplateLike($template);
            }
        }

        $this->currentReference = match (true) {
            $node instanceof Node\Stmt\Function_ => $this->fileReferenceBuilder,
            $node instanceof ClassLike && null !== $this->getReferenceName($node) => $this->fileReferenceBuilder,
            default => $this->currentReference,
        };

        return null;
    }

    private function enterReferenceChangingNode(Node\Stmt\Function_|ClassLike $node): void
    {
        $name = $this->getReferenceName($node);
        if (null !== $name) {
            $tags = $this->getTags($node);
            $templateTypes = $this->templateLikesFromDocs($node);

            $this->currentReference = match (true) {
                $node instanceof Node\Stmt\Function_ => $this->fileReferenceBuilder->newFunction($name, $templateTypes, $tags),
                $node instanceof Interface_ => $this->fileReferenceBuilder->newInterface($name, $templateTypes, $tags),
                $node instanceof Class_ => $this->fileReferenceBuilder->newClass($name, $templateTypes, $tags),
                $node instanceof Trait_ => $this->fileReferenceBuilder->newTrait($name, $templateTypes, $tags),
                default => $this->fileReferenceBuilder->newClassLike($name, $templateTypes, $tags),
            };
        }
    }

    private function getReferenceName(ClassLike|Node\Stmt\Function_ $node): ?string
    {
        if (isset($node->namespacedName)) {
            return $node->namespacedName->toCodeString();
        }

        if ($node->name instanceof Identifier) {
            return $node->name->toString();
        }

        return null;
    }

    private function enterFunctionLike(Node\FunctionLike $node): void
    {
        foreach ($this->templateLikesFromDocs($node) as $template) {
            $this->currentReference->addTokenTemplateLike($template);
        }
    }

    private function enterUse(Use_ $node): void
    {
        foreach ($node->uses as $use) {
            $this->currentTypeScope->addUse($use->name->toString(), $use->getAlias()->toString());
        }
    }

    private function enterGroupUse(GroupUse $node): void
    {
        foreach ($node->uses as $use) {
            if (Use_::TYPE_NORMAL === $use->type) {
                $classLikeName = $node->prefix->toString().'\\'.$use->name->toString();
                $this->currentTypeScope->addUse($classLikeName, $use->getAlias()->toString());
            }
        }
    }

    /**
     * @return array<string,list<string>>
     */
    private function getTags(ClassLike|Node\Stmt\Function_ $node): array
    {
        $docComment = $node->getDocComment();
        if (null === $docComment) {
            return [];
        }

        $tokens = new TokenIterator($this->lexer->tokenize($docComment->getText()));
        $docNodeCrate = $this->docParser->parse($tokens);

        $tags = [];
        foreach ($docNodeCrate->getTags() as $tag) {
            $tags[$tag->name][] = (string) $tag->value;
        }

        return $tags;
    }
}
