<?php

namespace PHPPHP\Engine\FunctionData;

use PHPPHP\Engine;

class User implements Engine\FunctionData {
    protected $opArray;
    protected $byRef = false;

    public function __construct(Engine\OpArray $opArray, $byRef = false) {
        $this->opArray = $opArray;
        $this->byRef = $byRef;
    }

    public function execute(Engine\Executor $executor, array $args, Engine\Zval\Ptr $return) {
        $scope = array();
        if (!$args) {
            $args = array();
        }
        if ($this->byRef) {
            $return->makeRef();
        }
        $executor->execute($this->opArray, $scope, $this, $args, $return);
    }

    public function isByRef() {
        return $this->byRef;
    }
}