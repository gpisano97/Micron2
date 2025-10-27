<?php

final class DependencyRegister
{
    public array $_createdScopedDependency;
    public array $_toCreateScopedDependency;

    public array $_transientDependency;

    /* private static DependencyRegister|null $_instance = null; */

    public function __construct()
    {
        $this->_createdScopedDependency = [];
        $this->_toCreateScopedDependency = [];
        $this->_transientDependency = [];
    }

    /* public static function GetInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new DependencyRegister();
        }
        return self::$_instance;
    } */

}

final class DependencyResolver
{
    public static function ResolveScoped(DependencyRegister $register): void
    {
        foreach ($register->_toCreateScopedDependency as $key => $className) {
            $ref = new ReflectionClass($className);

            $constructor = $ref->getConstructor();

            if (!$constructor || $constructor->getNumberOfParameters() === 0) {
                $register->_createdScopedDependency[$className] = new $className();
                return;
            }

            $args = [];
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                    throw new Exception("$className Argument {$param->getName()} not resolvable.");
                }

                if (!isset($register->_createdScopedDependency[$type->getName()])) {
                    throw new Exception("Cannot resolve $className before {$type->getName()}, check order.");
                }

                $args[] = $register->_createdScopedDependency[$type->getName()];
            }

            $register->_createdScopedDependency[$className] = new $className(...$args);
        }
    }

    public static function ResolveExternal(DependencyRegister $register, string $className)
    {
        $ref = new ReflectionClass($className);

        $constructor = $ref->getConstructor();

        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            return new $className();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                throw new Exception("$className Argument {$param->getName()} not resolvable.");
            }

            if (!isset($register->_createdScopedDependency[$type->getName()]) && !in_array($type->getName(), $register->_transientDependency)) {
                throw new Exception("Cannot resolve $className before {$type->getName()}, check order.");
            }
            
            if(isset($register->_createdScopedDependency[$type->getName()])){
                $args[] = $register->_createdScopedDependency[$type->getName()];
            }
            else{
                $args[] = self::ResolveExternal($register, $type->getName());
            }
            
        }

        return new $className(...$args);
    }
}