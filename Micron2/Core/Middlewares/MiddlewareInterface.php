<?php
require_once "Micron2/Core/Classes/HttpContext.php";
require_once "Micron2/Core/HttpResponse.php";

interface IMiddleware
{
    function setNext(IMiddleware $nextMiddleware): IMiddleware;

    function handle(HttpContext $context): HttpContext | HttpResponse | null;

    function next(HttpContext $context): HttpContext | HttpResponse | null;
}

abstract class AMiddleware implements IMiddleware
{
    private ?IMiddleware $_nextHandler = null;

    public function setNext(IMiddleware $nextMiddleware): IMiddleware
    {
        $this->_nextHandler = $nextMiddleware;
        return $nextMiddleware;
    }

    public function handle(HttpContext $context): HttpContext | HttpResponse | null
    {
        if ($this->_nextHandler != null) {
            return $this->_nextHandler->handle($context);
        }
        return null;
    }

    function next(HttpContext $context): HttpContext | HttpResponse | null
    {
        return $this->_nextHandler->handle($context);
    }
}