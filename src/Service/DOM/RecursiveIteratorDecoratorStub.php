<?php

namespace App\Service\DOM;

/**
 * Decorator Stub class for a RecursiveIterator.
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
