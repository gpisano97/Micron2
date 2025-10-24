<?php

require_once "Micron2/Core/HttpResponse.php";

final class HttpContextResponse {
    public array|object|string $content = "";
    public int $statusCode = 0;

}