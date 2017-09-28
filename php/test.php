<?php

//$data = array(
//    array(1,2,3),
//    array(2,4,
//        234
//    ),
//    8
//);
//
//$result = 0;
//
//class Adder {
//    private $result;
//
//    public function __constructor() {
//        $this->result = 0;
//    }
//
//    public function addRequrcive ($data) {
//        if(is_array($data)) {
//            foreach ($data as $dat) {
//                $this->addRequrcive($dat);
//            }
//        } else {
//            $this->result += $data;
//        }
//        return $this->result;
//    }
//}
//
//var_dump($data);
//$adder = new Adder();
//
//echo $adder->addRequrcive($data);

$testStr = "Пластина
Лист Б-ПН-10 ГОСТ 19903-74Ст3сп2 ГОСТ 14637-89
105-2х210-10";

echo preg_match("/ГОСТ/", $testStr);
phpinfo();
