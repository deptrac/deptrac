<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

use InvalidArgumentException;

use function get_debug_type;
use function is_scalar;
use function sprintf;

/**
 * Token for references recorded by third-party extractors.
 *
 * It carries plain data only - scalars, null and arrays thereof - so the AST
 * cache can restore it without ever unserializing extension-defined classes.
 * Extensions identify their tokens by a vendor-namespaced $type (e.g.
 * "acme/my-extension.route") and rebuild richer value objects from $payload
 * at analysis time.
 *
 * @psalm-immutable
 */
final class CustomToken implements TokenInterface
{
    /**
     * @param string $type vendor-namespaced tag identifying the kind of token
     * @param array<array-key, mixed> $payload scalars, null and arrays thereof only
     * @param string $display human readable representation, used in reports
     *
     * @throws InvalidArgumentException when the payload contains anything but scalars, null and arrays thereof
     */
    public function __construct(
        public readonly string $type,
        public readonly array $payload,
        private readonly string $display,
    ) {
        self::ensureDataOnly($payload);
    }

    public function toString(): string
    {
        return $this->display;
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self
            && $this->type === $token->type
            && $this->payload === $token->payload;
    }

    /**
     * unserialize() bypasses the constructor, so re-check the invariant when
     * an instance is restored from the AST cache; a throwing __wakeup() makes
     * the cache discard the entry.
     *
     * @throws InvalidArgumentException
     */
    public function __wakeup(): void
    {
        self::ensureDataOnly($this->payload);
    }

    /**
     * @param array<array-key, mixed> $payload
     *
     * @throws InvalidArgumentException
     */
    private static function ensureDataOnly(array $payload): void
    {
        array_walk_recursive(
            $payload,
            static function (mixed $value): void {
                if (null !== $value && !is_scalar($value)) {
                    throw new InvalidArgumentException(sprintf('CustomToken payloads may only contain scalars, null and arrays thereof, %s given.', get_debug_type($value)));
                }
            }
        );
    }
}
