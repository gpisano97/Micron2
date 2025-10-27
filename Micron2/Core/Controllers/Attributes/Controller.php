<?php

require_once "Micron2/Core/Classes/HttpTypes.php";

#[Attribute(Attribute::TARGET_CLASS)]
final class Controller
{
    private string $_prefix;
    public function __construct(string $prefix = "")
    {
        $this->_prefix = $prefix;
    }

    public function GetPrefix(): string
    {
        return $this->_prefix;
    }
}


class RouteMethod
{
    private string $_path;
    private HttpMethod|null $_method;
    public function __construct(string $path = "", HttpMethod|null $method = null)
    {
        $this->_path = $path;
        $this->_method = $method;
    }

    public function GetPath(): string
    {
        return $this->_path;
    }

    public function GetMethod(): ?HttpMethod
    {
        return $this->_method;
    }
}


#[Attribute(Attribute::TARGET_METHOD)]
final class Get extends RouteMethod
{
    public function __construct(string $path = '')
    {
        parent::__construct($path, HttpMethod::GET);
    }
}


#[Attribute(Attribute::TARGET_METHOD)]
final class Post extends RouteMethod
{
    public function __construct(string $path = '')
    {
        parent::__construct($path, HttpMethod::POST);
    }
}


#[Attribute(Attribute::TARGET_METHOD)]
final class Put extends RouteMethod
{
    public function __construct(string $path = '')
    {
        parent::__construct($path, HttpMethod::PUT);
    }
}


#[Attribute(Attribute::TARGET_METHOD)]
final class Delete extends RouteMethod
{
    public function __construct(string $path = '')
    {
        parent::__construct($path, HttpMethod::DELETE);
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
final class Patch extends RouteMethod
{
    public function __construct(string $path = '')
    {
        parent::__construct($path, HttpMethod::PATCH);
    }
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class FromPath
{
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class FromQuery
{
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class FromBody
{
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class FromFormData
{
}

