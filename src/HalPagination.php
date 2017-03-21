<?php

namespace NilPortugues\Api\Hal;

class HalPagination
{
    const LAST = 'last';
    const HREF = 'href';
    const NEXT = 'next';
    const FIRST = 'first';
    const PREV = 'prev';
    const SELF = 'self';

    /** @var int */
    private $count;

    /** @var int */
    private $total;

    /** @var array */
    private $_embedded = [];

    /** @var array */
    private $_links = [
        self::SELF => null,
        self::FIRST => null,
        self::PREV => null,
        self::NEXT => null,
        self::LAST => null,
    ];

    /**
     * @param string $self
     *
     * @return $this
     */
    public function setSelf($self)
    {
        $this->_links[self::SELF][self::HREF] = $self;
        $this->removeEmptyLinks();

        return $this;
    }

    /**
     * @param string $first
     *
     * @return $this
     */
    public function setFirst($first)
    {
        $this->_links[self::FIRST][self::HREF] = $first;
        $this->removeEmptyLinks();

        return $this;
    }

    /**
     * @param $prev
     *
     * @return $this
     */
    public function setPrev($prev)
    {
        $this->_links[self::PREV][self::HREF] = $prev;
        $this->removeEmptyLinks();

        return $this;
    }

    /**
     * @param string $next
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->_links[self::NEXT][self::HREF] = $next;
        $this->removeEmptyLinks();

        return $this;
    }

    /**
     * @param string $last
     *
     * @return $this
     */
    public function setLast($last)
    {
        $this->_links[self::LAST][self::HREF] = $last;
        $this->removeEmptyLinks();

        return $this;
    }

    /**
     * @param $count
     *
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = (int) $count;

        return $this;
    }

    /**
     * @param $total
     *
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = (int) $total;

        return $this;
    }

    /**
     * @param array $embedded
     *
     * @return $this
     */
    public function setEmbedded(array $embedded)
    {
        $this->_embedded = $embedded;

        return $this;
    }

    /**
     * Removes empty links.
     */
    protected function removeEmptyLinks()
    {
        $this->_links = array_filter($this->_links);
    }

    /**
     * Returns the Embedded value.
     *
     * @return array
     */
    public function getEmbedded()
    {
        return $this->_embedded;
    }
}
