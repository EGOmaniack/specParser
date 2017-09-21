<?php
// ini_set('display_errors', 0) ;
ini_set('xdebug.var_display_max_depth', 22);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

class Sorter {
    private $dataArray;


    public function __construct() {
        $this->dataArray = [];
    }


    /**
     * @param array $data
     * @param $rootDesign
     * @return array
     */
    public function sort(Array $data, $rootDesign) {
        $this->dataArray = $data;
        $loopIndex = -1;
        //Сортируем детали
        while (count($this->dataArray['blankDetails']) > 0) {
            $loopIndex ++;
            $detail = null;
            $blank = $this->dataArray['blankDetails'][$loopIndex];

            foreach ($this->dataArray['details'] as $det) {
                if($det->getDesignation() == $blank["designation"]) {
                    $detail = $det;
                }
            }

            foreach ($this->dataArray['assemblys'] as $assem) {

                if($assem->getDesignation() == $blank["parentDesignation"]) {
                    $assem->addDetailUnit( Array(
                            "count" => $blank['count'],
                            "detailUnit" => $detail
                        )
                    );
                    unset($this->dataArray['blankDetails'][$loopIndex]);
                }
            }


            if($loopIndex > 9999) {
                echo "too mach details work";
                break;
            }
        }
        unset($this->dataArray["details"]);
        unset($this->dataArray["blankDetails"]);

        $rootAssembly = null;

        // Вытаскиваем root сборку из массива
        foreach ($this->dataArray['assemblys'] as $key => $assemb) {
            if($assemb->getDesignation() == $rootDesign) {
                $rootAssembly = $assemb;
                unset($this->dataArray['assemblys'][$key]);
                break;
            }
        }

        if($rootAssembly === null) die("Не найдена главная спецификация");
        // Выделил root сборку. Можно начинать собирать проект

        $loopIndex = -1;
        while (count($this->dataArray["blankAssemblys"]) > 0) {
            $loopIndex ++;

            foreach ($this->dataArray["blankAssemblys"] as $blKey => $blAss) {
                $this->addToTree($blAss, $rootAssembly, $blKey);
            }

            if($loopIndex > 9999) {
                echo "too mach assemblys work";
                break;
            }
        }

        return $rootAssembly;
//        var_dump($rootAssembly);
//        var_dump($this->dataArray['blankAssemblys']);
    }
    private function addToTree($blankAss, $assembly, $blKey) {
        if($assembly->getDesignation() !== $blankAss["parentDesignation"]) { // Рекурсивно ищем куда воткнуть
//            echo $assembly->getDesignation() . " ==? " . $blankAss["parentDesignation"] . "<br />";
            if(count($assembly->getAssemblys()) > 0) {
                foreach ($assembly->getAssemblys() as $underAss) {
                    $this->addToTree($blankAss, $underAss["unit"], $blKey);
                }
            }
        } else {
            // Добавляем в эту сборку
            if(count($this->dataArray) > 0) {
                foreach ($this->dataArray["assemblys"] as $ass) {
                    if($ass->getDesignation() == $blankAss["designation"]) {
                        /*      добрасываем недостающую инфу        */
                        $ass->setSpecFormat($blankAss['specFormat']);
                        $ass->setName($blankAss['name']);
                        /*      Так и сохраняем     */
                        $assembly->addAssemb($ass, $blankAss["count"]);
                        unset($this->dataArray["blankAssemblys"][$blKey]);
                    }
                }
            }
        }
    }


    /**
     * @param array $data
     * @return array
     */
    public function rebuild(Array $data) {

        $result = [];

        //TODO: сохранить все ключи в массив и перебирать его
        //TODO: не допускать повторов при слиянии

        $assemblys = [];
        $blankAssemblys = [];
        $details = [];
        $blankDetails = [];

        foreach ($data as $specData) {
            $assemblys[] = $specData['assembly'];
            $blankAssemblys = array_merge(
                $blankAssemblys,
                $specData['blankAssembly']
            );
            $details = array_merge(
                $details,
                $specData['details']
            );
            $blankDetails = array_merge(
                $blankDetails,
                $specData['blankDetails']
            );
        }
        $result['assemblys'] = $assemblys;
//        foreach ($assemblys as $ass) {
//            $result['assemblys'][$ass->getDesignation()] = $ass;
//        }
        $result['blankAssemblys'] = $blankAssemblys;
        $result['details'] = $details;
        $result['blankDetails'] = $blankDetails;

        return $result;
    }
}