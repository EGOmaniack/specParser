<?php
class AssembleyUnit {

    private $drawingFormat; //Формат чертежа
    private $designation; //обозначение
    private $name; //название сборочной единицы
    private $docs;
    private $warnings;

    private $assembleys; //входящие сборки
    private $detailUnits; //входящие детали

    public function __construct () {
        $this->docs = [];
        $this->assembleys = [];
        $this->warnings = [];
        $this->detailUnits = [];
    }
    public function init ($drawingFormat, $designation, $name) {
        $this->drawingFormat = $drawingFormat;
        $this->designation = $designation;
        $this->name = $name;

        if($this->drawingFormat === null)
            $this->addWarning(new Warning("Формат чертежа, сборочной единицы, не задан"));
        if($this->name === '')
            $this->addWarning(new Warning("Не задано имя сборочной единицы", 5));
        if($this->designation === null)
            $this->addWarning(new Warning("Шифр, сборочной единицы, не задан", 70));
    }
    public function addDoc(Document $doc) {
        $this->docs[] = $doc;
    }
    public function addWarning(Warning $warn) {
        if(!in_array($warn, $this->warnings))
            $this->warnings[] = $warn;
    }
}