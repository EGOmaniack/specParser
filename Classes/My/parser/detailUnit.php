<?php

/**
 * @property  addAssem
 */
class DetailUnit extends SpecObject {

    private $drawingFormat; //Формат чертежа
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
        $this->material = 'unknown';
        $this->warnings = [];
        $this->trustlevel = 0;
    }
    public function init ($drawingFormat, $designation, $name, $material) {
        $this->drawingFormat = $drawingFormat;
        $this->designation = $designation;
        $this->name = $name;
        $this->material = $material;
        $this->checkWarnings();
    }

    public function checkWarnings() {
        $this->warnings = [];
        if($this->drawingFormat == '')
            $this->addWarning(new Warning('noFormat'));
        if($this->name == '')
            $this->addWarning(new Warning('noName'));
        if($this->designation == '')
            $this->addWarning(new Warning('noDesign'));
        if($this->material === null)
            $this->addWarning(new Warning('noMaterial'));
    }

    /**
     * @return int
     */
    public function getTrustlevel()
    {
        return $this->trustlevel;
    }
}