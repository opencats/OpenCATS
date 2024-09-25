<?php

// $Id: spl_examples.php 1262 2006-02-05 19:35:31Z lastcraft $

class IteratorImplementation implements Iterator
{
    public function current()
    {
    }

    public function next()
    {
    }

    public function key()
    {
    }

    public function valid()
    {
    }

    public function rewind()
    {
    }
}

class IteratorAggregateImplementation implements IteratorAggregate
{
    public function getIterator()
    {
    }
}
