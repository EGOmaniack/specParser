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
    private $sections;  // граници всех разделов
    private $fileName;

    public function __construct(PHPExcel_Worksheet $excelObj, string $fileName) {
        $this->assembly = new AssemblyUnit();
        $this->excelObj = $excelObj;
        $this->height = $excelObj->getHighestRow();
        $this->blankAssemblys = [];
        $this->blankdetails = [];
        $this->details = [];
        $this->helper = new Helper();
        $this->sections = [];
        $this->fileName = $fileName;
        $this->initSections();
    }
    private function saveSection($lastS) {
        $this->sections[$lastS["key"]] = array(
            "start" => $lastS['start'],
            "end" => $lastS["end"]
        );
    }
    private function initSections () {
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
        for($i = 1; $i <= $this->height; $i++) {
            $toLowName = trim($this->helper->strtolower_utf8($this->getAt(4, $i)));
            if(in_array($toLowName, $cats)) { // Нашли какой-то раздел
                if(isset($lastSection)) {
                    $lastSection['end'] = $i-1;
                    $this->saveSection($lastSection);
                    unset($lastSection);

                    $lastSection["key"] = array_search($toLowName, $cats);
                    $lastSection["start"] = $i+2;
                    $lastSection['end'] = (int) $this->height;
                } else {
                    if($toLowName == $cats["DOC"]) { //Первый раздел должен быть Документация
                        /*      Раздел Документация     */
                        $lastSection["key"] = "DOC";
                        $lastSection["start"] = $i+2;
                        $lastSection['end'] = (int) $this->height;
                    } else {
                        echo "Раздел Документация не задан<br />";
                        echo "Первый найденный раздел - " . $toLowName . "<br />";
                        echo "Файл - " . $this->fileName;
                        die();
                    }
                }
            }
            if($i == $this->height) {
                $this->saveSection($lastSection);
            }
        }
    }
    /**
     * @return array
     */
    public function parseAll() {
        $this->parseDocs();
        $this->parseAssemblys();
        $this->parseDetails();
        return Array(
            "assembly" => $this->assembly,
            "blankAssembly" => $this->blankAssemblys,
            "details" => $this->details,
            "blankDetails" => $this->blankdetails
        );
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
                    $this->assembly->init(
                        $this->getDrawingFormat($i),
                        trim(preg_replace("/(СБ)$/", null, $this->getAt(3, $i))),
                        preg_replace(
                            '/\s+/', " ",
                            preg_replace("/([.\s+] Сборочный чертеж)/", null, $this->getAt(4, $i))
                        )
                    );
                } else if ($this->getAt(4, $i) != "") { // Любая другая документация
                    $this->assembly->addDoc(new Document(
                            $this->getDrawingFormat($i),
                            $this->getAt(3, $i),
                            preg_replace('/\s+/', " ", $this->getAt(4, $i)))
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
                if ($this->getAt(4, $i) != "") {
                    $this->blankAssemblys[] = Array(
                        "parentDesignation" => $this->assembly->getDesignation(),
                        "designation" => trim(preg_replace("/(СБ)$/", null, $this->getAt(3, $i))),
                        "specFormat" => $this->getAt(0, $i),
                        "name" => preg_replace(
                            '/\s+/', ' ',
                            preg_replace("/([.\s+] Сборочный чертеж)/", null, $this->getAt(4, $i))
                        ),
                        "count" => $this->getAt(5, $i)
                    );
                }
            }
        }
    }
    private function parseDetails() {
        if(isset($this->sections['DETAILS'])) {
            /*      Парсим раздел Сборочные единицы      */
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
                            if ($this->isCaptureBody($i)) { //Мы все ещ под заголовком объединения
                                $detail->init(
                                    $this->getDrawingFormat($i),
                                    $this->getDesignation($i),
                                    $caption . " " . $this->getAt(4, $i),
                                    null
                                );
                                $this->details[] = $detail;
                                $this->blankdetails[] = Array(
                                    "parentDesignation" => $this->assembly->getDesignation(),
                                    "count" => $this->getAt(5, $i),
                                    "designation" => $this->getDesignation($i),
                                    "name" => $this->getAt(4, $i)
                                );
                            } else {
                                $caption = null;// упс объединение закончилось. Перечитаем cell зная это
                                $i--;
                            }
                        } else {
                            $detail->init(
                                $this->getDrawingFormat($i),
                                $this->getDesignation($i),
                                $this->getAt(4, $i),
                                null
                            );
                            $this->details[] = $detail;
                            $this->blankdetails[] = Array(
                                "parentDesignation" => $this->assembly->getDesignation(),
                                "count" => $this->getAt(5, $i),
                                "designation" => $this->getDesignation($i),
                                "name" => $this->getAt(4, $i)
                            );
                        }
                    }
                }

            }
        }
    }

    private function isCaptureBody ($j) {
        if(
            $this->helper->strtolower_utf8($this->getAt(0, $j)) == "бч" && /*деталь БЧ*/
            $this->getAt(3, $j) != ''&& /*Есть обозначение*/
            !preg_match("/ГОСТ/", $this->getAt(4, $j))
        ) {
            return true;
        }
        return false;
    }
    private function isCaption ($j) {
        // Выясняем не объединение ли на этой строке (Только детали)
        if($this->getAt(0, $j) == ''&& /*Нет формата*/
        $this->helper->strtolower_utf8($this->getAt(0, $j+1)) == "бч" &&/*Следующая деталь БЧ*/
        $this->getAt(2, $j) == '' &&/*Нет позиции*/
        $this->getAt(3, $j) == ''&& /*Нет обозначения*/
        $this->getAt(5, $j) == '') /*Нет количества*/
        {
            return true;
        }
        return false;
    }
    private function getDesignation($j) {
        $design = $this->getAt(3, $j);
//        return $this->getAt(3, $j);
        if(preg_match("/\A\s*(-[0-9]?[0-9])\s*/", $design)) {
            // Это исполнение. Ищем обозначение
            return $this->activeDetail . trim($design);
        } else {
            $this->activeDetail = $design; // Записываем последнюю деталь. вдруг будет исполнение
            return $design;
        }
    }
    private function getDrawingFormat($j) {
        $zero = $this->getAt(0, $j);
        return preg_match('/\*\)/', $zero )?
        preg_replace('/\*\)/', null, $this->getAt(6, $j)) :
        $zero;
    }
    private function getAt($x, $y){
        return $this->excelObj->getCellByColumnAndRow($x,$y)->getValue();
    }

}