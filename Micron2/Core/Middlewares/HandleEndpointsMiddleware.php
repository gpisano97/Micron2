<?php
require_once "Micron2/Core/Middlewares/MiddlewareInterface.php";
require_once "Micron2/Core/WebApplicationEngine/DependencyRegister.php";

final class HandleEndpointsMiddleware extends AMiddleware {
    private DependencyRegister $_dependencyRegister;
    public function __construct(DependencyRegister $register){
        $this->_dependencyRegister = $register;
    }
    public function handle(HttpContext $context): HttpContext|null{
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