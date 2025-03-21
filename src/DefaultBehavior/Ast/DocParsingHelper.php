<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast;

use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;

class DocParsingHelper
{
    public static function resolvePHPDocWithPHPStanScope(Node $node, PhpStanContainerDecorator $phpStanContainer, MutatingScope $scope): ?ResolvedPhpDocBlock
    {
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return null;
        }

        $fileTypeMapper = $phpStanContainer->createFileTypeMapper();
        $classReflection = $scope->getClassReflection();
        assert(null !== $classReflection);

        /** @throws void */
        return $fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $classReflection->getName(),
            $scope->getTraitReflection()?->getName(),
            $scope->getFunction()?->getName(),
            $docComment->getText(),
        );
    }

    /**
     * @param list<string> $tokenTemplates
     *
     * @return ?array{PhpDocNode, list<string>}
     */
    public static function resolvePHPDocWithNativeScope(Node $node, Lexer $lexer, PhpDocParser $docParser, array $tokenTemplates): ?array
    {
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return null;
        }

        $tokens = new TokenIterator($lexer->tokenize($docComment->getText()));
        $docNode = $docParser->parse($tokens);
        $templateTypes = array_values(array_merge(
            array_map(
                static fn (TemplateTagValueNode $node): string => $node->name,
                $docNode->getTemplateTagValues()
            ),
            $tokenTemplates
        ));

        return [$docNode, $templateTypes];
    }
}
