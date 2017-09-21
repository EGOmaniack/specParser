<?php

class Document extends SpecObject
{
    public $drawingFormat; //Формат чертежа

    public function __construct($drawingFormat = "", $designation, $name = "")
    {
        $this->designation = $designation;
        $this->drawingFormat = $drawingFormat;
        $this->name = $name;
    }
}

