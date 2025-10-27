<?php
require_once "Micron2/Core/Controllers/RouteMetadata.php";


final class MatchedRoute
{
    public function __construct(
        public RouteMetadata $metadata,
        /** @var array<string, string> */
        public array $uriParams = [],
        /** @var array<string, string> */
        public array $queryParams = []
    ) {
    }
}

final class RouteMatcher
{
    private static function extractUriParams(string $routePath, string $requestUri): ?array
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $uriParts = explode('/', trim($requestUri, '/'));

        if (count($routeParts) !== count($uriParts))
            return null;

        $params = [];

        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $name = trim($part, '{}');
                $params[$name] = $uriParts[$i];
            } elseif ($part !== $uriParts[$i]) {
                return null;
            }
        }

        return $params;
    }

    private static function extractQueryParams(string $uri): array
    {
        $query = parse_url($uri, PHP_URL_QUERY);
        parse_str($query ?? '', $params);
        return $params;
    }

    public static function match(HttpContext $context, RouteMap $map): ?MatchedRoute
    {
        $requestUri = rtrim($context->request->uri, '/');
        $requestMethod = $context->request->method;

        foreach ($map->all() as $meta) {
            if ($meta->httpMethod !== $requestMethod)
                continue;

            $routePath = rtrim($meta->prefix . $meta->path, '/');
            $uriParams = self::extractUriParams($routePath, $requestUri);

            if ($uriParams !== null) {
                $queryParams = self::extractQueryParams($context->request->uri);
                return new MatchedRoute($meta, $uriParams, $queryParams);
            }
        }

        return null;
    }

}