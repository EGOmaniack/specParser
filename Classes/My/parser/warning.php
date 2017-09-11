<?php

class Warning {
    public $message;
    public $warnLvl;
    public $code;

//    public $warns;

    public function __construct($code) {
        $this->code = $code;

        $warns = Array(
            "noFormat" =>   Array("warnlvl" => 10,  "message" => "Формат чертежа, не задан"),
            "noName" =>     Array("warnlvl" => 5,   "message" => "Не задано имя сборочной единицы"),
            "noDesign" =>   Array("warnlvl" => 70,   "message" => "Шифр, не задан"),
            "noMaterial" => Array("warnlvl" => 10,   "message" => "Не заполнен материал")
        );

        $this->warnLvl = $warns[$code]['warnlvl'];
        $this->message = $warns[$code]['message'];
    }
}