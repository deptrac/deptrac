<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency\Emitter;

use function array_shift;
use function count;
class FQDNIndexNode
{
    private bool $FQDN = \false;
    /**
     * @var array<string, FQDNIndexNode>
     */
    private array $nodes = [];
    public function isFQDN() : bool
    {
        return $this->FQDN;
    }
    /**
     * @param string[] $keys
     */
    public function getNestedNode(array $keys) : ?self
    {
        $index = $this;
        foreach ($keys as $key) {
            $node = $index->nodes[$key] ?? null;
            if (null === $node) {
                return null;
            }
            $index = $node;
        }
        return $index;
    }
    /**
     * @param string[] $keys
     */
    public function setNestedNode(array $keys) : void
    {
        if (0 === count($keys)) {
            $this->FQDN = \true;
            return;
        }
        $key = array_shift($keys);
        $node = $this->nodes[$key] ?? null;
        if (null === $node) {
            $node = new self();
            $this->nodes[$key] = $node;
        }
        $node->setNestedNode($keys);
    }
}
