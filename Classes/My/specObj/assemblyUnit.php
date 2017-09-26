<?php

/**
 * @property  addAssem
 */
class AssemblyUnit extends SpecObject implements initable, iErrorChecker {

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
//    public $me; //bool Используется для описаниня МЭ. Для сборок false
    private $assemblys; //входящие сборки
    private $detailUnits; //входящие детали
    private $specFormat; //формат спецификации
    private $standartUnits; //Стандартные изделия

    public function __construct () {
        $this->docs = [];
        $this->assemblys = [];
        $this->warnings = [];
        $this->detailUnits = [];
        $this->standartUnits = [];
        $this->trustlevel = 0;
        $this->specFormat = '';
    }

    /**
     * @return mixed
     */
    public function getAssemblys() {
        return $this->assemblys;
    }

    /**
     * @param string $specFormat
     */
    public function setSpecFormat($specFormat) {
        $this->specFormat = $specFormat;
        $this->checkErrors();
    }

    public function checkErrors(): void {
        $this->warnings = [];
        if($this->drawingFormat == null)
            $this->addWarning(new Warning('noFormat'));
        if($this->name === '')
            $this->addWarning(new Warning('noName'));
        if($this->designation === null)
            $this->addWarning(new Warning('noDesign'));
    }
    public function init (array $info): void {
        $this->drawingFormat = $info['drawingFormat'];
        $this->designation = $info['designation'];
        $this->name = $info['name'];
        $this->notation = $info['notation'];
        $this->checkErrors();
    }
    public function addDoc(Document $doc): void {
        $this->docs[] = $doc;
    }
    public function addAssemb(AssemblyUnit $assem, $count, $posNumber = null): void {
        $this->assemblys[] = Array(
            "count" => $count,
            "unit" =>$assem,
            "posNum" => $posNumber
        );
    }

    public function  addDetailUnit($detailInfo): void {
        $this->detailUnits[] = Array(
            "count" => $detailInfo['count'],
            "unit" => $detailInfo['unit'],
            "posNum" => $detailInfo['posNum']
        );
    }

    /**
     * @return int
     */
    public function getTrustlevel() {
        return $this->trustlevel;
    }

    /**
     * @return mixed
     */
    public function getDrawingFormat() {
        return $this->drawingFormat;
    }

    /**
     * @return array
     */
    public function getDetailUnits(): array {
        return $this->detailUnits;
    }

    /**
     * @param $stUInfo
     */
    public function addStandartUnit($stUInfo): void {
        $this->standartUnits[] = array(
            'unit' => $stUInfo['unit'],
            'count' => $stUInfo['count'],
            "posNum" => $stUInfo['posNum']
        );
    }

    /**
     * @return array
     */
    public function getStandartUnits(): array
    {
        return $this->standartUnits;
    }


}