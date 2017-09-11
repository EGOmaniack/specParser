<?php

/**
 * @property  addAssem
 */
class AssembleyUnit {

    private $drawingFormat; //Формат чертежа
    private $designation; //обозначение
    private $name; //название сборочной единицы
    private $docs;
    private $warnings;
    /*
     * trust lvl
     * 0 - назначается при автоматическом парсинге
     * 10 - резерв на варианты из бд с таким же шифром
     * 20 - заполнено/одобрено пользователем
     * 30 - есть полный апрув от ревьюверов (максимальное доверие)
     *
     */
    private $trustlevel;

    private $assembleys; //входящие сборки
    private $detailUnits; //входящие детали

    public function __construct () {
        $this->docs = [];
        $this->assembleys = [];
        $this->warnings = [];
        $this->detailUnits = [];
        $this->trustlevel = 0;
    }
    public function init ($drawingFormat, $designation, $name) {
        $this->drawingFormat = $drawingFormat;
        $this->designation = $designation;
        $this->name = $name;

        if($this->drawingFormat === null)
            $this->addWarning(new Warning('noFormat'));
        if($this->name === '')
            $this->addWarning(new Warning('noName'));
        if($this->designation === null)
            $this->addWarning(new Warning('noDesign'));
    }
    public function addDoc(Document $doc) {
        $this->docs[] = $doc;
    }
    public function addAssemb(AssembleyUnit $assem, $count,  $specFormat) {
        $this->assembleys[] = Array(
            "count" => $count,
            "specFormat" => $specFormat,
            "unit" =>$assem
        );
    }
    public function  addDetailUnit($detailInfo) {
        $this->detailUnits[] = Array(
            "count" => $detailInfo['count'],
            "unit" => $detailInfo['detailUnit']
        );
    }
    public function addWarning(Warning $warn) {
        if(!in_array($warn, $this->warnings))
            $this->warnings[] = $warn;
    }

    /**
     * @return int
     */
    public function getTrustlevel()
    {
        return $this->trustlevel;
    }
}