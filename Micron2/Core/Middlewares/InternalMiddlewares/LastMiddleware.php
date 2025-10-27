<?php

require_once "Micron2/Core/Middlewares/MiddlewareInterface.php";
require_once "Micron2/Core/Classes/HttpContext.php";
require_once "Micron2/Core/Controllers/Modules/ResponseWriter.php";

final class LastMiddleware extends AMiddleware {
    public function handle(HttpContext $context): HttpContext | null {
        ResponseWriter::write($context);
        return $context;
    }
}