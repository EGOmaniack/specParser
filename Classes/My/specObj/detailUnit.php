<?php

/**
 * @property  addAssem
 */
class DetailUnit extends SpecObject implements initable, iErrorChecker {

    private $drawingFormat; //Формат чертежа
    private $material;
    public $isIspolnenie;
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
        $this->isIspolnenie = false;
        $this->material = 'unknown';
        $this->warnings = [];
        $this->trustlevel = 0;
    }
    public function init (array $info): void {

        if(preg_match("/[-0-9]{3,3}$/", $info['designation']))
            $this->isIspolnenie = true;
        $this->drawingFormat = $info['drawingFormat'];
        $this->designation = $info['designation'];
        $this->name = $info['name'];
        $this->notation = $info['notation'];
        $this->material = $info['material'];
        $this->checkErrors();
    }

    public function checkErrors(): void {
        $this->warnings = [];
        if($this->drawingFormat == '' && !$this->isIspolnenie)
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

    /**
     * @return mixed
     */
    public function getDrawingFormat()
    {
        return $this->drawingFormat;
    }
}