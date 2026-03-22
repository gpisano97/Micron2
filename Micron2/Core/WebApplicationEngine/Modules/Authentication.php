<?php
require_once "Micron2/Micron2.php";

interface IAuthentication {
    function Authenticate(HttpContext $context): HttpResponse | bool;
}

final class AuthenticationMiddleware extends AMiddleware {
    
    public function __construct(private IAuthentication $authenticationHandler){}
    public function handle(HttpContext $context): HttpContext|HttpResponse|null {
        $authResponse = $this->authenticationHandler->Authenticate($context);
        if($authResponse instanceof HttpResponse){
            return $authResponse;
        }

        return $this->next($context);
    }
}