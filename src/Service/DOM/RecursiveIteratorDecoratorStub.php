<?php

namespace App\Service\DOM;

/**
 * Decorator Stub class for a RecursiveIterator.
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
        return new self($this->getInnerIterator()->getChildren());
    }
}
