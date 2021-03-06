<?php

namespace PHPPHP\Engine\OpLines;

class EvalOp extends \PHPPHP\Engine\OpLine {

    public function execute(\PHPPHP\Engine\ExecuteData $data) {
        $code = $this->op1->toString();
        try {
            $opCodes = $data->executor->compile('<?php ' . $code);
        } catch (\Exception $e) {
            throw new \RuntimeException('Compile Error on Eval');
        }
        $return = $data->executor->execute($opCodes, $data->symbolTable);
        if ($return) {
            $this->result->setValue($return);
        }
        $data->nextOp();
    }

}