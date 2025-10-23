<?php
require_once "Micron2/Core/Middlewares/MiddlewareInterface.php";

final class HandleEndpointsMiddleware extends AMiddleware {
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