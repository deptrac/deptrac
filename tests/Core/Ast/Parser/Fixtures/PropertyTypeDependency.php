<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

use Symfony\Component\Finder\SplFileInfo;

final class PropertyTypeDependency
{
    public SplFileInfo $property;
    public ?\SplFileInfo $propertyNullable;
    public ?object $propertyObject; // should be ignored
    public string $propertyScalar; // should be ignored
    public $propertyNonTyped; // should be ignored

    public \DateTimeInterface|SplFileInfo $propertyUnion;
}
