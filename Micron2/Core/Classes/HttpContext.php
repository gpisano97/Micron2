<?php
require_once "Micron2/Core/Classes/HttpRequest.php";
require_once "Micron2/Core/Classes/HttpContextResponse.php";

final class HttpContext {

    private static HttpContext | null $instance = null;

    public HttpRequest $request;

    public HttpContextResponse $response;

    public function __construct(){
        $this->request = new HttpRequest();
        $this->response = new HttpContextResponse();
    }

    public static function GetInstance(){
        if(self::$instance == null){
            self::$instance = new HttpContext();
        }

        return self::$instance;
    }
}