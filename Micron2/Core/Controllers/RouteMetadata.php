<?php
require_once "Micron2/Core/Classes/HttpTypes.php";

final class ParameterMetadata
{
    public function __construct(
        public string $name,
        public ?string $type,
        public ?string $source, // 'path', 'query', 'body', 'form'
        public bool $hasDefault,
        public mixed $defaultValue
    ) {}
}
final class RouteMetadata {
    public function __construct(
        public string $className,
        public string $methodName,
        public HttpMethod $httpMethod,
        public string $path,
        public string $prefix,
        /** @var ParameterMetadata[] */
        public array $parameters = []
    ) {}
}

class RouteMap
{
    /** @var array<string, RouteMetadata> */
    private array $routes = [];

    public function add(string $key, RouteMetadata $meta): void {
        $this->routes[$key] = $meta;        
    }

    public function all(): array {
        return $this->routes;
    }

    public function get(string $key): ?RouteMetadata {
        return $this->routes[$key] ?? null;
    }
}