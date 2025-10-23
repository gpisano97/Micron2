<?php

require_once "Micron2/Core/Middlewares/MiddlewareInterface.php";
require_once "Micron2/Core/Classes/HttpContext.php";

final class LastMiddleware extends AMiddleware {
    private function BuildContentTypeHeader(HttpContentType $contentType)
    {
        return HttpHeader::CONTENT_TYPE->value . ": " . $contentType->value;
    }
    private function WriteResponse(string|array|object $content)
    {
        $toWrite = "";
        switch (gettype($content)) {
            case 'string':
                header($this->BuildContentTypeHeader(HttpContentType::TEXT_PLAIN));
                $toWrite = $content;
                break;
            case 'array':
                header($this->BuildContentTypeHeader(HttpContentType::APPLICATION_JSON));
                $toWrite = json_encode($content);
                break;
            case 'object':
                header($this->BuildContentTypeHeader(HttpContentType::APPLICATION_JSON));
                $toWrite = json_encode($content);
                break;

            default:
                throw new Exception("Response content not allowed.", 500);
        }

        echo $toWrite;
    }
    public function handle(HttpContext $context): HttpContext | null {
        http_response_code($context->response->statusCode);
        $this->WriteResponse($context->response->content);
        return $context;
    }
}