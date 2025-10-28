<?php
require_once "Micron2/Core/Classes/HttpTypes.php";

final class CorsHandleSettings {
    public function __construct(
        /** @var array<string>*/
        public array $allowedOrigins = ['*'],
        /** @var array<HttpHeader> */
        public array $allowedHeaders = [HttpHeader::CONTENT_TYPE, HttpHeader::AUTHORIZATION],
        public int $maxAges = 86400,
        /** @var array<HttpMethod> */
        public array $allowedMethods = [HttpMethod::GET, HttpMethod::POST, HttpMethod::PUT, HttpMethod::DELETE, HttpMethod::OPTIONS]
    ){}
}

final class CorsHandler {
    
    public static function hanlde(HttpContext $context, CorsHandleSettings $settings){

    }
}