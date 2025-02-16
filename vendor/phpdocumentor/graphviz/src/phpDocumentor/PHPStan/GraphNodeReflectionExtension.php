<?php

declare (strict_types=1);
/**
 * phpDocumentor
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */
namespace DEPTRAC_INTERNAL\phpDocumentor\GraphViz\PHPStan;

use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Graph;
use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Node;
use DEPTRAC_INTERNAL\PHPStan\Reflection\Annotations\AnnotationPropertyReflection;
use DEPTRAC_INTERNAL\PHPStan\Reflection\ClassReflection;
use DEPTRAC_INTERNAL\PHPStan\Reflection\PropertiesClassReflectionExtension;
use DEPTRAC_INTERNAL\PHPStan\Reflection\PropertyReflection;
use DEPTRAC_INTERNAL\PHPStan\Type\ObjectType;
final class GraphNodeReflectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName) : bool
    {
        return $classReflection->getName() === Graph::class;
    }
    public function getProperty(ClassReflection $classReflection, string $propertyName) : PropertyReflection
    {
        return new AnnotationPropertyReflection($classReflection, new ObjectType(Node::class), \true, \true);
    }
}
