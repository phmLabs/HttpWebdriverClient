<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface SequenceAwareRequest extends RequestInterface, UserAgentAwareRequest, ViewportAwareRequest
{
    public function getSequenceId();

    public function hasSequence();

    public function getSessionIdentifier();
}