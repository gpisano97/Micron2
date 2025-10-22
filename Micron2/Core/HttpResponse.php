<?php

require_once "Micron2/Core/Classes/HttpHeaders.php";
require_once "Micron2/Core/Classes/HttpContentTypes.php";


final class HttpResponse
{
    private static function BuildContentTypeHeader(HttpContentType $contentType)
    {
        return HttpHeader::CONTENT_TYPE->value . ": " . $contentType->value;
    }
    private static function WriteResponse(string|array|object $content)
    {
        $toWrite = "";
        switch (gettype($content)) {
            case 'string':
                header(HttpResponse::BuildContentTypeHeader(HttpContentType::TEXT_PLAIN));
                $toWrite = $content;
                break;
            case 'array':
                header(HttpResponse::BuildContentTypeHeader(HttpContentType::APPLICATION_JSON));
                $toWrite = json_encode($content);
                break;
            case 'object':
                header(HttpResponse::BuildContentTypeHeader(HttpContentType::APPLICATION_JSON));
                $toWrite = json_encode($content);
                break;

            default:
                throw new Exception("Response content not allowed.", 1);
        }

        echo $toWrite;
    }

    // Informational 1xx
    public static function HttpContinue100(string|array|object $content): void
    {
        http_response_code(100);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpSwitchingProtocols101(string|array|object $content): void
    {
        http_response_code(101);
        self::WriteResponse($content);
        exit;
    }

    // Success 2xx
    public static function HttpOk200(string|array|object $content): void
    {
        http_response_code(200);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpCreated201(string|array|object $content): void
    {
        http_response_code(201);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpAccepted202(string|array|object $content): void
    {
        http_response_code(202);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpNoContent204(string|array|object $content): void
    {
        http_response_code(204);
        self::WriteResponse($content);
        exit;
    }

    // Redirection 3xx
    public static function HttpMovedPermanently301(string|array|object $content): void
    {
        http_response_code(301);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpFound302(string|array|object $content): void
    {
        http_response_code(302);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpSeeOther303(string|array|object $content): void
    {
        http_response_code(303);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpNotModified304(string|array|object $content): void
    {
        http_response_code(304);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpTemporaryRedirect307(string|array|object $content): void
    {
        http_response_code(307);
        self::WriteResponse($content);
        exit;
    }

    // Client Error 4xx
    public static function HttpBadRequest400(string|array|object $content): void
    {
        http_response_code(400);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpUnauthorized401(string|array|object $content): void
    {
        http_response_code(401);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpForbidden403(string|array|object $content): void
    {
        http_response_code(403);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpNotFound404(string|array|object $content): void
    {
        http_response_code(404);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpMethodNotAllowed405(string|array|object $content): void
    {
        http_response_code(405);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpConflict409(string|array|object $content): void
    {
        http_response_code(409);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpTooManyRequests429(string|array|object $content): void
    {
        http_response_code(429);
        self::WriteResponse($content);
        exit;
    }

    // Server Error 5xx
    public static function HttpInternalServerError500(string|array|object $content): void
    {
        http_response_code(500);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpNotImplemented501(string|array|object $content): void
    {
        http_response_code(501);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpBadGateway502(string|array|object $content): void
    {
        http_response_code(502);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpServiceUnavailable503(string|array|object $content): void
    {
        http_response_code(503);
        self::WriteResponse($content);
        exit;
    }
    public static function HttpGatewayTimeout504(string|array|object $content): void
    {
        http_response_code(504);
        self::WriteResponse($content);
        exit;
    }
}