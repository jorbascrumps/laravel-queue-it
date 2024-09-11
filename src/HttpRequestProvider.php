<?php

namespace Jorbascrumps\QueueIt;

use Illuminate\Http\Request;

class HttpRequestProvider extends \QueueIT\KnownUserV3\SDK\HttpRequestProvider
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequestBodyAsString(): string
    {
        return $this->request->getContent();
    }
}
