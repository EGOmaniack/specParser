<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 028 28.09.17
 * Time: 10:35
 */

class Purchased extends SpecObject implements initable {

    public function __construct(){
        $this->warnings = [];
    }

    public function init (array $info) {
        $this->name = $info['name'];
        $this->notation = $info['notation'];
        $this->checkErrors();
    }
}