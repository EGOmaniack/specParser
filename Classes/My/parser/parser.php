<?php

class Parser {
    private $excelObj;
    private $height;
    private $assembly; //Сборочная единица описываемая в спецификации
    private $blankAssemblys; //Сборочные единицы упомянутые в спецификации и кол-во
    private $blankdetails; // детали упомянутые  в спецификации и кол-во
    private $details; // детали упомянутые  в спецификации
    private $activeDetail;
    private $helper;
    private $sections;  // граници всех разделов плоской части? спецификации
    private $fileName;
    private $me;
    private $specificationInfo;
    private $index;
    private $forceDesign;

    public function __construct(PHPExcel_Worksheet $excelObj, string $fileName, int $index = 0, string $forceDesign = null) {
        $this->index = $index;
        $this->forceDesign = $forceDesign;
        $this->assembly = new AssemblyUnit();
        $this->excelObj = $excelObj;
        $this->height = $excelObj->getHighestRow();
        $this->blankAssemblys = [];
        $this->blankdetails = [];
        $this->details = [];
        $this->helper = new Helper();
        $this->sections = [];
        $this->fileName = $fileName;

        $this->getSpecificationInfo();
        $this->initSections();
        echo "File - " . $fileName;
    }
    private function MeDetect(): bool {
        $result = false;
        if(preg_match("/Устанавливают/",$this->getAt(4, 3))){
            $result = true;
            $designation = $this->getAt(4, 3);
            $designation = preg_replace("/(Устанавливают)+\s+(по)/", null, $designation);
            $designation = preg_replace("/\s(МЭ)$/", null, $designation);
            $this->assembly->init(array(
                "drawingFormat" => null,
                "designation" => trim($designation),
                "name" => null,
                "notation" => null
                )
            );
        }
        return $result;
    }
    private function getSpecificationInfo () {
        $result = array(
            "count" => 1,
            "diffLineNum" => (int) $this->height,
            "assemblys" => array()
        );
        for ($i=1; $i <= $this->height; $i++) {
            $line = $this->getAt(3, $i);
            $pattern = "/(П|п)(еременные)\s(данные)([\sа-я:]*+)/"; // Нашли "Переменные данные для исполнений:"
            if(preg_match($pattern, $line)) {
                //Выяснилось что тут более одной сборки описывается
                $result['count'] += 1;
                $count = 0;
                for($j = $i; $j <= $this->height; $j++ ) {
                    $jline = $this->getAt(4, $j);
                    if(preg_match("/[А-Я]{1,10}\s[0-9.]{3,}(-[0-9][0-9])?/", $jline)) {

                        if($count > 0)
                            $result['assemblys'][$count-1]['endLine'] = ($j - 1);
                        $count++;
                        $result['assemblys'][] = array(
                            "designation" => $jline,
                            "startLine" => ($j+1),
                            "endLine" => (int) $this->height
                        );
                    }
                }
                $result['count'] = $count;
                $result['diffLineNum'] = $i;
                break;
            }
        }
        $this->specificationInfo = $result;
    }
    private function initSections () {
        $i = 1;
        if($this->specificationInfo['count'] !== 0) {
//            var_dump($this->specificationInfo);
            if($this->index > 0) {
                //достаем не общую информацию
                $i = $this->specificationInfo['assemblys'][$this->index]['startLine'];
                echo "i = " . $i;
            }
        } else {
            $this->me = $this->MeDetect();
        }
        $lastSection = null;
        $cats = array(
            "DOC" => "документация",
            "KOMP" => "комплексы",
            "SBED" => "сборочные единицы",
            "DETAILS" => "детали",
            "STANDART" => "стандартные изделия",
            "OTHER" => "прочие изделия",
            "MATS" => "материалы",
            "KOMPLECTI" => "комплекты"
        );
        for(; $i <= $this->specificationInfo['diffLineNum']; $i++) {
            $toLowName = trim($this->helper->strtolower_utf8($this->getAt(4, $i)));
            if(in_array($toLowName, $cats)) { // Нашли какой-то раздел
                if(isset($lastSection)) {
                    $lastSection['end'] = $i-1;
                    $this->saveSection($lastSection);
                    unset($lastSection);

                    $lastSection["key"] = array_search($toLowName, $cats);
                    $lastSection["start"] = $i+2;
                    $lastSection['end'] = (int) $this->specificationInfo['diffLineNum'];
                } else {
                    if($toLowName == $cats["DOC"] || $this->me) { //Первый раздел должен быть Документация либо это МЭ
                        /*      Раздел Документация     */
                        $lastSection["key"] = "DOC";
                        $lastSection["start"] = $i+2;
                        $lastSection['end'] = (int) $this->specificationInfo['diffLineNum'];
                        if($this->me) $i = 0;
                    } else {
                        echo "Раздел Документация не задан <br />";
                        echo "Первый найденный раздел - " . $toLowName . "<br />";
                        echo "Файл - " . $this->fileName;
                        die();
                    }
                }
            }
            if($i == $this->specificationInfo['diffLineNum']) {
                $this->saveSection($lastSection);
            }
        }
    }
    private function saveSection($lastS) {
        $this->sections[$lastS["key"]] = array(
            "start" => $lastS['start'],
            "end" => $lastS["end"]
        );
    }
    /**
     * @return array
     */
    public function parseAll() {
        $result = null;
        if($this->specificationInfo['count'] == 1) {
            if (!$this->me)
                $this->parseDocs();
            $this->parseAssemblys();
            $this->parseDetails();
            $this->parseStandartUnits();
            $result = Array(Array(
                "assembly" => $this->assembly,
                "blankAssembly" => $this->blankAssemblys,
                "details" => $this->details,
                "blankDetails" => $this->blankdetails,
                "me" => $this->me
            ));
        } else {
            /* В спецификации более одной сборки
             * Надо отсканировать общую часть
             * Потом отсканировать все записи о разницах
             * и смерджить общее с разницей
             * Потом вернуть массив сборок
            */
            var_dump($this->sections);
            exit;
        }
        return $result;
    }
    private function parseDocs() {
        /*      Парсим раздел Документация      */
        $i = $this->sections['DOC']["start"];
        for (; $i <= $this->sections['DOC']["end"]; $i++) {
            if ($this->getAt(4, $i) != "") {
                // нашли документ Сборка ли это?
                //Нашли СБ
                if (preg_match("/сборочный чертеж/",
                        $this->helper->strtolower_utf8($this->getAt(4, $i))
                    ) || preg_match("/(СБ)$/",
                        $this->getAt(3, $i)
                    )
                ) { // Сборочный чертеж в рвзделе документация
                    $notation = $this->getAt(6, $i);
                    $this->assembly->init(array(
                        "drawingFormat" => $this->getDrawingFormat($i),
                        "designation" => trim(preg_replace("/(СБ)$/", null, $this->getAt(3, $i))),
                        "name" => preg_replace(
                            '/\s+/', " ",
                            preg_replace("/([.\s+]?Сборочный чертеж)/", null, $this->getAt(4, $i))
                        ),
                        "notation" => preg_match('/\*\)/', $notation) ? "" : $notation
                        )
                    );
                } else if ($this->getAt(4, $i) != "") { // Любая другая документация
                    $this->assembly->addDoc(new Document(
                            $this->getDrawingFormat($i),
                            $this->getAt(3, $i),
                            preg_replace('/\s+/', "", $this->getAt(4, $i)))
                    );
                }
            }

        }
    }
    private function parseAssemblys() {
        /*      Парсим раздел Сборочные единицы      */
        if(isset($this->sections['SBED'])) {
            $i = $this->sections['SBED']["start"];
            for (; $i <= $this->sections['SBED']["end"]; $i++) {
                if ($this->getAt(3, $i) != "") {
                    $this->blankAssemblys[] = Array(
                        "parentDesignation" => $this->assembly->getDesignation(),
                        "designation" => trim(preg_replace("/(СБ)$/", null, $this->getAt(3, $i))),
                        "specFormat" => $this->getAt(0, $i),
                        "name" => preg_replace(
                            '/\s+/', ' ',
                            preg_replace("/([.\s+] Сборочный чертеж)/", null, $this->getAt(4, $i))
                        ),
                        "count" => $this->getAt(5, $i),
                        "posNum" => $this->getAt(2, $i)
                    );
                }
            }
        }
    }
    private function parseDetails() {
        /*      Парсим раздел детали      */
        if(isset($this->sections['DETAILS'])) {
            $i = $this->sections['DETAILS']["start"];
            for (; $i <= $this->sections['DETAILS']["end"]; $i++) {
                $caption = null;
                if ($this->getAt(4, $i) != "") {
                    if ($this->isCaption($i)) {  // Нашел объединение
                        $caption = $this->getAt(4, $i); // Запоминаем Заголовок объединения
                    } else {  // не заголовок объединение
                        //Вдруг это тело объединения
                        $detail = new DetailUnit();
                        if ($caption !== null) { // Если заголовок объединения задан - то надо искать конец этой группы
                            if ($this->isCaptionBody($i)) { //Мы все ещ под заголовком объединения
                                $detail->init(array(
                                    "drawingFormat" => $this->getDrawingFormat($i),
                                    "designation" => $this->getDesignation($i),
                                    "name" => $caption . " " . $this->getAt(4, $i),
                                    "material" => null,
                                    "notation" => $this->getAt(6, $i)
                                    )
                                );
                                $this->details[] = $detail;
                                $this->blankdetails[] = Array(
                                    "parentDesignation" => $this->assembly->getDesignation(),
                                    "count" => $this->getAt(5, $i),
                                    "designation" => $this->getDesignation($i),
                                    "name" => $this->getAt(4, $i),
                                    "posNum" => $this->getAt(2, $i)
                                );
                            } else {
                                $caption = null;// упс объединение закончилось. Перечитаем cell зная это
                                $i--;
                            }
                        } else {
                            $detail->init(array(
                                "drawingFormat" => $this->getDrawingFormat($i),
                                "designation" => $this->getDesignation($i),
                                "name" => $this->getAt(4, $i),
                                "material" => null,
                                "notation" => $this->getAt(6, $i)
                                )
                            );
                            $this->details[] = $detail;
                            $this->blankdetails[] = Array(
                                "parentDesignation" => $this->assembly->getDesignation(),
                                "count" => $this->getAt(5, $i),
                                "designation" => $this->getDesignation($i),
                                "name" => $this->getAt(4, $i),
                                "posNum" => $this->getAt(2, $i)
                            );
                        }
                    }
                }

            }
        }
    }

    private function parseStandartUnits() {
        /*      Парсим раздел стандартные изделия      */
        if(isset($this->sections['STANDART'])) {
            $i = $this->sections['STANDART']['start'];
            for (; $i < $this->sections['STANDART']['end']; $i++) {
                if ($this->getAt(4, $i) != "") {
                    $standartUnit = new StandartUnit();
                    $standartUnit->init(array(
                        "name" => $this->getAt(4, $i),
                        "notation" => $this->getAt(6, $i)
                    ));
                    $this->assembly->addStandartUnit(array(
                        'unit'=> $standartUnit,
                        'count' => $this->getAt(5, $i),
                        'posNum' => $this->getAt(2, $i)
                    ));
                }
            }
        }
    }

    private function isCaptionBody ($j) {
        return ($this->helper->strtolower_utf8($this->getAt(0, $j)) == "бч" && /*деталь БЧ*/
            $this->getAt(3, $j) != ''&& /*Есть обозначение*/
            !preg_match("/ГОСТ/", $this->getAt(4, $j)) /*нет ГОСТа*/
        );
    }
    private function isCaption ($j) {
        // Выясняем не объединение ли на этой строке (Только детали)
        return (
            $this->getAt(0, $j) == ''&& /*Нет формата*/
            $this->helper->strtolower_utf8($this->getAt(0, $j+1)) == "бч" &&/*Следующая деталь БЧ*/
            $this->getAt(2, $j) == '' &&/*Нет позиции*/
            $this->getAt(3, $j) == ''&& /*Нет обозначения*/
            $this->getAt(5, $j) == ''  /*Нет количества*/
        );
    }
    private function getDesignation($j) {
        $design = $this->getAt(3, $j);
//        return $this->getAt(3, $j);
        if(preg_match("/\A\s*(-[0-9]?[0-9])\s*/", $design)) {
            // Это исполнение. Ищем обозначение
            $design = $this->activeDetail . trim($design);
        } else {
            $this->activeDetail = $design; // Записываем последнюю деталь. вдруг будет исполнение
        }
        return $design;
    }
    private function getDrawingFormat($i) {
        $zero = $this->getAt(0, $i);
        return preg_match('/\*\)/', $zero )?
        preg_replace('/\*\)/', null, $this->getAt(6, $i)) :
        $zero;
    }
    private function getAt($x, $y){
        return $this->excelObj->getCellByColumnAndRow($x,$y)->getValue();
    }

}