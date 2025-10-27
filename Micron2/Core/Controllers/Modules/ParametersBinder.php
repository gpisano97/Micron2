<?php

final class ParameterBinder
{
    public static function bind(MatchedRoute $matched, HttpContext $context): array
    {
        $finalParams = [];
        $hydratedCache = [];
                
        foreach ($matched->metadata->parameters as $paramMeta) {
            $isObject = $paramMeta->type && class_exists($paramMeta->type);
            $sourceKey = $paramMeta->source . ':' . $paramMeta->type;

            if ($isObject) {
                // Evita duplicati: se già creato, riutilizza
                if (isset($hydratedCache[$sourceKey])) {
                    $finalParams[] = $hydratedCache[$sourceKey];
                    continue;
                }

                $sourceData = match ($paramMeta->source) {
                    'query' => $matched->queryParams,
                    'path' => $matched->uriParams,
                    'body' => json_decode($context->request->requestBody ?? '', true) ?? [],
                    default => []
                };

                $object = self::hydrateObject($paramMeta->type, $sourceData);
                $hydratedCache[$sourceKey] = $object;
                $finalParams[] = $object;
                continue;
            }

            // Parametro scalare
            $value = match ($paramMeta->source) {
                'query' => $matched->queryParams[$paramMeta->name] ?? null,
                'path' => $matched->uriParams[$paramMeta->name] ?? null,
                'body' => json_decode($context->request->requestBody ?? '', true)[$paramMeta->name] ?? null,
                default => null
            };

            if ($value === null && $paramMeta->hasDefault) {
                $value = $paramMeta->defaultValue;
            }

            $finalParams[] = self::castScalar($value, $paramMeta->type);
        }

        return $finalParams;
    }

    private static function castScalar(mixed $value, ?string $type): mixed
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string) $value,
            default => $value,
        };
    }

    private static function hydrateObject(string $className, array $data): object
    {
        $ref = new ReflectionClass($className);
        $instance = $ref->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            if ($ref->hasProperty($key)) {
                $prop = $ref->getProperty($key);
                if ($prop->isPublic()) {
                    $instance->$key = $value;
                }
            }
        }

        return $instance;
    }
}
