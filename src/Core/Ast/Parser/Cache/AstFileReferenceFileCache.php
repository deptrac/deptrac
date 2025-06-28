<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Ast\Parser\Cache;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstInherit;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Supportive\File\Exception\CouldNotReadFileException;
use Deptrac\Deptrac\Supportive\File\Exception\FileNotExistsException;
use Deptrac\Deptrac\Supportive\File\FileReader;

use function array_filter;
use function array_map;
use function assert;
use function dirname;
use function file_exists;
use function is_readable;
use function is_writable;
use function json_decode;
use function json_encode;
use function realpath;
use function sha1_file;
use function unserialize;

class AstFileReferenceFileCache implements AstFileReferenceDeferredCacheInterface
{
    /** @var array<string, array{hash: string, reference: FileReference}> */
    private array $cache = [];
    private bool $loaded = false;
    /** @var array<string, bool> */
    private array $parsedFiles = [];

    public function __construct(private readonly string $cacheFile, private readonly string $cacheVersion) {}

    public function get(string $filepath): ?FileReference
    {
        $this->load();

        /** @throws void */
        $filepath = $this->normalizeFilepath($filepath);

        /** @throws void */
        if ($this->has($filepath)) {
            $this->parsedFiles[$filepath] = true;

            return $this->cache[$filepath]['reference'];
        }

        return null;
    }

    public function set(FileReference $fileReference): void
    {
        $this->load();

        /** @throws void */
        $filepath = $this->normalizeFilepath($fileReference->filepath);

        $this->parsedFiles[$filepath] = true;

        $this->cache[$filepath] = [
            'hash' => (string) sha1_file($filepath),
            'reference' => $fileReference,
        ];
    }

    public function load(): void
    {
        if (true === $this->loaded) {
            return;
        }

        if (!file_exists($this->cacheFile) || !is_readable($this->cacheFile)) {
            return;
        }

        try {
            $contents = FileReader::read($this->cacheFile);
        } catch (CouldNotReadFileException) {
            return;
        }

        /** @var ?array{version: string, payload: array<string, array{hash: string, reference: string}>} $cache */
        $cache = json_decode($contents, true);

        $this->loaded = true;

        if (null === $cache || $this->cacheVersion !== $cache['version']) {
            return;
        }

        $this->cache = array_map(
            /** @param array{hash: string, reference: string} $data */
            static function (array $data): array {
                $reference = unserialize(
                    $data['reference'],
                    [
                        'allowed_classes' => [
                            FileReference::class,
                            ClassLikeReference::class,
                            FunctionReference::class,
                            VariableReference::class,
                            AstInherit::class,
                            DependencyToken::class,
                            DependencyType::class,
                            FileToken::class,
                            ClassLikeToken::class,
                            ClassLikeType::class,
                            FunctionToken::class,
                            SuperGlobalToken::class,
                            FileOccurrence::class,
                            DependencyContext::class,
                        ],
                    ]
                );
                assert($reference instanceof FileReference);

                return [
                    'hash' => $data['hash'],
                    'reference' => $reference,
                ];
            },
            $cache['payload']
        );
    }

    public function write(): void
    {
        if (!is_writable(dirname($this->cacheFile))) {
            return;
        }

        $cache = array_filter(
            $this->cache,
            fn (string $key): bool => isset($this->parsedFiles[$key]),
            ARRAY_FILTER_USE_KEY
        );

        $payload = array_map(
            static function (array $data): array {
                $data['reference'] = serialize($data['reference']);

                return $data;
            },
            $cache
        );

        file_put_contents(
            $this->cacheFile,
            json_encode(
                [
                    'version' => $this->cacheVersion,
                    'payload' => $payload,
                ]
            )
        );
    }

    /**
     * @throws FileNotExistsException
     */
    private function has(string $filepath): bool
    {
        $this->load();

        $filepath = $this->normalizeFilepath($filepath);

        if (!isset($this->cache[$filepath])) {
            return false;
        }

        $hash = sha1_file($filepath);

        if ($hash !== $this->cache[$filepath]['hash']) {
            unset($this->cache[$filepath]);

            return false;
        }

        return true;
    }

    /**
     * @throws FileNotExistsException
     */
    private function normalizeFilepath(string $filepath): string
    {
        $normalized = realpath($filepath);

        if (false === $normalized) {
            throw FileNotExistsException::fromFilePath($filepath);
        }

        return $normalized;
    }
}
