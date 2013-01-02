<?php

namespace PHPPHP\Engine\Zval;

use PHPPHP\Engine\Zval;

class Variable extends Zval {

    protected $name;
    protected $zval;
    protected $executeData;

    public function __construct(Zval $name) {
        $this->name = $name;
    }

    public function __call($method, $args) {
        $this->zval = $this->executeData->fetchVariable($this->name->toString());
        return call_user_func_array(array($this->zval, $method), $args);
    }

    public function setExecuteData(\PHPPHP\Engine\ExecuteData $executeData) {
        $this->executeData = $executeData;
    }

    public function getName() {
        return $this->name->toString();
    }
}