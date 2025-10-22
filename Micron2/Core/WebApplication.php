<?php
require_once "Middlewares/MiddlewareInterface.php";
require_once "Classes/HttpRequest.php";
require_once "Middlewares/InternalMiddlewares/StartMiddleware.php";

final class WebApplication
{
    private IMiddleware $_nextHandler;
    private IMiddleware $_lastHandler;

    private function GetRequestHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = substr($key, 5);
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $name = $key;
            } else {
                continue;
            }

            $normalized = ucwords(strtolower(str_replace('_', '-', $name)), '-');
            $headers[$normalized] = $value;
        }

        return $headers;
    }

    public function __construct()
    {
        $this->_nextHandler = new FirstHandler();
        $this->_lastHandler = $this->_nextHandler;
    }

    public function AddMiddleware(IMiddleware $middleware){
        $this->_lastHandler = $this->_lastHandler->setNext($middleware);
    }

    public function Start()
    {
        $request = new HttpRequest();

        $request->requestBody = file_get_contents("php://input");
        $request->uri = $_REQUEST["uri"];
        $request->headers = $this->GetRequestHeaders();
        $request->method = HttpMethod::tryFrom($_SERVER["REQUEST_METHOD"]);

        $middlewareChainResult = $this->_lastHandler->handle($request);

        if($middlewareChainResult == null){
            //richiesta non gestita
        }
    }
}