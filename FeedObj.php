<?php


class FeedObj {
    /**
     * @var array
     */
    private $data;

    public function getData(PropListObj $propListObj) {
        $result = [];
        foreach ($this->data as $item) {
            $cur_result = [];
            foreach ($propListObj->getData() as $k => $prop) {
                $cur_result [$k] = [
                    'code' => $k,
                    'value' => Helper::extractPath($item, $prop->getName()),
                    'prop' => $prop,
                ];
            }
            $result []= $cur_result;
        }
        return $result;
    }

    public function __construct($filename, $start)
    {
        $feed = simplexml_load_file($filename);

        $data = Helper::xmlToArray($feed); // массив из фида
        $data = Helper::extractPath($data, Helper::fromBXName($start));
        $this->data = $data;
    }
}
