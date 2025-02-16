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

use function addslashes;
use function implode;
use function strtolower;
use function substr;
/**
 * Class representing a node / element in a graph.
 *
 * @link      http://phpdoc.org
 *
 * @method void setLabel(string $name) Sets the label for this node.
 */
class Node
{
    use Attributes;
    /** @var string Name for this node */
    protected $name = '';
    /**
     * Creates a new node with name and optional label.
     *
     * @param string      $name  Name of the new node.
     * @param string|null $label Optional label text.
     */
    public function __construct(string $name, ?string $label = null)
    {
        $this->setName($name);
        if ($label === null) {
            return;
        }
        $this->setLabel($label);
    }
    /**
     * Factory method used to assist with fluent interface handling.
     *
     * See the examples for more details.
     *
     * @param string      $name  Name of the new node.
     * @param string|null $label Optional label text.
     */
    public static function create(string $name, ?string $label = null) : self
    {
        return new self($name, $label);
    }
    /**
     * Sets the name for this node.
     *
     * Not to confuse with the label.
     *
     * @param string $name Name for this node.
     */
    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Returns the name for this node.
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * Magic method to provide a getter/setter to add attributes on the Node.
     *
     * Using this method we make sure that we support any attribute without
     * too much hassle. If the name for this method does not start with get or
     * set we return null.
     *
     * Set methods return this graph (fluent interface) whilst get methods
     * return the attribute value.
     *
     * @param string  $name      Method name; either getX or setX is expected.
     * @param mixed[] $arguments List of arguments; only 1 is expected for setX.
     *
     * @return Attribute|Node|null
     *
     * @throws AttributeNotFound
     */
    public function __call(string $name, array $arguments)
    {
        $key = strtolower(substr($name, 3));
        if (strtolower(substr($name, 0, 3)) === 'set') {
            return $this->setAttribute($key, (string) $arguments[0]);
        }
        if (strtolower(substr($name, 0, 3)) === 'get') {
            return $this->getAttribute($key);
        }
        return null;
    }
    /**
     * Returns the node definition as is requested by GraphViz.
     */
    public function __toString() : string
    {
        $attributes = [];
        foreach ($this->attributes as $value) {
            $attributes[] = (string) $value;
        }
        $attributes = implode("\n", $attributes);
        $name = addslashes($this->getName());
        return <<<DOT
"{$name}" [
{$attributes}
]
DOT;
    }
}
