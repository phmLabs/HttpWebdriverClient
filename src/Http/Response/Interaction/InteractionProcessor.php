<?php

namespace phm\HttpWebdriverClient\Http\Response\Interaction;

interface InteractionProcessor
{
    const TYPE_CSS = 'css';
    const TYPE_XPATH = 'xpath';

    /**
     * @param $elementIdentifier
     * @param $type
     * @return Position[]
     */
    public function getPositions($elementIdentifier, $type = self::TYPE_CSS);

    public function endInteraction();

    public function runSequence($sequence);

    public function getSessionIdentifier();
}
