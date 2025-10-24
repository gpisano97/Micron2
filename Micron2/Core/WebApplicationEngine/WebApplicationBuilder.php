<?php
require_once "Micron2/Core/Classes/HttpContext.php";
require_once "Micron2/Core/WebApplicationEngine/DependencyRegister.php";
require_once "Micron2/Core/WebApplicationEngine/WebApplication.php";
final class WebApplicationBuilder
{

    private DependencyRegister $_dependencyRegister;
    private HttpContext $_httpContext;
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

    public function AddDbContext(){
        array_unshift($this->_dependencyRegister->_toCreateScopedDependency, 'NOMECLASSEDB'); 
    }

    public function AddScoped(string $className){
        $this->_dependencyRegister->_toCreateScopedDependency[] = $className;
    }
    public function __construct()
    {
        $this->_dependencyRegister = new DependencyRegister();

        $this->_httpContext = new HttpContext(); 
        $this->_httpContext->request->requestBody = file_get_contents("php://input");
        $this->_httpContext->request->uri = $_SERVER['REQUEST_URI'];
        $this->_httpContext->request->headers = $this->GetRequestHeaders();
        $this->_httpContext->request->method = HttpMethod::tryFrom($_SERVER["REQUEST_METHOD"]);
    }

    public function Build(){
        
        $this->_dependencyRegister->_createdScopedDependency["HttpContext"] = $this->_httpContext;

        DependencyResolver::ResolveScoped($this->_dependencyRegister);
        return new WebApplication($this->_dependencyRegister, $this->_httpContext);
    }
}