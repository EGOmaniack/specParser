<?php
if (!defined('MYCLASSES_ROOT')) {
    define('MYCLASSES_ROOT', str_replace("\\", "/", dirname(__FILE__) . '/') );
    require(MYCLASSES_ROOT . 'parser/assembleyUnit.php');
    require(MYCLASSES_ROOT . 'parser/detailUnit.php');
    require(MYCLASSES_ROOT . 'parser/parser.php');
    require(MYCLASSES_ROOT . 'helpers/strToLowerUtf.php');
    require(MYCLASSES_ROOT . 'parser/warning.php');
    require(MYCLASSES_ROOT . 'parser/doc.php');
}