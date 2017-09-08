<?php
class Document {
    public $drawingFormat; //Формат чертежа
    public $designation; //обозначение
    public $name; //название документа

    public function __construct($designation, $drawingFormat = "", $name = "") {
        $this->designation = $designation;
        $this->drawingFormat =$drawingFormat;
        $this->name = $name;
    }
}