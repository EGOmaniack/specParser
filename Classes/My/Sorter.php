<?php
 ini_set('display_errors', 0) ;
ini_set('xdebug.var_display_max_depth', 6);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

class Sorter {
    private $dataArray;

    public function __construct() {
        $this->dataArray = [];
    }

    /**
     * @param array $data
     */
    public function sort(Array $data, $rootDesign) {

        $loopIndex = 0;
        //Сортируем детали
        while (count($data['blankDetails']) > 0) {
            $loopIndex ++;
            $detail = null;
            $blank = $data['blankDetails'][0];

            foreach ($data['details'] as $det) {
                if($det->getDesignation() == $blank["designation"]) {
                    $detail = $det;
                }
            }

            foreach ($data['assembleys'] as $assem) {

                if($assem->getDesignation() == $blank["parentDesignation"]) {
                    $assem->addDetailUnit( Array(
                            "count" => $blank['count'],
                            "detailUnit" => $detail
                        )
                    );
                    unset($data['blankDetails'][0]);
                }
            }


            if($loopIndex > 9999) {
                echo "too mach";
                break;
            }
        }



        var_dump($data);
        $rootAssembley = null;
        $rootAssKey = 0;

        // Вытаскиваем root сборку из массива
        foreach ($data['assembleys'] as $key => $assemb) {
            if($assemb->getDesignation() == $rootDesign) {
                $rootAssembley = $assemb;
                $rootAssKey = $key;
                break;
            }
        }

        if($rootAssembley === null) die("Не найдена главная спецификация");

        unset($data['assembleys'][$rootAssKey]);



    }

    /**
     * @param array $data
     * @return array
     */
    public function rebuild(Array $data) {

        $result = [];

        //TODO: сохранить все ключи в массив и перебирать его

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
        $result['assembleys'] = $assemblys;
        $result['blankAssemblys'] = $blankAssemblys;
        $result['details'] = $details;
        $result['blankDetails'] = $blankDetails;

        return $result;
    }
}