<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 019 19.09.17
 * Time: 14:44
 */

class Renderer {
    private $mode;
    private $data;
    private $settings;
    private $counter;

    public function __construct(string $mode = "minibus") {
        $this->mode = $mode;
        $this->settings = array(
            "minibus" => array(
                "columNames" => array(
                    "0 № строки", "1 формат", "2 Зона", "3 поз", "4 Обозначение", "5 Наименование", "6 кол-во",
                    "7 кол-во узлов", "8 кол-во общее", "9 Примечание", "10 Входит в", "11 Материал", "12 Размеры",
                    "13 Размер1", "14 Размер2", "15 м/кв.м", "16 Размеры заготовок", "17 Отрезка", "18 Рубка ГЗ",
                    "19 Точение", "20 Фрезер.", "21 Слесар.", "22 Сварка", "23 Сборка", "24 Покраска", "25 Испытания",
                    "размер проката", "материал проката", "периметр", "26 готовность"
                )
            )
        );
        $this->counter = 0;
    }
    public function loadData($data) {
        $this->data = $data;
    }
    public function render() {
        $result = "<table>";
        if($this->mode == "minibus") {
            $result .= $this->generateTableHead();
//            var_dump($this->data); exit;
            $result .= $this->getAssemblyInfo($this->data);
            $result .= "</table>";
            return $result;
        }
    }
    private function getWarnings($specObj) {
        $result = "";
        $specObj->checkErrors();
        $warns = $specObj->getWarnings(10);
        if($warns != null) {
            foreach ($warns as $warning) {
                $result .= "\n<span style=\"color: orangered; weight: bold;\">" . $warning->message . "</span>\n";
            }
        }

        return $result;
    }
    private function getAssemblyInfo(
        AssemblyUnit $assemb,
        $posNum = '',
        $count = 1,
        $perentCount = 1,
        $summcount = 1,
        $parentDesignation = "") {

        $result = "<tr class=\"sb\">";
        $result .= "<td>". ++$this->counter . "</td>";
        $result .= "<td>". $assemb->getDrawingFormat() . "</td><td></td>";
        $result .= "<td>" . $posNum . "</td>";
        $result .= "<td>" . $assemb->getDesignation() . " СБ" . "</td>";
        $result .= "<td>" . $assemb->getName() . "</td>";
        $result .= "<td>" . $count . "</td>";
        $result .= "<td>" . $perentCount . "</td>";
        $result .= "<td>" . $summcount . "</td>";
        $result .= "<td>" . /*$this->getWarnings($assemb)*/ $assemb->getNotation() . "</td>";
        $result .= "<td>" . $parentDesignation . "</td>";
        $result .= "<td>" .  "</td>";
        $result .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
        $result .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
        $result .= "</tr>";
        if(count($assemb->getAssemblys()) > 0) {
            foreach ($assemb->getAssemblys() as $assembly) {
                $result .= $this->getAssemblyInfo(
                    $assembly["unit"],
                    $assembly['posNum'],
                    $assembly["count"],
                    $summcount,
                    ($assembly["count"] * $summcount),
                    $assemb->getDesignation()
                );
            }
        }
        // В детали
        $dets = $assemb->getDetailUnits();
        if(count($dets) > 0) {
            foreach ($dets as $detail) {
                $result .= "<tr>";
                $result .= "<td>". ++$this->counter . "</td>";
                $result .= "<td>". $detail['unit']->getDrawingFormat() . "</td><td></td>";
                $result .= "<td>" . $detail['posNum'] . "</td>";
                $result .= "<td>" . $detail['unit']->getDesignation() . "</td>";
                $result .= "<td>" . $detail['unit']->getName() . "</td>";
                $result .= "<td>" . $detail['count'] . "</td>";
                $result .= "<td>" . $summcount . "</td>";
                $result .= "<td>" . ($detail['count'] * $summcount) . "</td>";
                $result .= "<td>" . $detail['unit']->getNotation() . $this->getWarnings($detail['unit']) . "</td>";
                $result .= "<td>" . $assemb->getDesignation() . "</td>";
                $result .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
                $result .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
                $result .= "</tr>";
            }
        }
        // В стандартные изделия
        $standartUnits = $assemb->getStandartUnits();
        if(count($standartUnits) > 0) {
            foreach ($standartUnits as $stU) {
                $result .= "<tr class=\"st\">";
                $result .= "<td>". ++$this->counter . "</td>";
                $result .= "<td></td><td></td>";
                $result .= "<td>" . $stU['posNum'] . "</td>";
                $result .= "<td></td>";
                $result .= "<td>" . $stU['unit']->getName() . "</td>";
                $result .= "<td>" . $stU['count'] . "</td>";
                $result .= "<td>" . $summcount . "</td>";
                $result .= "<td>" . ($stU['count'] * $summcount) . "</td>";
                $result .= "<td>" . $stU['unit']->getNotation() . $this->getWarnings($stU['unit']) . "</td>";
                $result .= "<td>" . $assemb->getDesignation() . "</td>";
                $result .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
                $result .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
                $result .= "</tr>";
            }
        }
        return $result;
    }
    private function visit() {
    }
    private  function  listen() {
    }

    /**
     * @return string
     */
    public function generateTableHead(){
        $result = "<tr>";
        foreach ($this->settings[$this->mode]["columNames"] as $headElem) {
            $result .= "<th>" . $headElem . "</th>";
        }
        $result .= "</tr>";

        return $result;
    }

    /**
     * @param string $mode
     */
    public function setMode(string $mode)
    {
        $this->mode = $mode;
    }

    // сортировать через uasort() http://php.net/manual/ru/function.uasort.php
}