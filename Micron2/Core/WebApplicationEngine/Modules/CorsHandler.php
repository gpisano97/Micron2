<?php
require_once "Micron2/Core/Classes/HttpTypes.php";
require_once "Micron2/Core/HttpResponse.php";
require_once "Micron2/Core/Classes/HttpContext.php";

final class CorsHandlerSettings
{
    public function __construct(
        /** @var array<string>*/
        public array $allowedOrigins = ['*'],
        /** @var array<HttpHeader> */
        public array $allowedHeaders = [HttpHeader::CONTENT_TYPE, HttpHeader::AUTHORIZATION],
        public int $maxAges = 86400,
        /** @var array<HttpMethod> */
        public array $allowedMethods = [HttpMethod::GET, HttpMethod::POST, HttpMethod::PUT, HttpMethod::DELETE, HttpMethod::OPTIONS]
    ) {
    }
}

final class CorsHandler
{

    public static function handle(HttpContext $context, CorsHandlerSettings $settings): HttpResponse|HttpContext
    {

        $origin = $context->request->headers['Origin'] ?? '*';

        if (!in_array($origin, $settings->allowedOrigins)) {
            return HttpResponse::HttpForbidden403("Origin Not Allowed");
        }

        //preflight
        $enumMappingFunction = function ($item) {
            return $item->value;
        };

        if ($context->request->method === HttpMethod::OPTIONS) {
            $response = HttpResponse::HttpNoContent204();

            $response->addHeader(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $origin)
                ->addHeader(HttpHeader::ACCESS_CONTROL_ALLOW_METHODS, implode(',', array_map($enumMappingFunction, $settings->allowedMethods)))
                ->addHeader(HttpHeader::ACCESS_CONTROL_ALLOW_HEADERS, implode(',', array_map($enumMappingFunction, $settings->allowedHeaders)))
                ->addHeader(HttpHeader::ACCESS_CONTROL_MAX_AGE, $settings->maxAges);
            return $response;
        }
        header(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN->value . ': ' . $origin);
        header(HttpHeader::ACCESS_CONTROL_ALLOW_HEADERS->value . ': ' . implode(',', array_map($enumMappingFunction, $settings->allowedHeaders)));
        return $context;
    }
}