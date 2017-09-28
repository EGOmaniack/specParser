<?php

interface iSpecObject {
    public function getName();
    public function getDesignation();
    public function getWarnings();
}

interface initable {
    public function init(array $info): void;
}

interface iErrorChecker {
    public function checkErrors (): void;
}