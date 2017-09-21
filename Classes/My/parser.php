<?php
if (!defined('MYCLASSES_ROOT')) {
    define('MYCLASSES_ROOT', str_replace("\\", "/", dirname(__FILE__) . '/') );
    include(MYCLASSES_ROOT . './parser/specObject.php');
    include(MYCLASSES_ROOT . './parser/assemblyUnit.php');
    include(MYCLASSES_ROOT . './parser/detailUnit.php');
    include(MYCLASSES_ROOT . './parser/doc.php');
    include(MYCLASSES_ROOT . './parser/warning.php');
    include(MYCLASSES_ROOT . './parser/parser.php');
    include(MYCLASSES_ROOT . './helpers/helper.php');
    include(MYCLASSES_ROOT . './parser/Sorter.php');
    include(MYCLASSES_ROOT . './parser/Renderer.php');
}