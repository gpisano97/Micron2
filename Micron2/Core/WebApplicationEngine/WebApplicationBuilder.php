<?php
require_once "Micron2/Core/Classes/HttpContext.php";
require_once "Micron2/Core/WebApplicationEngine/DependencyRegister.php";
final class WebApplicationBuilder
{

    private DependencyRegister $_scopedDependencyRegister;
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
        $this->_scopedDependencyRegister = DependencyRegister::GetInstance();

        $context = HttpContext::GetInstance();    
        $context->request->requestBody = file_get_contents("php://input");
        $context->request->uri = $_REQUEST["uri"];
        $context->request->headers = $this->GetRequestHeaders();
        $context->request->method = HttpMethod::tryFrom($_SERVER["REQUEST_METHOD"]);
    }
}