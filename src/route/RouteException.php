<?php

namespace shiyun\route;

use Psr\Log\LoggerInterface;
use Throwable;
use think\exception\Handle as ExceptionHandlerInterface;
use shiyun\support\Request;
use shiyun\support\Response;

class RouteException implements ExceptionHandlerInterface
{
    /**
     * @param Throwable $e
     * @return mixed
     */
    public function report(Throwable $e)
    {
    }

    /**
     * @param Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render(Request $request, Throwable $e): Response
    {
    }
}
