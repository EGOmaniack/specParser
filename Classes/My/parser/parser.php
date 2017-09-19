<?php

class Parser {
    private $excelObj;
    private $height;
    private $assembly; //Сборочная единица описываемая в спецификации
    private $blankAssemblys; //Сборочные единицы упомянутые в спецификации и кол-во
    private $blankdetails; // детали упомянутые  в спецификации и кол-во
    private $details; // детали упомянутые  в спецификации
    private $activeDetail;
    private $categories;
    private $docs;

    public function __construct($excelObj) {
        $this->assembly = new AssemblyUnit();
        $this->excelObj = $excelObj;
        $this->height = $excelObj->getHighestRow();
        $this->blankAssemblys = [];
        $this->blankdetails = [];
        $this->details = [];
        $this->categories = array(
            "DOC" => "документация",
            "SBED" => "сборочные единицы",
            "DETALI" => "детали",
            "STANDART" => "стандартные изделия"
        );
    }

    /**
     * @return array
     */
    public function parseAll() {
        $this->startParse();
        return Array(
            "assembly" => $this->assembly,
            "blankAssembly" => $this->blankAssemblys,
            "details" => $this->details,
            "blankDetails" => $this->blankdetails
        );
    }
    private function startParse () { //Парсим стандартную спецификацию и заполняем $this->assembly
        for ($i=0; $i < $this->height; $i++) {

            /*      Парсим раздел Документация      */

            if(trim(
                $this->strtolower_utf8(
                    $this->getAt(4, $i)
                )) == $this->categories["DOC"]) { // Нашли раздел документация
                for ($j = $i+2; $j <= $this->height; $j++) {
                    if($this->getAt(4, $j) != "") {
                        // нашли документ Сборка ли это?
                        //Нашли СБ
                        if(preg_match("/сборочный чертеж/",
                            $this->strtolower_utf8($this->getAt(4, $j))
                        ) || preg_match("/(СБ)$/",
                            $this->getAt(3, $j)
                        )
                        ) { // Сборочный чертеж в рвзделе документация
                            $this->assembly->init(
                                $this->getDrawingFormat($j),
                                trim(preg_replace("/(СБ)$/", null, $this->getAt(3, $j))),
                                preg_replace(
                                    '/\s+/', " ",
                                    preg_replace("/([.\s+] Сборочный чертеж)/", null, $this->getAt(4, $j))
                                )
                            );
                        } else if($this->getAt(4, $j) != ""){ // Любая другая документация
                            $this->assembly->addDoc(new Document(
                                $this->getDrawingFormat($j),
                                $this->getAt(3, $j),
                                preg_replace('/\s+/'," ", $this->getAt(4, $j) ))
                            );
                        }
                    } else { //нашли пустую строку в разделе (Конец раздела Документация)
                        $i = $j;
                        break;
                    }
                }

                /*      Парсим раздел Сборочные единицы      */

            } else if (trim(
                $this->strtolower_utf8(
                    $this->getAt(4, $i)
                )) == $this->categories["SBED"]) { // Нашли Сборочные единицы
                for($j = $i+2; $j <= $this->height; $j ++) {
                    if($this->getAt(4, $j) != "") {
                        $this->blankAssemblys[] = Array(
                            "parentDesignation" =>$this->assembly->getDesignation(),
                            "designation" => trim(preg_replace("/(СБ)$/", null, $this->getAt(3, $j))),
                            "specFormat" => $this->getAt(0, $j),
                            "name" => preg_replace(
                                '/\s+/', ' ',
                                preg_replace("/([.\s+] Сборочный чертеж)/", null, $this->getAt(4, $j))
                            ),
                            "count" => $this->getAt(5, $j)
                        );
                    } else {    //нашли пустую строку в разделе (Конец раздела Сборочные единицы)
                        $i = $j;
                        break;
                    }
                }

                /*      Парсим раздел Детали      */

            } else if (trim(
                    $this->strtolower_utf8(
                        $this->getAt(4, $i)
                    )) == $this->categories["DETALI"]) {  // Нашли Детали
                $caption = null;
                for($j = $i+2; $j <= $this->height; $j ++) {
                    if(!in_array($this->strtolower_utf8($this->getAt(4, $j)), $this->categories)) { // если это не новая категория
                        if($this->getAt(4, $j) != "") {
                            if($this->isCaption($j)){  // Нашел объединение
                                $caption = $this->getAt(4, $j); // Запоминаем Заголовок объединения
                            } else {  // не заголовок объединение
                                //Вдруг это тело объединения
                                $detail = new DetailUnit();
                                if($caption !== null) { // Если заголовок объединения задан - то надо искать конец этой группы
                                    if($this->isCaptureBody($j)) { //Мы все ещ под заголовком объединения
                                        $detail->init(
                                            $this->getDrawingFormat($j),
                                            $this->getDesignation($j),
                                            $caption . " " . $this->getAt(4, $j),
                                            null
                                        );
                                        $this->details[] = $detail;
                                        $this->blankdetails[] = Array(
                                            "parentDesignation" => $this->assembly->getDesignation(),
                                            "count" => $this->getAt(5, $j),
                                            "designation" => $this->getDesignation($j),
                                            "name" => $this->getAt(4, $j)
                                        );
                                    } else {
                                        $caption = null;
                                        $j--;
                                    }
                                } else {
                                    $detail->init(
                                        $this->getDrawingFormat($j),
                                        $this->getDesignation($j),
                                        $this->getAt(4, $j),
                                        null
                                    );
                                    $this->details[] = $detail;
                                    $this->blankdetails[] = Array(
                                        "parentDesignation" => $this->assembly->getDesignation(),
                                        "count" => $this->getAt(5, $j),
                                        "designation" => $this->getDesignation($j),
                                        "name" => $this->getAt(4, $j)
                                    );
                                }
                            }
                        }
                    } else {  //нашли пустую строку в разделе (Конец раздела детали)
                        $i = $j;
                        break;
                    }
                }

            } else {
                //Следующий ращдел
                // break;
            }
        }
    }
    private function isCaptureBody ($j) {
        if(
            $this->strtolower_utf8($this->getAt(0, $j)) == "бч" && /*деталь БЧ*/
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
        $this->strtolower_utf8($this->getAt(0, $j+1)) == "бч" &&/*Следующая деталь БЧ*/
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
    private function strtolower_utf8($string){
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
}