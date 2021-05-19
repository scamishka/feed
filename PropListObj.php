<?php


class PropListObj {
    /**
     * @var PropObj[]
     */
    private $data;

    public function getData() {
        return $this->data;
    }

    public function getFieldType($code) {
        return Helper::arrayGetDefault($this->data, $code, 'S');
    }

    public function __construct($propList)
    {
        $result = [];
        $propList[] = [
            'CODE' => 'DESCRIPTION',
        ];
        foreach ($propList as $item) {
            $code =$item['CODE'];
            $result [$code] = new PropObj(
                Helper::fromBXName($code), $item["PROPERTY_TYPE"]
            );
        }
        $this->data = $result;
    }

}
