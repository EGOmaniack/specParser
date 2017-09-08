<?php

class Warning {
    public $message;
    public $warnLvl;
    // private $

    public function __construct($message, $warnLvl = 10) {
        $this->message = $message;
        $this->warnLvl = $warnLvl;
    }
}