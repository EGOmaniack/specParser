<?php

include 'constants.php';

class Parser {
    private $excelObj;
    private $height;
    private $assembley;
    private $docs;

    public function __construct($excelObj) {
        $this->assembley = new AssembleyUnit();
        $this->excelObj = $excelObj;
        $this->height = $excelObj->getHighestRow();
    }

    /**
     * @return AssembleyUnit
     */
    public function parceAll() {
        $this->startParse();
        return $this->assembley;
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
                for ($j=$i+2; $j < $this->height; $j++) {
                    if(count($this->getAt(4, $j)) > 0) {
                        // нашли документ Сборка ли это?
                        //Нашли СБ
                        if(preg_match("/" . SBORO4NIY4ERTEJ . "/",
                            $this->strtolower_utf8($this->getAt(4, $j))
                        ) || preg_match("/(\sСБ)$/",
                            $this->getAt(3, $j)
                        )
                        ) { // Сборочный чертеж в рвзделе документация
                            $this->assembley->init(
                                $this->getDrawingFormat($j),
                                trim(preg_replace("/(СБ)$/", null, $this->getAt(3, $j))),
                                preg_replace("/([.\s+] Сборочный чертеж)/", null, $this->getAt(4, $j))
                            );
                        } else if($this->getAt(4, $j) != ""){ // Любая другая документация
                            $this->assembley->addDoc(new Document(
                                $this->getDrawingFormat($j),
                                $this->getAt(3, $j),
                                $this->getAt(4, $j) )
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

                for($j = $i+2; $j < $this->height; $j ++) {
                    if(count($this->getAt(4, $j)) > 0) {
                        $podsborka = new AssembleyUnit();
                        $podsborka->init(
                            null,
                            trim(preg_replace("/(СБ)$/", null, $this->getAt(3, $j+2))),
                            preg_replace("/([.\s+] Сборочный чертеж)/", null, $this->getAt(4, $j+2))
                        );
                        $this->assembley->addAssemb(
                            $podsborka,
                            $this->getAt(5, $j),
                            $this->getDrawingFormat($j)
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

                for($j = $i+2; $j < $this->height; $j ++) {
                    if(count($this->getAt(4, $j)) > 0) {
                        $podsborka = new DetailUnit();
                        $podsborka->init(
                            null,
                            $this->getAt(3, $j),
                            $this->getAt(4, $j),
                            null
                        );
                        $this->assembley->addDetailUnit( Array(
                                "count" => $this->getAt(5, $j),
                                "detailUnit" => $podsborka
                            ));
                    } else {    //нашли пустую строку в разделе (Конец раздела Сборочные единицы)
                        echo "Пустая строчка найдена";
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