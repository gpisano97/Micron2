<?php
require_once "Middlewares/MiddlewareInterface.php";
require_once "Micron2/Core/Classes/HttpContext.php";
require_once "Middlewares/InternalMiddlewares/StartMiddleware.php";
require_once "Micron2/Core/Middlewares/HandleEndpointsMiddleware.php";

final class WebApplication
{
    private IMiddleware $_nextHandler;
    private IMiddleware $_lastHandler;
    private bool $_endpointsAdded;

    public function __construct()
    {
        $this->_nextHandler = new FirstHandler();
        $this->_lastHandler = $this->_nextHandler;
        $this->_endpointsAdded = false;
    }

    public function AddMiddleware(IMiddleware $middleware){
        $this->_lastHandler = $this->_lastHandler->setNext($middleware);
    }

    public function AddEndpoints(){
        if(!$this->_endpointsAdded){
            //aggiunta Endpoint Handler
            $this->_lastHandler->setNext(new HandleEndpointsMiddleware());
            $this->_endpointsAdded = true;
        }
    }

    public function Start()
    {
        $context = HttpContext::GetInstance();
        
        $this->_lastHandler->setNext(new LastMiddleware());

        $middlewareChainResult = $this->_lastHandler->handle($context);

        if($middlewareChainResult == null){
            //richiesta non gestita
        }
    }
}