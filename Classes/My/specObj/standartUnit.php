<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 025 25.09.17
 * Time: 14:13
 */

class StandartUnit extends SpecObject implements initable, iErrorChecker {
    private $shortName;
    private $parametr;

    public function __construct(){
        $this->warnings = [];
    }

    public function init (array $info): void {
        $this->name = $info['name'];
        $this->notation = $info['notation'];
        $this->checkErrors();
    }

    public function checkErrors(): void {

    }
}