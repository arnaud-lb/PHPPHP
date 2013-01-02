<?php

namespace PHPPHP\Engine;

class Executor {
    const DO_RETURN = 1;
    const DO_SHUTDOWN = 2;

    public $executorGlobals;
    public $structureStack = array();

    protected $stack = array();
    protected $current;
    protected $globalScope = array();
    protected $parser;
    protected $shutdown = false;
    protected $compiler;
    protected $files = array();

    protected $extensions;

    protected $functionStore;
    protected $constantStore;

    public function __construct(FunctionStore $functionStore, ConstantStore $constantStore) {
        $this->executorGlobals = new ExecutorGlobals;
        $this->parser = new Parser;
        $this->compiler = new Compiler;
        $this->functionStore = $functionStore;
        $this->constantStore = $constantStore;

        $this->extensions = new \SplObjectStorage;
    }

    public function hasFile($fileName) {
        return isset($this->files[$fileName]);
    }

    public function compileFile($fileName) {
        if (!isset($this->files[$fileName])) {
            $code = file_get_contents($fileName);
            $this->files[$fileName] = $this->parser->parse($code);
        }
        return $this->compiler->compile($this->files[$fileName]);
    }

    public function compile($code) {
        $ast = $this->parser->parse($code);
        return $this->compiler->compile($ast);
    }

    public function execute(OpArray $opArray, array &$symbolTable = array(), FunctionData $function = null, array $args = array(), Zval $result = null) {
        if ($this->shutdown) return;
        $scope = new ExecuteData($this, $opArray, $function);
        $scope->arguments = $args;

        if ($this->current) {
            $scope->parent = $this->current;
        }
        $this->stack[] = $scope;
        $this->current = $scope;

        if ($symbolTable || $function) {
            $scope->symbolTable =& $symbolTable;
        } else {
            $scope->symbolTable =& $this->executorGlobals->symbolTable;
        }

        $opArray->registerExecutor($this);

        while (!$this->shutdown && $scope->opLine) {
            $ret = $scope->opLine->execute($scope);
            switch ($ret) {
                case self::DO_RETURN:
                    array_pop($this->stack);
                    if ($result) {
                        $result->setValue($scope->returnValue);
                    }
                    $this->current = end($this->stack);
                    return;
                case self::DO_SHUTDOWN:
                    $this->shutdown = true;
                    return;
            }
        }
        die('Should never reach this point!');
    }

    public function getCurrent() {
        return $this->current;
    }

    public function getFunctionStore() {
        return $this->functionStore;
    }

    public function getConstantStore() {
        return $this->constantStore;
    }

    public function registerExtension(Extension $extension) {
        if (!$this->extensions->contains($extension)) {
            $extension->register($this);
            $this->extensions->attach($extension);
        }
    }
}
