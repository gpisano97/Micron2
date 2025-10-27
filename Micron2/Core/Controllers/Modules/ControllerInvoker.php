<?php
require_once "Micron2/Core/WebApplicationEngine/DependencyRegister.php";

require_once "Micron2/Core/Controllers/Modules/RouteMatcher.php";

final class ControllerInvoker {
    public static function invoke(DependencyRegister $register, MatchedRoute $matchedRouteData, HttpContext $context) : array|object|string|int|float {
        $resolvedInstance = DependencyResolver::ResolveExternal($register, $matchedRouteData->metadata->className);
        $methodParameters = ParameterBinder::bind($matchedRouteData, $context);
        return $resolvedInstance->{$matchedRouteData->metadata->methodName}(...$methodParameters);
    }
}