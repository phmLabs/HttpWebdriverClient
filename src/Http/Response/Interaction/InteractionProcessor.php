<?php

namespace phm\HttpWebdriverClient\Http\Response\Interaction;

interface InteractionProcessor
{
    /**
     * @param $elementIdentifier
     * @return Position
     */
    public function getPosition($elementIdentifier);

    public function endInteraction();

    public function runSequence($equence);

    public function getSessionIdentifier();
}
