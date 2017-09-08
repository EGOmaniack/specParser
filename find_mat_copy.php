<?php
ini_set('display_errors', 0) ;
ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);
require_once('saveExcel.php');
require_once('BDgets.php');
require_once('Classes/PHPExcel.php');
include_once 'Classes/PHPExcel/IOFactory.php';

$file = $_FILES['fileToUpload']['tmp_name'];
$tfile = file_get_contents($_FILES['fileToUpload']['tmp_name']);
$date = date("d-m-Y-g-i-s");
//echo $date;
file_put_contents('./uploads/'.$date.str_replace(":","x",$_SERVER['REMOTE_ADDR']).".xlsx", $tfile);

$objreader = PHPExcel_IOFactory::createReader('Excel2007');//создали ридер
$objreader->setReadDataOnly(true); //только на чтение файла
$objExcel = $objreader->load($file);
$objExcel ->setActiveSheetIndex(3);
$objWorkSheet = $objExcel->getActiveSheet(); //Вся таблица 4ого листа
$higestRow = $objWorkSheet->getHighestRow(); // Слишком много перезапишем
$filename = $_FILES['fileToUpload']['name'];
$Data; // Все агрегаты

$dubstr = BDgetDictionary(2);/*Двустрочные материалы*/
$onestr = BDgetDictionary(1);/*Однострочные материалы*/



//  Обрабатывем таблицу как обычныйфайл с калькуляциями без добавления n "имя агрегата" "количество"
$agregat['name'] = $filename;
$agregat['options']['number'] = 1;
$agregat['matlist'] = create_block(0, $higestRow, $objWorkSheet, $dubstr,$onestr );
$Data[]=$agregat;

if(count($Data) == 0) unset($Data);
// если такого не оказалось то удаляем (просто что-бы не было пустого агрегата в массиве) и пытаемся найти по формату  n "имя агрегата" "количество"
for($i = 0, $q = 0; $i < $higestRow ; $i++  ) {
    $ncheck = $objWorkSheet->getCellByColumnAndRow(0,$i)->getValue();
    if($ncheck == 'n'){
        $q++;
        $agregat['name'] = $objWorkSheet->getCellByColumnAndRow(1,$i)->getValue();
        $agregat['options']['number'] = $objWorkSheet->getCellByColumnAndRow(2,$i)->getValue();
        $agregat['matlist'] = create_block($i+1, $higestRow, $objWorkSheet, $dubstr,$onestr );
        unset($ncheck);
        $Data[$q]=$agregat;
    }
}

//Создаем сущность матлиста($matlist)
function create_block($startRow,$maxrow, $sheet, $spr2,$spr ){
    for ($j = $startRow, $pust = 0; $j < $maxrow; $j++) {
        $val = $sheet->getCellByColumnAndRow(1, $j)->getValue();
        if (strlen($val) > 0) {
            $val = killSpaces($val);
            $pref = explode(' ', $val);


            if (in_array(strtolower_utf8($pref[0]), $spr2)) {

                $matlist[] = save_mat(2, $j, $sheet);
                unset($element);
                //$j++;   /*Есть ситуации, когда подкладка пишется в одну строку и сразу за ней идет след. материал.*/
                /*Тогда при пропуске строки след материал не учитывается */
            } elseif (in_array(strtolower_utf8($pref[0]), $spr)) {

                $matlist[] = save_mat(1, $j, $sheet);
                unset($element);

            }
            switch (strtolower_utf8($pref[0])) {
                case "цепь":
                    $matlist[] = save_mat(2,$j,$sheet);
                    break;
                case "проволока":
                    //Заплата /В данном документе все равно нет совпадений по проволоке
                    $matlist[] = save_mat(2,$j,$sheet);
                    break;
            }
        }

        if ($sheet->getCellByColumnAndRow(0, $j)->getValue() == 'n') {
            return $matlist;
        }
        // переписывем макс число строк и прерываем цикл
        if(strlen($sheet->getCellByColumnAndRow(1,$j)->getValue()) == 0 ){
            if($pust == 50){
                return $matlist;
            }else{$pust++;}

        }else{$pust = 0;}
    }
}

// if(count($Data) == 0){
//     $agregat['name'] = $_FILES['fileToUpload']['name'];
//         $agregat['options']['number'] = 1;
//         $agregat['matlist'] = create_block(0, $higestRow, $objWorkSheet, $dubstr,$onestr );
//         $Data[]=$agregat;
// }
//сохранение материала в matlist из Excel
function save_mat($strok, $i ,$sheet){

    $elem1['name'] = killSpaces($sheet->getCellByColumnAndRow(1,$i)->getValue());//строка с названием и Excel как она есть
    $elem1['sname']= strtolower_utf8(explode(" ", $elem1['name'])[0]);
    $elem1['ei'] = $sheet->getCellByColumnAndRow(5,$i)->getValue();//единица измерения
    $elem1['mass'] = get_mass($sheet->getCellByColumnAndRow(8,$i)->getValue());//масса материала в проектк
    $elem1['cost'] = (float)$sheet->getCellByColumnAndRow(9,$i)->getValue();// стоимость tltybws vfnthbfkf
    $elem1['size'] = getmatsize($elem1['name']);

    //if($elem1['mass'] == 4.49) var_dump($elem1);

    if($strok == 2) {
        $mat = $sheet->getCellByColumnAndRow(1,$i+1)->getValue();//марка материала
        $mat = killSpaces($mat);
        $mat = newgost($mat);
        $elem1['mat'] = $mat;
    }
    return $elem1;
}

function get_mass ($mass){
    $mass = killSpaces($mass);
    $mass = str_replace(',','.',$mass);
    return (float) $mass;
}

function getmatsize($name){
    

    if(strpos($name, "ГОСТ")>0){
        $name = substr($name,0, strpos($name, "ГОСТ"));
    }
    // $name = killSpaces($name);//уже сделано
    if(strpos($name, "-В")>0){/*у всего что -В вытаскиваем значение*/
        $value = str_replace(',', '.', $name);
        if(strtolower_utf8(explode(' ',$value)[0]) != "уголок") {
            $value = preg_replace("/[^0-9\.]/", "", $value);
            $value = (float)$value;
            return $value;
        }
    }
    if(strpos($name, "II")>0){/*у всего что II вытаскиваем значение*/
//        echo ($name."<br>");
        $value = substr($name,strpos($name,"II"));
//        echo $value."<br>";
        $value = str_replace(',', '.', $value);
        $value = preg_replace("/[^0-9\.]/", "", $value);
        $value = (int)$value;
        return $value;
    }
    if(strtolower_utf8(explode(' ',$name)[0]) == "уголок"){
       // var_dump ($name);
        if(strpos($name, "х")>0){$value = explode("х", $name);} /*русское х*/
        else{$value = explode("x", $name);} /*английсое x*/
        foreach ($value as $key => $str) {
            $str = preg_replace("/[^0-9\.]/", "", $str);
            $value[$key] = $str;
        }
        if($value[0] == $value[1]) {
            $value[1] = $value[2];
            unset($value[2]);
        }

        return $value;
    }
//    if(strtolower_utf8(explode(' ',$name))[0] == "труба") echo $name."<br>";
    $name = preg_replace("/[^0-9\.]/", "", $name);
    $name = (float)$name;
    //var_dump($name);
    return $name;
}
//var_dump($Data);
/*Выискиваем старые госты  ГОСТ 1050-88 -> 2003; ГОСТ 535-88 -> 2005
* ГОСТ 51685-2000 -> 2013; */
function newgost($str){
        //Замена номеров госта в наименовании материала
    $str = str_replace("1050-88","1050-2003",$str);
    $str = str_replace("380-2005","380-2008",$str);
    $str = str_replace('535-88','535-2005',$str);
    $str = str_replace('51685-2000','51685-2013',$str);
        return $str;
}//  /\s+/

/*Удаляем пробелы вначале фразы*/
function killSpaces($str){
    $exp = explode(' ',$str);
    $co = count($exp);
    for($i = 0; $i < $co; $i++){
        if($exp[$i] == ''){
            unset($exp[$i]);
        }else{
            break;}
    }
    $str = implode(' ', $exp);
    return $str;
}

//Собираем новый массив материалов
$count;
$matmerge;
//Объединение все в единый массив с проверкой копий
foreach ($Data as $key => $agregat){
    for($i = 0; $i < count($agregat['matlist']);$i++){
        //if($agregat['matlist'][$i]['sname'] == 'труба') var_dump($agregat['matlist'][$i]);
        if($agregat['matlist'][$i]['sname'] == 'лист' &&
            $agregat['matlist'][$i]['mass'] == 4.49 ) {
                //var_dump($agregat['matlist'][$i]);
                //$count++;
            }
        $copy = false;
        $y = 0;
        //if($agregat['matlist'][$i]['sname'] == "шестигранник" && $agregat['matlist'][$i]['size'] == 12) var_dump ($agregat['matlist'][$i]);
        for($j = 0; $j < count($matmerge);$j++){   /*Смотрим есть ли копия очередного материала в matmerge*/
            /*if(preg_replace('/\s+/', '', strtolower_utf8($matmerge[$j]['name'])) == preg_replace('/\s+/', '', strtolower_utf8($value['matlist'][$i]['name'])) &&
                preg_replace('/\s+/', '', strtolower_utf8($matmerge[$j]['mat'])) == preg_replace('/\s+/', '', strtolower_utf8($value['matlist'][$i]['mat']))) {*/
            if($matmerge[$j]['sname'] == $agregat['matlist'][$i]['sname'] &&
                preg_replace('/\s+/', '', strtolower_utf8($matmerge[$j]['mat'])) == preg_replace('/\s+/', '', strtolower_utf8($agregat['matlist'][$i]['mat'])) &&
                $agregat['matlist'][$i] != 0 &&
                 $matmerge[$j]['size'] == $agregat['matlist'][$i]['size']) {
                   
                    //$count++; /*Считаем количество учтенных совпадений*/
                    $copy = true;/*Нашли совпадение*/
                    $y = $j;/*Запоминаем порядковый номер совпадения*/
                    break;/*больше не проверяем раз уж нашли одно совпадение*/
                // echo 'Есть...'.$matmerge[$j]['name'].'/'.$matmerge[$j]['size'].'<br/>'
                //     . 'Копия'.$agregat['matlist'][$i]['name'].'/'.$agregat['matlist'][$i]['size'].' масса= '.$Data[$key]['matlist'][$i]['mass'].'<br/><br/>';
            }
        }
        $newmat = $agregat['matlist'][$i];
        $newmat['mass'] *= $agregat['options']['number'];

        if(!$copy) {
            $matmerge[] = $newmat;
        }else{
            $matmerge[$y]['mass'] += $newmat['mass'];
        }
        unset($newmat);
    }
}
//echo $count;
//var_dump ($matmerge);

function strtolower_utf8($string){
    $convert_to = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
        "v", "w", "x", "y", "z", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
        "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "а", "б", "в", "г", "д", "е", "ё", "ж",
        "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы",
        "ь", "э", "ю", "я"
    );
    $convert_from = array(
        "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
        "V", "W", "X", "Y", "Z", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï",
        "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж",
        "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ъ",
        "Ь", "Э", "Ю", "Я"
    );

    return str_replace($convert_from, $convert_to, $string);
}

//var_dump($Data);
//var_dump($matmerge);
  saveExcel($matmerge,$dubstr, $onestr);
//makeDataTable($Data);
//makemergeTable($matmerge);

function makeDataTable ($Data){
    //Рисуем таблицу
    $table ='<table border="1">
            <caption>Таблица содержания файла</caption>
            <tr>
            <th>Наименование агрегата</th>
            <th>Кол-во <br/> агрегатов</th>
            <th>Кол-во <br/> материалов</th>
            </tr>';
    for($i=0 ;$i <= count($Data) ;$i++){
    $table .='<tr><td>'.$Data[$i]['name'].'</td><td>'.$Data[$i]['options']['number'].'</td><td>'.count($Data[$i]['matlist']).'</td></tr>';
    }
    $table .='</table><br><br>';

    echo $table;
}

function makemergeTable($matmerge){
    //Рисуем таблицу всех материалов
    $table2 ='<table border="1">
            <caption>Таблица материалов</caption>
            <tr>
            <th>№</th>
            <th>Наименование<br> материала</th>
            <th>Марка</th>
            <th>обозначение <br/> стандарта или <br/> тех. Условия</th>
            <th>Код <br/> материала</th>
            <th>материал</th>
            <th>Еденица <br/> измерения</th>
            <th>Код <br/> еденицы <br/> измерения</th>
            <th>Норма <br/> расхода</th>
            <th>масса</th>
            <th>Стоимость <br/> еденицы <br/> измерения</th>
            <th>Сумма <br/> на <br/> комплект</th>
            </tr>';
    for($i=0 ;$i < count($matmerge) ;$i++){
        $table2 .='<tr><td>'.$i.'</td><td>'.$matmerge[$i]['name'].'</td>'.'<td></td><td></td><td></td><td></td>'.'<td>'.
            $matmerge[$i]['ei'].'</td><td></td><td></td><td>'.round($matmerge[$i]['mass'], 2).'</td><td>'.$matmerge[$i]['cost'].'</td><td>'.
            $matmerge[$i]['mass'] * $matmerge[$i]['cost'].'</td></tr>';
        if($matmerge[$i]['mat'] != null){$table2 .='<tr><td>'.'-'.'</td><td class = "mat">'.$matmerge[$i]['mat'].'</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';}
    }
    $table2 .='</table><br><br>';

    echo $table2;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/main.css?2"/>
</head>

<body>

<!--<input type="button" value="Сравнить">-->



</body>
</html>