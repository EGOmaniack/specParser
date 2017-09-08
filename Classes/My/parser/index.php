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
    public function parceAll() {
        $this->parceDocs();
        return $this->assembley;
    }
    private function getAt($x, $y){
        return $this->excelObj->getCellByColumnAndRow($x,$y)->getValue();
    }
    private function parceDocs () { //Парсим раздел документация
        // echo 'I\'m here';
        for ($i=0; $i < $this->height; $i++) {

            if(trim(
                $this->strtolower_utf8(
                    $this->getAt(4, $i)
                )) == DOC) {
                for ($j=$i+2; $j < $this->height; $j++) {
                    if(count ($this->getAt(4, $j)) > 0) {
                        // нашли документ Сборка ли это?
                        $SB = SB; //Нашли СБ
                        if(preg_match("/$SB/",
                            $this->strtolower_utf8($this->getAt(4, $j))
                        ) || preg_match("/сб/",
                            $this->strtolower_utf8($this->getAt(3, $j))
                        )
                        ) {
                            $this->assembley->init(
                                $this->getDrawingFormat($j),
                                $this->getAt(3, $j),
                                preg_replace("/([.\s+] Сборочный чертеж)/", null, $this->getAt(4, $j))
                            );
                        } else if($this->getAt(4, $j) != ""){
                            $this->assembley->addDoc(new Document(
                                $this->getDrawingFormat($j),
                                $this->getAt(3, $j),
                                $this->getAt(4, $j) )
                            );
                        }
                    } else { //нашли пустую строку в разделе (Конец раздела)
                        $i = $j;
                        break;
                    }
                }
            } else { //Следующий ращдел
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