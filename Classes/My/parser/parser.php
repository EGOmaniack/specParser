<?php

include 'constants.php';

class Parser {
    private $excelObj;
    private $height;
    private $assembly; //Сборочная единица описываемая в спецификации
    private $blankAssemblys; //Сборочные единицы упомянутые в спецификации и кол-во
    private $blankdetails; // детали упомянутые  в спецификации и кол-во
    private $details; // детали упомянутые  в спецификации
    private $docs;

    public function __construct($excelObj) {
        $this->assembly = new AssemblyUnit();
        $this->excelObj = $excelObj;
        $this->height = $excelObj->getHighestRow();
        $this->blankAssemblys = [];
        $this->blankdetails = [];
        $this->details = [];
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
    private function getAt($x, $y){
        return $this->excelObj->getCellByColumnAndRow($x,$y)->getValue();
    }
    private function startParse () { //Парсим стандартную спецификацию и заполняем $this->assembley

        for ($i=0; $i < $this->height; $i++) {

            if(trim(
                $this->strtolower_utf8(
                    $this->getAt(4, $i)
                )) == DOC) { // Нашли раздел документация
                for ($j=$i+2; $j <= $this->height; $j++) {
                    if(count($this->getAt(4, $j)) > 0) {
                        // нашли документ Сборка ли это?
                        //Нашли СБ
                        if(preg_match("/" . SBORO4NIY4ERTEJ . "/",
                            $this->strtolower_utf8($this->getAt(4, $j))
                        ) || preg_match("/(\sСБ)$/",
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
            } else if (trim(
                $this->strtolower_utf8(
                    $this->getAt(4, $i)
                )) == SBED) { // Нашли Сборочные единицы
                for($j = $i+2; $j <= $this->height; $j ++) {
                    if(count($this->getAt(4, $j)) > 0) {
                        $this->blankAssemblys[] = Array(
                            "parentDesignation" =>$this->assembly->getDesignation(),
                            "designation" => trim(preg_replace("/(СБ)$/", null, $this->getAt(3, $j))),
                            "name" =>preg_replace(
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

            } else if (trim(
                    $this->strtolower_utf8(
                        $this->getAt(4, $i)
                    )) == DETALI) { // Нашли Детали

                for($j = $i+2; $j <= $this->height; $j ++) {
                    if(count($this->getAt(4, $j)) > 0) {
                        $detail = new DetailUnit();
                        $detail->init(
                            $this->getDrawingFormat($j),
                            $this->getAt(3, $j),
                            $this->getAt(4, $j),
                            null
                        );
                        $this->details[] = $detail;
                        $this->blankdetails[] = Array(
                                "parentDesignation" =>$this->assembly->getDesignation(),
                                "count" => $this->getAt(5, $j),
                                "designation" => $this->getAt(3, $j),
                                "name" => $this->getAt(4, $j)
                            );
                    } else {    //нашли пустую строку в разделе (Конец раздела детали)
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
    private function getDrawingFormat($j) {
        return preg_match('/\*\)/', $this->getAt(0, $j)) ?
        preg_replace('/\*\)/', null, $this->getAt(6, $j)) :
        $this->getAt(0, $j);
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