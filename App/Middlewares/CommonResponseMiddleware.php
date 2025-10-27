<?php
require_once "Micron2/Micron2.php";


final class CommonResponseMiddleware extends AMiddleware {
    public function handle(HttpContext $context): HttpContext|null {
        $body = [
            "description" => "Descrizione",
            "data" => $context->response->content
        ];

        $context->response->content = $body;
        
        return $this->next($context);
    }
}