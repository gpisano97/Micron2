<?php

require_once "Micron2/Core/Classes/HttpTypes.php";

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