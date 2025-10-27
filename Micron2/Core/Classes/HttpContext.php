<?php
require_once "Micron2/Core/Classes/HttpRequest.php";
require_once "Micron2/Core/HttpResponse.php";

final class HttpContext {

    public HttpRequest $request;

    public HttpResponse $response;

    public function __construct(){
        $this->request = new HttpRequest();
        $this->response = new HttpResponse(0, '');
    }
}