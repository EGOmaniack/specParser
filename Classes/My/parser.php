<?php
if (!defined('MYCLASSES_ROOT')) {
    define('MYCLASSES_ROOT', str_replace("\\", "/", dirname(__FILE__) . '/') );
    include(MYCLASSES_ROOT . 'parser/interfaces.php');
    include(MYCLASSES_ROOT . 'Dictionary/dictionary.php');

    include(MYCLASSES_ROOT . 'specObj/specObject.php');
    include(MYCLASSES_ROOT . 'specObj/purchased.php');
    include(MYCLASSES_ROOT . 'specObj/assemblyUnit.php');
    include(MYCLASSES_ROOT . 'specObj/detailUnit.php');
    include(MYCLASSES_ROOT . 'specObj/standartUnit.php');
    include(MYCLASSES_ROOT . 'specObj/doc.php');
    include(MYCLASSES_ROOT . 'specObj/warning.php');

    include(MYCLASSES_ROOT . 'parser/parser.php');
    include(MYCLASSES_ROOT . 'parser/Sorter.php');
    include(MYCLASSES_ROOT . 'parser/Renderer.php');

    include(MYCLASSES_ROOT . 'helpers/helper.php');
}