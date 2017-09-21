<?php
// ini_set('display_errors', 0) ;
ini_set('xdebug.var_display_max_depth', 8);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

include '../../Classes/My/parser.php';
include '../../Classes/PHPExcel.php';

if(isset($_FILES['specs']) > 0) {
    $pfiles=[];
    $filesCount = count($_FILES['specs']['name']);
    $assemblyes = [];
    for($i=0; $i<$filesCount; $i++) {
        //Нужны только excel файлы
        $ExcelRegex = '/([a-z-.\/]+)(excel)$/';
        if(preg_match($ExcelRegex, $_FILES['specs']['type'][$i])) {
            $pfile=[];
            $pfile['name'] = $_FILES['specs']['name'][$i];
            $pfile['type'] = $_FILES['specs']['type'][$i];
            $pfile['tmp_name'] = $_FILES['specs']['tmp_name'][$i];
            $pfile['error'] = $_FILES['specs']['error'][$i];
            $pfile['size'] = $_FILES['specs']['size'][$i];

            $pfiles[] = $pfile;
            unset($pfile);
        }
    }
    $objreader = PHPExcel_IOFactory::createReader('Excel2007');//создали ридер

    foreach ($pfiles as $file) {
        $objreader->setReadDataOnly(true); //только на чтение файла
        $objExcel = $objreader->load($file['tmp_name']);
        $objExcel ->setActiveSheetIndex(0);
        $objWorkSheet = $objExcel->getActiveSheet(); //Вся таблица 1ого листа

        $parcer = new Parser($objWorkSheet);
        $assemblyes[] = $parcer->parceAll();
    }
    echo json_encode($assemblyes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
//    var_dump($assemblyes);

} else {
    die("Файлов нет, возможно вы пытветесь отправить слишком большой файл");
}
?>