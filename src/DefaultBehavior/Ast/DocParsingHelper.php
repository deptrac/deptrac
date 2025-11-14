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
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

class DocParsingHelper
{
    /**
     * @return array{Lexer, PhpDocParser}
     */
    public static function create(): array
    {
        if (class_exists(ParserConfig::class)) {
            $config = new ParserConfig(usedAttributes: ['lines' => true, 'indexes' => true]);
            $lexer = new Lexer($config);
            $constExprParser = new ConstExprParser($config);
            $docParser = new PhpDocParser($config, new TypeParser($config, $constExprParser), $constExprParser);
        } else {
            // For phpstan/phpdoc-parser v1

            /**
             * @psalm-suppress TooFewArguments
             * @psalm-suppress InvalidArgument
             *
             * @phpstan-ignore-next-line
             */
            $lexer = new Lexer();

            /**
             * @psalm-suppress TooFewArguments
             * @psalm-suppress InvalidArgument
             *
             * @phpstan-ignore-next-line
             */
            $docParser = new PhpDocParser(new TypeParser(), new ConstExprParser());
        }

        return [$lexer, $docParser];
    }

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
