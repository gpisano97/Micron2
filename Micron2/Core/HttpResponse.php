<?php

require_once "Micron2/Core/Classes/HttpContentTypes.php";


final class HttpResponse
{
    public int $statusCode;
    public string|array|object $content;

    public function __construct(int $statusCode, string|array|object $content)
    {
        $this->statusCode = $statusCode;
        $this->content = $content;
    }

    // Informational 1xx
    public static function HttpContinue100(string|array|object $content): HttpResponse
    {
        return new HttpResponse(100, $content);
    }

    public static function HttpSwitchingProtocols101(string|array|object $content): HttpResponse
    {
        return new HttpResponse(101, $content);
    }

    // Success 2xx
    public static function HttpOk200(string|array|object $content): HttpResponse
    {
        return new HttpResponse(200, $content);
    }

    public static function HttpCreated201(string|array|object $content): HttpResponse
    {
        return new HttpResponse(201, $content);
    }

    public static function HttpAccepted202(string|array|object $content): HttpResponse
    {
        return new HttpResponse(202, $content);
    }

    public static function HttpNoContent204(string|array|object $content): HttpResponse
    {
        return new HttpResponse(204, $content);
    }

    // Redirection 3xx
    public static function HttpMovedPermanently301(string|array|object $content): HttpResponse
    {
        return new HttpResponse(301, $content);
    }

    public static function HttpFound302(string|array|object $content): HttpResponse
    {
        return new HttpResponse(302, $content);
    }

    public static function HttpSeeOther303(string|array|object $content): HttpResponse
    {
        return new HttpResponse(303, $content);
    }

    public static function HttpNotModified304(string|array|object $content): HttpResponse
    {
        return new HttpResponse(304, $content);
    }

    public static function HttpTemporaryRedirect307(string|array|object $content): HttpResponse
    {
        return new HttpResponse(307, $content);
    }

    // Client Error 4xx
    public static function HttpBadRequest400(string|array|object $content): HttpResponse
    {
        return new HttpResponse(400, $content);
    }

    public static function HttpUnauthorized401(string|array|object $content): HttpResponse
    {
        return new HttpResponse(401, $content);
    }

    public static function HttpForbidden403(string|array|object $content): HttpResponse
    {
        return new HttpResponse(403, $content);
    }

    public static function HttpNotFound404(string|array|object $content): HttpResponse
    {
        return new HttpResponse(404, $content);
    }

    public static function HttpMethodNotAllowed405(string|array|object $content): HttpResponse
    {
        return new HttpResponse(405, $content);
    }

    public static function HttpConflict409(string|array|object $content): HttpResponse
    {
        return new HttpResponse(409, $content);
    }

    public static function HttpTooManyRequests429(string|array|object $content): HttpResponse
    {
        return new HttpResponse(429, $content);
    }

    // Server Error 5xx
    public static function HttpInternalServerError500(string|array|object $content): HttpResponse
    {
        return new HttpResponse(500, $content);
    }

    public static function HttpNotImplemented501(string|array|object $content): HttpResponse
    {
        return new HttpResponse(501, $content);
    }

    public static function HttpBadGateway502(string|array|object $content): HttpResponse
    {
        return new HttpResponse(502, $content);
    }

    public static function HttpServiceUnavailable503(string|array|object $content): HttpResponse
    {
        return new HttpResponse(503, $content);
    }

    public static function HttpGatewayTimeout504(string|array|object $content): HttpResponse
    {
        return new HttpResponse(504, $content);
    }
}