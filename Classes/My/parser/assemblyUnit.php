<?php

/**
 * @property  addAssem
 */
class AssemblyUnit extends SpecObject {

    private $drawingFormat; //Формат чертежа
    private $docs;
    /*
     * trust lvl
     * 0 - назначается при автоматическом парсинге
     * 10 - резерв на варианты из бд с таким же шифром
     * 20 - заполнено/одобрено пользователем
     * 30 - есть полный апрув от ревьюверов (максимальное доверие)
     *
     */
    private $trustlevel;

    private $assemblys; //входящие сборки
    private $detailUnits; //входящие детали
    private $specFormat;

    public function __construct () {
        $this->name = "";
        $this->docs = [];
        $this->assemblys = [];
        $this->warnings = [];
        $this->detailUnits = [];
        $this->trustlevel = 0;
        $this->specFormat = '';
    }

    /**
     * @return mixed
     */
    public function getAssemblys()
    {
        return $this->assemblys;
    }

    /**
     * @param string $specFormat
     */
    public function setSpecFormat($specFormat) {
        $this->specFormat = $specFormat;
        $this->checkErrors();
    }

    private function checkErrors() {
        $this->warnings = [];
        if($this->drawingFormat == null)
            $this->addWarning(new Warning('noFormat'));
        if($this->name === '')
            $this->addWarning(new Warning('noName'));
        if($this->designation === null)
            $this->addWarning(new Warning('noDesign'));
    }
    public function init ($drawingFormat, $designation, $name) {
        $this->drawingFormat = $drawingFormat;
        $this->designation = $designation;
        $this->name = $name;
        $this->checkErrors();
    }
    public function addDoc(Document $doc) {
        $this->docs[] = $doc;
    }
    public function addAssemb(AssemblyUnit $assem, $count) {
        $this->assemblys[] = Array(
            "count" => $count,
            "unit" =>$assem
        );
    }

    public function  addDetailUnit($detailInfo) {
        $this->detailUnits[] = Array(
            "count" => $detailInfo['count'],
            "unit" => $detailInfo['detailUnit']
        );
    }

    /**
     * @return int
     */
    public function getTrustlevel()
    {
        return $this->trustlevel;
    }


}