<?php
require_once "Micron2/Core/Middlewares/MiddlewareInterface.php";
require_once "Micron2/Core/WebApplicationEngine/DependencyRegister.php";
require_once "Micron2/Core/Controllers/Modules/ControllerDiscovery.php";
require_once "Micron2/Core/Controllers/Modules/RouteMatcher.php";

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
        if($matchedRoute == null){
            //ritornare 404 perché non lo trova
        }
        // 3) Se troviamo un match iniziamo istanziando la classe con le opportune dipendenze 
        // 4) prendiamo il metodo e facciamo il binding dei parametri 
        // 5) Eseguiamo il metodo
        //scoperta endpoint da gestire


        //esecuzione funzione che gestisce l'endpoint se la trova
        //recupero della risposta, simuliamo che risponda con un HttpResponse, dopo gestiamo anche gli altri casi
        $endpointResponse = HttpResponse::HttpOk200(["ciao" => "prova"]);

        //scrittura della risposta nel context;
        $context->response->statusCode = $endpointResponse->statusCode;
        $context->response->content = $endpointResponse->content;
        return $context;
    }
}