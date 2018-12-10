<?php

namespace phm\HttpWebdriverClient\Http\Response;

use phm\HttpWebdriverClient\Http\Response\Interaction\InteractionProcessor;
use Psr\Http\Message\ResponseInterface;

interface InteractiveResponse extends ResponseInterface
{
    /**
     * @return InteractionProcessor
     */
    public function getInteractionProcessor();
}