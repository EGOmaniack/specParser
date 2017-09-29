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
    private $otherUnits; //Прочие изделия
    private $matUnits;

    public function __construct () {
        $this->docs = [];
        $this->assemblys = [];
        $this->warnings = [];
        $this->detailUnits = [];
        $this->standartUnits = [];
        $this->otherUnits = [];
        $this->matUnits = [];
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

    public function checkErrors() {
        $this->warnings = [];
        if($this->drawingFormat == null)
            $this->addWarning(new Warning('noFormat'));
        if($this->name === '')
            $this->addWarning(new Warning('noName'));
        if($this->designation === null)
            $this->addWarning(new Warning('noDesign'));
    }
    public function init (array $info) {
        $this->drawingFormat = $info['drawingFormat'];
        $this->designation = $info['designation'];
        $this->name = $info['name'];
        $this->notation = $info['notation'];
        $this->checkErrors();
    }
    public function addDoc(Document $doc) {
        $this->docs[] = $doc;
    }
    public function addAssemb(AssemblyUnit $assem, $count, $posNumber = null) {
        $this->assemblys[] = Array(
            "count" => $count,
            "unit" =>$assem,
            "posNum" => $posNumber
        );
    }

    public function  addDetailUnit($detailInfo) {
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
    public function addStandartUnit($stUInfo) {
        $this->standartUnits[] = array(
            'unit' => $stUInfo['unit'],
            'count' => $stUInfo['count'],
            "posNum" => $stUInfo['posNum']
        );
    }
    public function addArrayOfStUnits(array $stUnits) {
        if(count($stUnits) > 0) {
            foreach ($stUnits as $st) {
                $this->addStandartUnit($st);
            }
        }
    }

    /**
     * @return array
     */
    public function getStandartUnits(): array
    {
        return $this->standartUnits;
    }

    /**
     * @return mixed
     */
    public function getOtherUnits()
    {
        return $this->otherUnits;
    }

    /**
     * @param mixed $otherUnits
     */
    public function addOtherUnit($stUInfo) {
        $this->otherUnits[] = array(
            'unit' => $stUInfo['unit'],
            'count' => $stUInfo['count'],
            "posNum" => $stUInfo['posNum']
        );
    }
    public function addArrayOfOthUnits(array $otherUnits) {
        if(count($otherUnits) > 0) {
            foreach ($otherUnits as $st) {
                $this->addOtherUnit($st);
            }
        }
    }

    /**
     * @return array
     */
    public function getMatUnits(): array
    {
        return $this->matUnits;
    }

    /**
     * @param mixed $otherUnits
     */
    public function addMatUnit($stUInfo) {
        $this->matUnits[] = array(
            'unit' => $stUInfo['unit'],
            'count' => $stUInfo['count'],
            "posNum" => $stUInfo['posNum']
        );
    }
    public function addArrayOfMatUnits(array $matUnit) {
        if(count($matUnit) > 0) {
            foreach ($matUnit as $st) {
                $this->addMatUnit($st);
            }
        }
    }


}