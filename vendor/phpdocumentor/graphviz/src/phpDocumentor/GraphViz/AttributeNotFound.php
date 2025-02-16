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
namespace DEPTRAC_INTERNAL\phpDocumentor\GraphViz;

use function sprintf;
class AttributeNotFound extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Attribute with name "%s" was not found.', $name));
    }
}
