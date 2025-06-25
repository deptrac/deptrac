<?php

namespace Tests\Deptrac\Deptrac\Core\Ast\Fixtures\BasicDependency;

final class BasicDependencyClassA {}
interface BasicDependencyClassInterfaceA {}
interface BasicDependencyClassInterfaceB {}

final class BasicDependencyClassB extends BasicDependencyClassA implements BasicDependencyClassInterfaceA {

}

final class BasicDependencyClassC implements BasicDependencyClassInterfaceA, BasicDependencyClassInterfaceB {

}
