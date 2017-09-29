<?php
/* Объект спецификации  */

class SpecObject {
    protected $name;
    protected $designation;
    protected $warnings;
    protected $notation;

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param Warning $warn
     */
    public function addWarning(Warning $warn) {
        if(!in_array($warn, $this->warnings))
            $this->warnings[] = $warn;
    }

    public function getWarnings($minWarnLvl = 0) {
        $result = null;
        if(count($this->warnings) > 0) {
            foreach ($this->warnings as $warning) {
                if($warning->warnLvl > $minWarnLvl) {
                    $result[] = $warning;
                }
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @return string
     */
    public function getNotation()
    {
        return $this->notation;
    }

    /**
     * @param string $designation
     */
    public function setDesignation(string $designation)
    {
        $this->designation = $designation;
    }

}