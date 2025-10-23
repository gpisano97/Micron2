<?php

final class DependencyRegister {
    public array $_createdScopedDependency;
    public array $_toCreateScopedDependency;

    public function __construct(){
        $this->_createdScopedDependency = [];
        $this->_toCreateScopedDependency = [];        
    }
}