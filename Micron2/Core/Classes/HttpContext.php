<?php
require_once "Micron2/Core/Classes/HttpRequest.php";
require_once "Micron2/Core/Classes/HttpContextResponse.php";

final class HttpContext {

    public HttpRequest $request;

    public HttpContextResponse $response;

    public function __construct(){
        $this->request = new HttpRequest();
        $this->response = new HttpContextResponse();
    }
}