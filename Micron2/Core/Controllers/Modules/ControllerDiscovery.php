<?php

require_once "Micron2/Core/Controllers/RouteMetadata.php";
require_once "Micron2/Core/Controllers/Attributes/Controller.php";

final class ControllerDiscovery
{
    public static function scan(): RouteMap
    {
        $map = new RouteMap();

        foreach (get_declared_classes() as $className) {
            $refClass = new ReflectionClass($className);
            $controllerAttrs = $refClass->getAttributes(Controller::class);

            if (count($controllerAttrs) === 0)
                continue;

            /** @var Controller $controller */
            $controller = $controllerAttrs[0]->newInstance();
                    
            $prefix = $controller->getPrefix();

            foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $routeAttrs = $method->getAttributes(RouteMethod::class, ReflectionAttribute::IS_INSTANCEOF);

                foreach ($routeAttrs as $attr) {
                    /** @var RouteMethod $route */
                    $route = $attr->newInstance();

                    $parameters = [];
                    foreach ($method->getParameters() as $param) {
                        $type = $param->getType();

                        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : null;
                        
                        $source = null;
                        foreach ($param->getAttributes() as $pAttr) {
                            $attrName = $pAttr->getName();
                            if ($attrName === FromPath::class)
                                $source = 'path';
                            elseif ($attrName === FromQuery::class)
                                $source = 'query';
                            elseif ($attrName === FromBody::class)
                                $source = 'body';
                            elseif ($attrName === FromFormData::class)
                                $source = 'form';
                        }

                        $parameters[] = new ParameterMetadata(
                            name: $param->getName(),
                            type: $typeName,
                            source: $source,
                            hasDefault: $param->isDefaultValueAvailable(),
                            defaultValue: $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
                        );
                    }

                    $meta = new RouteMetadata(
                        className: $className,
                        methodName: $method->getName(),
                        httpMethod: $route->getMethod(),
                        path: $route->getPath(),
                        prefix: $prefix,
                        parameters: $parameters
                    );

                    $map->add($className . '@' . $method->getName(), $meta);
                }
            }
        }

        return $map;
    }
}