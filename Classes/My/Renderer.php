<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 019 19.09.17
 * Time: 14:44
 */

class Renderer {
    private $mode;
    private $data;

    public function __construct(string $mode = "default") {
        $this->mode = $mode;
    }
    public function loadData($data) {
        $this->data = $data;
    }
    public function render() {

    }
}