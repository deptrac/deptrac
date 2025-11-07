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
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasImportTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
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

        /** @throws void */
        return $fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $scope->getClassReflection()?->getName(),
            $scope->getTraitReflection()?->getName(),
            $scope->getFunction()?->getName(),
            $docComment->getText(),
        );
    }

    /**
     * @param list<string> $tokenTemplateLikes
     *
     * @return ?array{PhpDocNode, list<string>}
     */
    public static function resolvePHPDocWithNativeScope(Node $node, Lexer $lexer, PhpDocParser $docParser, array $tokenTemplateLikes,
    ): ?array {
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return null;
        }

        $tokens = new TokenIterator($lexer->tokenize($docComment->getText()));
        $docNode = $docParser->parse($tokens);
        $templateTypes = array_merge(self::getTagsIntroducingIgnoredNames($docNode), $tokenTemplateLikes);

        return [$docNode, $templateTypes];
    }

    /**
     * These tags produce "names" or tokens that should be ignored by Deptrac.
     *
     * @return list<string>
     */
    private static function getTagsIntroducingIgnoredNames(PhpDocNode $docNode): array
    {
        $templateNames =
            array_map(static fn (TemplateTagValueNode $tag): string => $tag->name,
                $docNode->getTemplateTagValues() + $docNode->getTemplateTagValues('@template-covariant'));
        $aliasNames = array_map(static fn (TypeAliasTagValueNode $tag): string => $tag->alias, $docNode->getTypeAliasTagValues());
        $importNames = array_map(static fn (TypeAliasImportTagValueNode $tag): string => $tag->importedAs ?? $tag->importedAlias, $docNode->getTypeAliasImportTagValues());

        return array_values($templateNames + $aliasNames + $importNames);
    }
}
