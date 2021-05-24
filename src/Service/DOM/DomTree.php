<?php

/**
 * DomTree
 *
 * Dump DomDocument based documents, suiting debugging needs
 *
 * @author hakre <http://hakre.wordpress.com/>
 * @link http://stackoverflow.com/questions/12108324/how-to-get-a-raw-from-a-domnodelist/12108732#12108732
 * @link http://stackoverflow.com/questions/684227/debug-a-domdocument-object-in-php/8631974#8631974
 */

namespace App\Service\DOM;

/**
 * Decorator Stub class for a RecursiveIterator
 *
 * @TODO woz
 */
abstract class RecursiveIteratorDecoratorStub extends \IteratorIterator implements \RecursiveIterator
{

    public function __construct(\RecursiveIterator $iterator)
    {
        parent::__construct($iterator);
    }

    public function hasChildren()
    {
        return $this->getInnerIterator()->hasChildren();
    }

    public function getChildren()
    {
        return new static($this->getInnerIterator()->getChildren());
    }
}

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
     * @return boolean The return value will be casted to boolean and then evaluated. Returns true on success or false on failure.
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

class DomTree
{

    /**
     * @static
     * @param array|DOMNode|DOMNodeList $nodeOrNodes
     * @param int $maxDepth (optional)
     */
    public static function dump($nodeOrNodes, $maxDepth = 0)
    {
        $iterator = new \DOMRecursiveIterator($nodeOrNodes);
        $decorated = new \DOMRecursiveDecoratorStringAsCurrent($iterator);
        $tree = new \RecursiveTreeIterator($decorated);
        $tree->setPrefixPart(\RecursiveTreeIterator::PREFIX_END_LAST, '`');
        $tree->setPrefixPart(\RecursiveTreeIterator::PREFIX_END_HAS_NEXT, '+');
        $maxDepth && $tree->setMaxDepth($maxDepth);
        foreach ($tree as $key => $value) {
            echo htmlentities($value) . "<br />";
        }
    }

    /**
     * @static
     * @param DOMNode $node
     * @param int $maxDepth (optional)
     * @return string
     */
    public static function asString(\DOMNode $node, $maxDepth = 0)
    {
        ob_start();
        self::dump($node, $maxDepth);
        return ob_get_clean();
    }
}
