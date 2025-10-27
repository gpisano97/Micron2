<?php

require_once "Micron2/Core/Classes/HttpTypes.php";


final class HttpResponse
{
    public int $statusCode;
    public mixed $content;
    public array $headers = [];

    public function __construct(
        int $statusCode,
        mixed $content,
        ?HttpContentType $contentType = null
    ) {
        $this->statusCode = $statusCode;
        $this->content = $content;

        if ($contentType) {
            $this->addHeader(HttpHeader::CONTENT_TYPE, $contentType->value);
        }
    }

    public function addHeader(HttpHeader $header, string $value): self
    {
        $this->headers[$header->value] = $value;
        return $this;
    }

    // Informational 1xx
    public static function HttpContinue100(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(100, $content, $type);
    }

    public static function HttpSwitchingProtocols101(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(101, $content, $type);
    }

    // Success 2xx
    public static function HttpOk200(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(200, $content, $type);
    }

    public static function HttpCreated201(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(201, $content, $type);
    }

    public static function HttpAccepted202(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(202, $content, $type);
    }

    public static function HttpNoContent204(mixed $content = '', ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(204, $content, $type);
    }

    // Redirection 3xx
    public static function HttpMovedPermanently301(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(301, $content, $type);
    }

    public static function HttpFound302(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(302, $content, $type);
    }

    public static function HttpSeeOther303(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(303, $content, $type);
    }

    public static function HttpNotModified304(mixed $content = '', ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(304, $content, $type);
    }

    public static function HttpTemporaryRedirect307(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(307, $content, $type);
    }

    // Client Error 4xx
    public static function HttpBadRequest400(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(400, $content, $type);
    }

    public static function HttpUnauthorized401(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(401, $content, $type);
    }

    public static function HttpForbidden403(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(403, $content, $type);
    }

    public static function HttpNotFound404(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(404, $content, $type);
    }

    public static function HttpMethodNotAllowed405(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(405, $content, $type);
    }

    public static function HttpConflict409(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(409, $content, $type);
    }

    public static function HttpTooManyRequests429(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(429, $content, $type);
    }

    // Server Error 5xx
    public static function HttpInternalServerError500(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(500, $content, $type);
    }

    public static function HttpNotImplemented501(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(501, $content, $type);
    }

    public static function HttpBadGateway502(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(502, $content, $type);
    }

    public static function HttpServiceUnavailable503(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(503, $content, $type);
    }

    public static function HttpGatewayTimeout504(mixed $content, ?HttpContentType $type = null): HttpResponse
    {
        return new HttpResponse(504, $content, $type);
    }

    public static function fileDownload(string $filePath, string $filename, ?HttpContentType $type = null): HttpResponse
    {
        if (!is_readable($filePath)) {
            throw new Exception("File not accessible: $filePath", 500);
        }

        $stream = fopen($filePath, 'rb');
        if (!$stream) {
            throw new Exception("Unable to open file stream: $filePath", 500);
        }

        $contentType = $type?->value ?? HttpContentType::OCTET_STREAM->value;
        $size = filesize($filePath);

        $response = new HttpResponse(200, $stream);
        $response->addHeader(HttpHeader::CONTENT_TYPE, $contentType);
        $response->addHeader(HttpHeader::CONTENT_LENGTH, (string) $size);
        $response->addHeader(HttpHeader::CONTENT_DISPOSITION, 'attachment; filename="' . $filename . '"');

        return $response;
    }

}