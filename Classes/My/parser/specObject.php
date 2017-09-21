<?php
/* Объект спецификации  */

class specObject {
    protected $name;
    protected $designation;
    protected $warnings;

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

    public function getWarnings(): array {
        if(count($this->warnings) > 0) {
            return array(
                "designation" => $this->getDesignation(),
                "name" => $this->getName(),
                "warnings" => $this->warnings
            );
        } else {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getDesignation()
    {
        return $this->designation;
    }

}