<?php

namespace App\Service\DOM;

class NodesArrayIterator implements \Iterator
{
    /**
     * @var array
     */
    private $nodes;
    private $virtual;

    /**
     * @param array $DOMNodes
     */
    public function __construct(array $DOMNodes)
    {
        $this->nodes = $DOMNodes;
    }

    /**
     * @return DOMNode
     */
    public function current()
    {
        $keys = array_keys($this->nodes);
        return $this->nodes[$keys[$this->virtual]];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->virtual++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int|string scalar on success, integer 0 on failure.
     */
    public function key()
    {
        $this->virtual;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *      Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->virtual < count($this->nodes);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->virtual = 0;
    }
}
