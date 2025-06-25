<?php

namespace Tests\Deptrac\Deptrac\Core\Ast\Fixtures\BasicDependency;

trait BasicDependencyTraitA {}
trait BasicDependencyTraitB {}
trait BasicDependencyTraitC { use \Tests\Deptrac\Deptrac\Core\Ast\Fixtures\BasicDependency\BasicDependencyTraitB; }

trait BasicDependencyTraitD {
    use BasicDependencyTraitA;
    use BasicDependencyTraitB;
}

final class BasicDependencyTraitClass {
    use BasicDependencyTraitA;
}
