<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 025 25.09.17
 * Time: 14:13
 */

class StandartUnit extends Purchased implements iErrorChecker {
    private $shortName;
    private $parametr;

    public function checkErrors() {

    }
}

class OtherUnit extends Purchased implements iErrorChecker {
    public function checkErrors() {

    }
}

class MaterialUnit extends SpecObject implements initable, iErrorChecker {

    public function __construct(){
        $this->warnings = [];
    }

    public function init (array $info) {
        $this->name = $info['name'];
        $this->notation = $info['notation'];
        $this->checkErrors();
    }

    public function checkErrors() {
        if($this->notation == '')
            $this->addWarning(new Warning('noMeasure'));
    }
}