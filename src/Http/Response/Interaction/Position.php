<?php

namespace phm\HttpWebdriverClient\Http\Response\Interaction;

class Position implements \JsonSerializable
{
    /**
     * @var float
     */
    private $top;

    /**
     * @var float
     */
    private $left;

    /**
     * @var float
     */
    private $bottom;

    /**
     * @var float
     */
    private $right;

    /**
     * Position constructor.
     * @param $top
     * @param $left
     * @param $bottom
     * @param $right
     */
    public function __construct($top, $left, $bottom, $right)
    {
        $this->top = $top;
        $this->left = $left;
        $this->bottom = $bottom;
        $this->right = $right;
    }

    /**
     * @return float
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * @return float
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @return float
     */
    public function getBottom()
    {
        return $this->bottom;
    }

    /**
     * @return float
     */
    public function getRight()
    {
        return $this->right;
    }

    public function jsonSerialize()
    {
        return [
            'top' => $this->top,
            'left' => $this->left,
            'bottom' => $this->bottom,
            'right' => $this->right
        ];
    }
}
