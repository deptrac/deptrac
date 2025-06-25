<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Fixtures;

use Foo\{Exception, RuntimeException};
use LogicException;

use function get_class;

use const PHP_EOL;

final class Issue319
{
    public function getClassName(): string
    {
        return get_class($this) . PHP_EOL;
    }

    public function error(): void
    {
        throw new LogicException();
    }
}
