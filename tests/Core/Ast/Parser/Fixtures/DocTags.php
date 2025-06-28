<?php

declare(strict_types = 1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\NikicPhpParser\Fixtures;

class UntaggedThing
{
    public function untaggedFunction() {}
}

/**
 * @internal
 * @note Note one
 * @note Note two
 */
class TaggedThing
{
}
