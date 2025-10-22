<?php

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case CONNECT = 'CONNECT';
}

final class HttpRequest
{
    public string $uri;
    public HttpMethod $method;
    public string $requestBody;
    public array $headers;
    private DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function GetRequestCreatedAt () : DateTime  {
        return $this->createdAt;
    }
}