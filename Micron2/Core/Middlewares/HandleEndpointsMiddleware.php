<?php
require_once "Micron2/Core/Middlewares/MiddlewareInterface.php";
require_once "Micron2/Core/WebApplicationEngine/DependencyRegister.php";
require_once "Micron2/Core/Controllers/Modules/ControllerDiscovery.php";
require_once "Micron2/Core/Controllers/Modules/ControllerInvoker.php";
require_once "Micron2/Core/Controllers/Modules/RouteMatcher.php";
require_once "Micron2/Core/Controllers/Modules/ParametersBinder.php";

final class HandleEndpointsMiddleware extends AMiddleware
{
    private DependencyRegister $_dependencyRegister;
    public function __construct(DependencyRegister $register)
    {
        $this->_dependencyRegister = $register;
    }
    public function handle(HttpContext $context): HttpContext|null
    {

        // 1) Discover dei Controller 
        $routeMap = ControllerDiscovery::scan();
        // 2) Ricerca di un match per l'URI -> magari ritorniamo il nome del metodo 
        $matchedRoute = RouteMatcher::match($context, $routeMap);
        if ($matchedRoute) {
            // 3) Se troviamo un match iniziamo istanziando la classe con le opportune dipendenze 
            // 4) prendiamo il metodo e facciamo il binding dei parametri             
            // 5) Eseguiamo il metodo
            try {
                $executionResult = ControllerInvoker::invoke($this->_dependencyRegister, $matchedRoute, $context);
                if ($executionResult instanceof HttpResponse) {
                    $context->response = $executionResult;
                }
                else{
                    $context->response->statusCode = 200;
                    $context->response->content = $executionResult;
                }
            } catch (\Throwable $th) {
                $context->response->statusCode = 500;
                $context->response->content = $th->getMessage();
            }
            //scrittura della risposta nel context;

        } else {
            $context->response->statusCode = 404;
            $context->response->content = "Route not found.";
        }

        return $this->next($context);
    }
}