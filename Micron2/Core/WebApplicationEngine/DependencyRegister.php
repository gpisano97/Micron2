<?php

final class DependencyRegister {
    public array $_createdDependency;
    public array $_toCreateDependency;

    private static DependencyRegister | null $_instance = null;

    public function __construct(){
        $this->_createdDependency = [];
        $this->_toCreateDependency = [];        
    }

    public static function GetInstance(){
        if(self::$_instance == null){
            self::$_instance = new DependencyRegister();
        }

        return self::$_instance;
    }

}

final class DependencyResolver {
    public static function Resolve(DependencyRegister $register, string $className) : object{
        
    }
}