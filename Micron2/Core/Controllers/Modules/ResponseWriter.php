<?php

require_once "Micron2/Core/Classes/HttpTypes.php";

final class ResponseWriter
{
    public static function write(HttpContext $context): void
    {
        http_response_code($context->response->statusCode);

        $content = $context->response->content;
        $headers = $context->response->headers ?? [];

        // Content-Type
        $contentType = $headers[HttpHeader::CONTENT_TYPE->value] ?? self::detectContentType($content);
        header(HttpHeader::CONTENT_TYPE->value . ': ' . $contentType);

        // Altri header (escludi Content-Type e Content-Length per ora)
        foreach ($headers as $key => $value) {
            if (
                in_array($key, [
                    HttpHeader::CONTENT_TYPE->value,
                    HttpHeader::CONTENT_LENGTH->value
                ])
            )
                continue;

            header("$key: $value");
        }

        // Scrittura del contenuto
        if (is_resource($content)) {
            // Stream binario
            if (!isset($headers[HttpHeader::CONTENT_LENGTH->value])) {
                // Se non è stato già impostato
                $stats = fstat($content);
                if ($stats && isset($stats['size'])) {
                    header(HttpHeader::CONTENT_LENGTH->value . ': ' . $stats['size']);
                }
            }

            fpassthru($content);
            fclose($content);
        } else {
            // Contenuto serializzabile
            $serialized = self::serialize($content, $contentType);
            header(HttpHeader::CONTENT_LENGTH->value . ': ' . strlen($serialized));
            echo $serialized;
        }
    }

    private static function detectContentType(mixed $content): string
    {
        return match (gettype($content)) {
            'string', 'integer', 'double' => HttpContentType::TEXT_PLAIN->value,
            'array', 'object' => HttpContentType::APPLICATION_JSON->value,
            'resource' => HttpContentType::OCTET_STREAM->value,
            default => throw new Exception("Unsupported response content type", 500),
        };
    }

    private static function serialize(mixed $content, string $contentType): string
    {
        return match ($contentType) {
            HttpContentType::APPLICATION_JSON->value => json_encode($content),
            HttpContentType::TEXT_PLAIN->value => (string) $content,
            default => throw new Exception("Cannot serialize content for type $contentType", 500),
        };
    }
}