<?php

/**
 * @property  addAssem
 */
class DetailUnit {

    private $drawingFormat; //Формат чертежа
    private $designation; //обозначение
    private $name; //название сборочной единицы
    private $warnings;
    private $material;
    /*
     * trust lvl
     * 0 - назначается при автоматическом парсинге
     * 10 - резерв на варианты из бд с таким же шифром
     * 20 - заполнено/одобрено пользователем
     * 30 - есть полный апрув от ревьюверов (максимальное доверие)
     *
     */
    private $trustlevel;


    public function __construct () {
        $this->material = 'unknow';
        $this->warnings = [];
        $this->trustlevel = 0;
    }
    public function init ($drawingFormat, $designation, $name, $material) {
        $this->drawingFormat = $drawingFormat;
        $this->designation = $designation;
        $this->name = $name;
        $this->material = $material;

        if($this->drawingFormat === null)
            $this->addWarning(new Warning('noFormat'));
        if($this->name === '')
            $this->addWarning(new Warning('noName'));
        if($this->designation === null)
            $this->addWarning(new Warning('noDesign'));
        if($this->material === null)
            $this->addWarning(new Warning('noMaterial'));
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