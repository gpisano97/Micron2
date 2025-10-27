<?php
require_once "Micron2/Core/Middlewares/MiddlewareInterface.php";
require_once "Micron2/Core/Classes/HttpContext.php";
require_once "Micron2/Core/Middlewares/InternalMiddlewares/StartMiddleware.php";
require_once "Micron2/Core/Middlewares/InternalMiddlewares/LastMiddleware.php";
require_once "Micron2/Core/Middlewares/HandleEndpointsMiddleware.php";
require_once "Micron2/Core/WebApplicationEngine/DependencyRegister.php";

final class WebApplication
{
    private IMiddleware $_firstHandler;
    private ?IMiddleware $_lastHandler;
    private bool $_endpointsAdded;

    private DependencyRegister $_register;
    private HttpContext $_httpContext;

    public function __construct(DependencyRegister $register, HttpContext $context)
    {
        $this->_firstHandler = new FirstHandler();
        $this->_lastHandler = $this->_firstHandler;
        $this->_endpointsAdded = false;
        $this->_register = $register;

        $this->_httpContext = $context;
    }

    /**
     * @param class-string<AMiddleware> $middlewareClass
     */
    public function AddMiddleware(string $middlewareClassName)
    {
        $this->_lastHandler = $this->_lastHandler->setNext(DependencyResolver::ResolveExternal($this->_register, $middlewareClassName));
    }

    public function AddEndpoints()
    {
        if (!$this->_endpointsAdded) {
            //aggiunta Endpoint Handler
            $this->_lastHandler = $this->_lastHandler->setNext(new HandleEndpointsMiddleware($this->_register));
            $this->_endpointsAdded = true;
        }
    }

    public function Start()
    {
        $this->_lastHandler = $this->_lastHandler->setNext(new LastMiddleware());

        $middlewareChainResult = $this->_firstHandler->handle($this->_httpContext);

        if ($middlewareChainResult == null) {
            //richiesta non gestita
        }
    }
}