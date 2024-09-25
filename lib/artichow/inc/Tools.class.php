<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */

/**
 * Objects capable of being positioned
 *
 * @package Artichow
 */
interface awPositionable
{
    /**
     * Left align
     *
     * @var int
     */
    public const LEFT = 1;

    /**
     * Right align
     *
     * @var int
     */
    public const RIGHT = 2;

    /**
     * Center align
     *
     * @var int
     */
    public const CENTER = 3;

    /**
     * Top align
     *
     * @var int
     */
    public const TOP = 4;

    /**
     * Bottom align
     *
     * @var int
     */
    public const BOTTOM = 5;

    /**
     * Middle align
     *
     * @var int
     */
    public const MIDDLE = 6;

    /**
     * Change alignment
     *
     * @param int $h Horizontal alignment
     * @param int $v Vertical alignment
     */
    public function setAlign($h = null, $v = null);
}

registerInterface('Positionable');

/**
 * Manage left, right, top and bottom sides
 *
 * @package Artichow
 */
class awSide
{
    /**
     * Left side
     *
     * @var int
     */
    public $left = 0;

    /**
     * Right side
     *
     * @var int
     */
    public $right = 0;

    /**
     * Top side
     *
     * @var int
     */
    public $top = 0;

    /**
     * Bottom side
     *
     * @var int
     */
    public $bottom = 0;

    /**
     * Build the side
     */
    public function __construct(mixed $left = null, mixed $right = null, mixed $top = null, mixed $bottom = null)
    {
        $this->set($left, $right, $top, $bottom);
    }

    /**
     * Change side values
     */
    public function set(mixed $left = null, mixed $right = null, mixed $top = null, mixed $bottom = null)
    {
        if ($left !== null) {
            $this->left = (float) $left;
        }
        if ($right !== null) {
            $this->right = (float) $right;
        }
        if ($top !== null) {
            $this->top = (float) $top;
        }
        if ($bottom !== null) {
            $this->bottom = (float) $bottom;
        }
    }

    /**
     * Add values to each side
     */
    public function add(mixed $left = null, mixed $right = null, mixed $top = null, mixed $bottom = null)
    {
        if ($left !== null) {
            $this->left += (float) $left;
        }
        if ($right !== null) {
            $this->right += (float) $right;
        }
        if ($top !== null) {
            $this->top += (float) $top;
        }
        if ($bottom !== null) {
            $this->bottom += (float) $bottom;
        }
    }
}

registerClass('Side');
