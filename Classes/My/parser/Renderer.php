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

    public function __construct(string $mode = "default") {
        $this->mode = $mode;
        $this->settings = array(
            "default" => array(
                "columNames" => array(
                    "1 формат", "2 Зона", "3 поз", "4 Обозначение", "5 Наименование", "6 кол-во",
                    "7 кол-во узлов", "8 кол-во общее", "9 Примечание", "10 Входит в"
                )
            )
        );
    }
    public function loadData($data) {
        $this->data = $data;
    }
    public function render() {
        $result = "<table>";
        if($this->mode == "default") {
//            var_dump($this->data);
//            exit;
            $result .= $this->generateTableHead();
            $result .= $this->getAssemblyInfo($this->data);
            $result .= "</table>";
            return $result;
        }
    }
    private function getAssemblyInfo(AssemblyUnit $assemb, $count = 1, $perentCount = 1, $summcount = 1, $parentDesignation = "") {
        $result = "<tr>";
        $result .= "<td>". $assemb->getDrawingFormat() . "</td><td></td><td></td>";
        $result .= "<td>" . $assemb->getDesignation() . "</td>";
        $result .= "<td>" . $assemb->getName() . "</td>";
        $result .= "<td>" . $count . "</td>";
        $result .= "<td>" . $perentCount . "</td>";
        $result .= "<td>" . $summcount . "</td>";
        $result .= "<td>" . /*Сюда варнинги*/ "</td>";
        $result .= "<td>" . $parentDesignation . "</td>";
        $result .= "</tr>";
        if(count($assemb->getAssemblys()) > 0) {
            foreach ($assemb->getAssemblys() as $assembly) {
                $result .= $this->getAssemblyInfo(
                    $assembly["unit"],
                    $assembly["count"],
                    $summcount,
                    ($assembly["count"] * $summcount),
                    $assemb->getDesignation()
                );
            }
        }
        // В детали

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
/*
   <table border="1">
   <caption>Таблица размеров обуви</caption>
   <tr>
    <th>Россия</th>
    <th>Великобритания</th>
    <th>Европа</th>
    <th>Длина ступни, см</th>
   </tr>
   <tr><td>34,5</td><td>3,5</td><td>36</td><td>23</td></tr>
   <tr><td>35,5</td><td>4</td><td>36⅔</td><td>23–23,5</td></tr>
   <tr><td>36</td><td>4,5</td><td>37⅓</td><td>23,5</td></tr>
   <tr><td>36,5</td><td>5</td><td>38</td><td>24</td></tr>
   <tr><td>37</td><td>5,5</td><td>38⅔</td><td>24,5</td></tr>
   <tr><td>38</td><td>6</td><td>39⅓</td><td>25</td></tr>
   <tr><td>38,5</td><td>6,5</td><td>40</td><td>25,5</td></tr>
   <tr><td>39</td><td>7</td><td>40⅔</td><td>25,5–26</td></tr>
   <tr><td>40</td><td>7,5</td><td>41⅓</td><td>26</td></tr>
   <tr><td>40,5</td><td>8</td><td>42</td><td>26,5</td></tr>
   <tr><td>41</td><td>8,5</td><td>42⅔</td><td>27</td></tr>
   <tr><td>42</td><td>9</td><td>43⅓</td><td>27,5</td></tr>
   <tr><td>43</td><td>9,5</td><td>44</td><td>28</td></tr>
   <tr><td>43,5</td><td>10</td><td>44⅔</td><td>28–28,5</td></tr>
   <tr><td>44</td><td>10,5</td><td>45⅓</td><td>28,5–29</td></tr>
   <tr><td>44,5</td><td>11</td><td>46</td><td>29</td></tr>
   <tr><td>45</td><td>11,5</td><td>46⅔</td><td>29,5</td></tr>
   <tr><td>46</td><td>12</td><td>47⅓</td><td>30</td></tr>
   <tr><td>46,5</td><td>12,5</td><td>48</td><td>30,5</td></tr>
   <tr><td>47</td><td>13</td><td>48⅔</td><td>31</td></tr>
   <tr><td>48</td><td>13,5</td><td>49⅓</td><td>31,5</td></tr>
  </table>
*/