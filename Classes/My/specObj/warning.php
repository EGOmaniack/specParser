<?php

class Warning {
    public $message;
    public $warnLvl;
    public $code;

//    public $warns;

    public function __construct($code) {
        $this->code = $code;

        $warns = Array(
            "noFormat" =>   Array("warnlvl" => 20,  "message" => "Формат чертежа, не задан"),
            "noName" =>     Array("warnlvl" => 5,   "message" => "Не задано название"),
            "noDesign" =>   Array("warnlvl" => 70,   "message" => "Шифр, не задан"),
            "noMaterial" => Array("warnlvl" => 10,   "message" => "Не заполнен материал"),
            "noMeasure" => Array("warnlvl" => 20,   "message" => "не указана единица измерения")
        );

        $this->warnLvl = $warns[$code]['warnlvl'];
        $this->message = $warns[$code]['message'];
    }
}