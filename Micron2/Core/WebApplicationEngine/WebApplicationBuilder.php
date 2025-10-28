<?php
require_once "Micron2/Core/Classes/HttpContext.php";
require_once "Micron2/Core/WebApplicationEngine/DependencyRegister.php";
require_once "Micron2/Core/WebApplicationEngine/WebApplication.php";
require_once "Micron2/Core/Classes/AppConfiguration.php";
require_once "Micron2/Core/WebApplicationEngine/Modules/CorsHandler.php";
require_once "Micron2/Core/Controllers/Modules/ResponseWriter.php";
final class WebApplicationBuilder
{

    private DependencyRegister $_dependencyRegister;
    private HttpContext $_httpContext;
    private string $_configurationsPath = "";

    /** @var array<callable> */
    private array $_scopedWithoutDI = [];
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

    private function IstantiateOutOfDi(){
        foreach ($this->_scopedWithoutDI as $callableConstructor) {
            $appConfiguratorInstace = null;
            if(array_key_exists(AppConfiguration::class,$this->_dependencyRegister->_createdScopedDependency)){
                $appConfiguratorInstace = $this->_dependencyRegister->_createdScopedDependency[AppConfiguration::class];
            }
            $instance = $callableConstructor(
                $this->_dependencyRegister->_createdScopedDependency[HttpContext::class],
                $appConfiguratorInstace
            );

            $this->_dependencyRegister->_createdScopedDependency[get_class($instance)] = $instance; 
        }
    }

    public function AddDbContext()
    {
        array_unshift($this->_dependencyRegister->_toCreateScopedDependency, 'NOMECLASSEDB');
    }

    /**
     * @param class-string<AMiddleware> $middlewareClass
     */
    public function AddScoped(string $className)
    {
        $this->_dependencyRegister->_toCreateScopedDependency[] = $className;
    }

    /**
     * @param callable(HttpContext $context, AppConfiguration $config): object $objectConstructor
     */
    public function AddScopedWithoutDI(callable $objectConstructor){
        $this->_scopedWithoutDI[] = $objectConstructor;
    }
    public function __construct()
    {
        $this->_dependencyRegister = new DependencyRegister();

        $this->_httpContext = new HttpContext();
        $this->_httpContext->request->requestBody = file_get_contents("php://input");
        $this->_httpContext->request->uri = $_SERVER['REQUEST_URI'];
        $this->_httpContext->request->headers = $this->GetRequestHeaders();
        $this->_httpContext->request->method = HttpMethod::tryFrom($_SERVER["REQUEST_METHOD"]);
        $this->_httpContext->request->post = $_POST;
        $this->_httpContext->request->files = $_FILES;
    }

    public function AddConfigurations(string $path){
        $this->_configurationsPath = $path;
    }

    public function AddCors(CorsHandlerSettings $settings){
        $corsHandlingResponse = CorsHandler::handle($this->_httpContext, $settings);

        if($corsHandlingResponse instanceof HttpResponse){
            $this->_httpContext->response = $corsHandlingResponse;
            ResponseWriter::write($this->_httpContext);
            exit;
        }
    }

    public function Build()
    {

        $this->_dependencyRegister->_createdScopedDependency["HttpContext"] = $this->_httpContext;
        
        if($this->_configurationsPath != "")
            $this->_dependencyRegister->_createdScopedDependency[AppConfiguration::class] = new AppConfiguration($this->_configurationsPath);

        $this->IstantiateOutOfDi();
    
        DependencyResolver::ResolveScoped($this->_dependencyRegister);
        return new WebApplication($this->_dependencyRegister, $this->_httpContext);
    }
}