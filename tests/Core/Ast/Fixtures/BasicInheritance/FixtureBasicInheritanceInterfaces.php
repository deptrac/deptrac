<?php

namespace Tests\Deptrac\Deptrac\Core\Ast\Fixtures;

interface FixtureBasicInheritanceInterfaceA { }
interface FixtureBasicInheritanceInterfaceB extends FixtureBasicInheritanceInterfaceA { }
interface FixtureBasicInheritanceInterfaceC extends FixtureBasicInheritanceInterfaceB { }
interface FixtureBasicInheritanceInterfaceD extends FixtureBasicInheritanceInterfaceC { }
interface FixtureBasicInheritanceInterfaceE extends \Tests\Deptrac\Deptrac\Core\Ast\Fixtures\FixtureBasicInheritanceInterfaceD
{ }
