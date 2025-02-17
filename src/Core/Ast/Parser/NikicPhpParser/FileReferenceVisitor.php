<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser\NikicPhpParser;

use DEPTRAC_INTERNAL\PhpParser\Node;
use DEPTRAC_INTERNAL\PhpParser\Node\Identifier;
use DEPTRAC_INTERNAL\PhpParser\Node\Name;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\Class_;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\ClassLike;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\GroupUse;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\Interface_;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\Namespace_;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\Trait_;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\Use_;
use DEPTRAC_INTERNAL\PhpParser\NodeVisitorAbstract;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Lexer\Lexer;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Parser\ConstExprParser;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Parser\PhpDocParser;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Parser\TokenIterator;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Parser\TypeParser;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\ReferenceExtractorInterface;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\Core\Ast\Parser\TypeScope;
class FileReferenceVisitor extends NodeVisitorAbstract
{
    /** @var ReferenceExtractorInterface[] */
    private readonly array $dependencyResolvers;
    private TypeScope $currentTypeScope;
    private readonly Lexer $lexer;
    private readonly PhpDocParser $docParser;
    private ReferenceBuilder $currentReference;
    public function __construct(private readonly FileReferenceBuilder $fileReferenceBuilder, private readonly TypeResolver $typeResolver, ReferenceExtractorInterface ...$dependencyResolvers)
    {
        $this->currentTypeScope = new TypeScope('');
        $this->lexer = new Lexer();
        $this->docParser = new PhpDocParser(new TypeParser(), new ConstExprParser());
        $this->dependencyResolvers = $dependencyResolvers;
        $this->currentReference = $fileReferenceBuilder;
    }
    /**
     * @return list<string>
     */
    private function templatesFromDocs(Node $node) : array
    {
        $docComment = $node->getDocComment();
        if (null === $docComment) {
            return [];
        }
        $docText = $docComment->getText();
        // prevent expensive parsing, when not templates involved.
        if (!\str_contains($docText, '@template')) {
            return [];
        }
        $tokens = new TokenIterator($this->lexer->tokenize($docText));
        $docNode = $this->docParser->parse($tokens);
        return \array_values(\array_map(static fn(TemplateTagValueNode $tag): string => $tag->name, $docNode->getTemplateTagValues()));
    }
    public function enterNode(Node $node)
    {
        match (\true) {
            $node instanceof Namespace_ => $this->currentTypeScope = new TypeScope($node->name ? $node->name->toCodeString() : ''),
            $node instanceof Node\Stmt\Function_ => $this->enterFunction($node),
            $node instanceof ClassLike => $this->enterClassLike($node),
            $node instanceof Node\FunctionLike => $this->enterFunctionLike($node),
            default => null,
        };
        return null;
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\FunctionLike) {
            foreach ($this->templatesFromDocs($node) as $template) {
                $this->currentReference->removeTokenTemplate($template);
            }
        }
        match (\true) {
            $node instanceof Node\Stmt\Function_ => $this->currentReference = $this->fileReferenceBuilder,
            $node instanceof ClassLike && null !== $this->getClassReferenceName($node) => $this->currentReference = $this->fileReferenceBuilder,
            $node instanceof Use_ && Use_::TYPE_NORMAL === $node->type => $this->leaveUse($node),
            $node instanceof GroupUse => $this->leaveGroupUse($node),
            default => null,
        };
        foreach ($this->dependencyResolvers as $resolver) {
            $resolver->processNode($node, $this->currentReference, $this->currentTypeScope);
        }
        return null;
    }
    /**
     * @return ?array{PhpDocNode, int} DocNode, comment start line
     */
    private function getDocNodeCrate(Node $node) : ?array
    {
        $docComment = $node->getDocComment();
        if (null === $docComment) {
            return null;
        }
        $tokens = new TokenIterator($this->lexer->tokenize($docComment->getText()));
        return [$this->docParser->parse($tokens), $docComment->getStartLine()];
    }
    private function enterClassLike(ClassLike $node) : void
    {
        $name = $this->getClassReferenceName($node);
        $docNodeCrate = $this->getDocNodeCrate($node);
        if (null !== $name) {
            $tags = [];
            if (null !== $docNodeCrate) {
                $tags = $this->extractTags($docNodeCrate[0]);
            }
            match (\true) {
                $node instanceof Interface_ => $this->enterInterface($name, $node, $tags),
                $node instanceof Class_ => $this->enterClass($name, $node, $tags),
                $node instanceof Trait_ => $this->currentReference = $this->fileReferenceBuilder->newTrait($name, $this->templatesFromDocs($node), $tags),
                default => $this->currentReference = $this->fileReferenceBuilder->newClassLike($name, $this->templatesFromDocs($node), $tags),
            };
        }
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                foreach ($this->typeResolver->resolvePHPParserTypes($this->currentTypeScope, $attribute->name) as $classLikeName) {
                    $this->currentReference->attribute($classLikeName, $attribute->getLine());
                }
            }
        }
        if (null !== $docNodeCrate) {
            $this->processClassLikeDocs($docNodeCrate);
        }
    }
    private function enterFunction(Node\Stmt\Function_ $node) : void
    {
        if (isset($node->namespacedName)) {
            $namespacedName = $node->namespacedName;
            $name = $namespacedName->toCodeString();
        } else {
            $name = $node->name->toString();
        }
        $docNodeCrate = $this->getDocNodeCrate($node);
        $tags = [];
        if (null !== $docNodeCrate) {
            $tags = $this->extractTags($docNodeCrate[0]);
        }
        $this->currentReference = $this->fileReferenceBuilder->newFunction($name, $this->templatesFromDocs($node), $tags);
        foreach ($node->getParams() as $param) {
            if (null !== $param->type) {
                foreach ($this->typeResolver->resolvePHPParserTypes($this->currentTypeScope, $param->type) as $classLikeName) {
                    $this->currentReference->parameter($classLikeName, $param->getLine());
                }
            }
        }
        $returnType = $node->getReturnType();
        if (null !== $returnType) {
            foreach ($this->typeResolver->resolvePHPParserTypes($this->currentTypeScope, $returnType) as $classLikeName) {
                $this->currentReference->returnType($classLikeName, $returnType->getLine());
            }
        }
        foreach ($node->getAttrGroups() as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                foreach ($this->typeResolver->resolvePHPParserTypes($this->currentTypeScope, $attribute) as $classLikeName) {
                    $this->currentReference->attribute($classLikeName, $attribute->getLine());
                }
            }
        }
    }
    private function getClassReferenceName(ClassLike $node) : ?string
    {
        if (isset($node->namespacedName)) {
            return $node->namespacedName->toCodeString();
        }
        if ($node->name instanceof Identifier) {
            return $node->name->toString();
        }
        return null;
    }
    /**
     * @param array<string,list<string>> $tags
     */
    private function enterInterface(string $name, Interface_ $node, array $tags) : void
    {
        $this->currentReference = $this->fileReferenceBuilder->newInterface($name, $this->templatesFromDocs($node), $tags);
        foreach ($node->extends as $extend) {
            $this->currentReference->implements($extend->toCodeString(), $extend->getLine());
        }
    }
    /**
     * @param array<string,list<string>> $tags
     */
    private function enterClass(string $name, Class_ $node, array $tags) : void
    {
        $this->currentReference = $this->fileReferenceBuilder->newClass($name, $this->templatesFromDocs($node), $tags);
        if ($node->extends instanceof Name) {
            $this->currentReference->extends($node->extends->toCodeString(), $node->extends->getLine());
        }
        foreach ($node->implements as $implement) {
            $this->currentReference->implements($implement->toCodeString(), $implement->getLine());
        }
    }
    private function enterFunctionLike(Node\FunctionLike $node) : void
    {
        foreach ($this->templatesFromDocs($node) as $template) {
            $this->currentReference->addTokenTemplate($template);
        }
    }
    private function leaveUse(Use_ $node) : void
    {
        foreach ($node->uses as $use) {
            $this->currentTypeScope->addUse($use->name->toString(), $use->getAlias()->toString());
            $this->fileReferenceBuilder->useStatement($use->name->toString(), $use->name->getLine());
        }
    }
    private function leaveGroupUse(GroupUse $node) : void
    {
        foreach ($node->uses as $use) {
            if (Use_::TYPE_NORMAL === $use->type) {
                $classLikeName = $node->prefix->toString() . '\\' . $use->name->toString();
                $this->currentTypeScope->addUse($classLikeName, $use->getAlias()->toString());
                $this->fileReferenceBuilder->useStatement($classLikeName, $use->name->getLine());
            }
        }
    }
    /**
     * @param array{PhpDocNode, int} $docNodeCrate
     */
    private function processClassLikeDocs(array $docNodeCrate) : void
    {
        [$docNode, $line] = $docNodeCrate;
        foreach ($docNode->getMethodTagValues() as $methodTagValue) {
            $templateTypes = \array_merge(\array_map(static fn(TemplateTagValueNode $node): string => $node->name, $methodTagValue->templateTypes), $this->currentReference->getTokenTemplates());
            foreach ($methodTagValue->parameters as $tag) {
                if (null !== $tag->type) {
                    $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $this->currentTypeScope, $templateTypes);
                    foreach ($types as $type) {
                        $this->currentReference->parameter($type, $line);
                    }
                }
            }
            $returnType = $methodTagValue->returnType;
            if (null !== $returnType) {
                $types = $this->typeResolver->resolvePHPStanDocParserType($returnType, $this->currentTypeScope, $templateTypes);
                foreach ($types as $type) {
                    $this->currentReference->returnType($type, $line);
                }
            }
        }
        /** @var list<PropertyTagValueNode> $propertyTags */
        $propertyTags = \array_merge($docNode->getPropertyTagValues(), $docNode->getPropertyReadTagValues(), $docNode->getPropertyWriteTagValues());
        foreach ($propertyTags as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $this->currentTypeScope, $this->currentReference->getTokenTemplates());
            foreach ($types as $type) {
                $this->currentReference->variable($type, $line);
            }
        }
    }
    /**
     * @return array<string,list<string>>
     */
    private function extractTags(PhpDocNode $doc) : array
    {
        $tags = [];
        foreach ($doc->getTags() as $tag) {
            $tags[$tag->name][] = (string) $tag->value;
        }
        return $tags;
    }
}
