<?php
require_once "Micron2/Core/Classes/HttpRequest.php";

interface IMiddleware {
    function setNext(IMiddleware $nextMiddleware): IMiddleware;

    function handle(HttpRequest $request): HttpRequest | null;
}

abstract class AMiddleware implements IMiddleware {
    private IMiddleware $_nextHandler = null;

    public function setNext(IMiddleware $nextMiddleware): IMiddleware {
        $this->_nextHandler = $nextMiddleware;
        return $nextMiddleware;
    }

    public function handle(HttpRequest $request): HttpRequest | null {
        if($this->_nextHandler != null){
            return $this->_nextHandler->handle($request);
        }
        return null;
    }
}