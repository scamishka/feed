<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("feed-Test");

require_once 'helper.php';
require_once 'FeedObj.php';
require_once 'PropListObj.php';

CModule::IncludeModule('iblock');

$IBLOCK_ID = 2;

class PropObj {
    private $name;
    private $type;

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }
    public function getName() {
        return $this->name;
    }
}

class BXElement {
    /**
     * @var array
     */
    private $data;
    private $iblockId;


    public function __construct($iblockId, $obj) {
        $this->iblockId = $iblockId;
        $this->data = $obj;
    }

    public function creatOrUpdateElement($iblockId) {
        $name = self::getElementName($this->data);
        echo '<pre>';
        print_r($name);
        echo '</pre>';

//        foreach ($this->data as $c_prop => $item) {
//            /** @var PropObj $p */
//            $p = $item['prop'];
//            return $p->getName();
//        }

//        $el = new CIBlockElement;

        $temp = self::getLoadProductArray($iblockId, $this->data);
//        echo '<pre>';
//        print_r($temp);
//        echo '</pre>';
        return '';
    }

    public function getElementName($obj) {
        $strLoc = $obj['AREA']['value']['value'] . ' м2, '
            . $obj['LOCATION__ADDRESS']['value'] . ', '
            . $obj['LOCATION__LOCALITY_NAME']['value'];
        switch ($obj['CATEGORY']['value']) {
            case "flat":
                if($obj['ROOMS']['value']['value']){
                    $strRooms = $obj['ROOMS']['value']['value'] . '-комн.' .' квартира,';
                    return $strRooms . $strLoc;
                } else if(!empty($obj['STUDIO']['value']['value']) && $obj['ROOMS']['value']['value'] > 0) {
                    $strStudio = 'квартира-студия ';
                    return $strStudio . $strLoc;
                } else {
                    return $strLoc;
                }
                break;
            default:
                return $obj['_INTERNAL_ID'];
        }
    }

    public function addPropTypeList($iblockId, $propCode, $propValue) {
        $ENUM_ID = 0;
        $property_enums = CIBlockPropertyEnum::GetList(Array(), Array("IBLOCK_ID"=>$iblockId, "CODE"=>$propCode, "VALUE" => $propValue));
        while($enum_fields = $property_enums->GetNext())
        {
            $ENUM_ID =  $enum_fields["ID"];
//        echo 'enum - ' . $ENUM_ID;
        }
        return $ENUM_ID;
    }

    public function getPropValue($iblockId, $prop) {
//        echo '<pre>';
//        print_r($prop);
//        echo '</pre>';
        $prop_value = $prop['value'];
        $prop_code = $prop['code'];
        /** @var PropObj $cur_prop */
        $cur_prop = $prop['prop'];
        $prop_name = $cur_prop->getName();
        $prop_type = $cur_prop->getType();

//        echo ' $prop_value - ' . $prop_value;
//        echo PHP_EOL;

        if($prop_value) {
            switch ($prop_type) {
                case "N": // свойство NUMBER
                    $PROP[$prop_code] = $prop_value['value'];
                    return $PROP[$prop_code];
                    break;
                case "L": // LIST
                    $ENUM_ID = self::addPropTypeList($iblockId, $prop_code, $prop_value['value']);
                    $PROP[$prop_code] = array("VALUE" => $ENUM_ID);
                    return $PROP[$prop_code];
                    break;
                case "S": // STRING
                    $PROP[$prop_code] = $prop_value['value'];
                    return $PROP[$prop_code];
                    break;
                default:
                    return null;
                    break;
//                    echo 'ошибка нет собвадений типа для свойства' .'<br/>';
            }
        } else {
            switch ($prop_type) {
                case "F": // свойство файл
                    $PROP[$prop_code] = '';
//                    $PROP[$prop_name] = self::addPropTypeFile($prop_value);
                    return $PROP[$prop_code];
                    break;
                default:
                    return null;
                    break;
//                    echo 'ошибка нет собвадений типа для свойства' . '<br/>';
            }
        }
    }

    public function addPropTypeFile (array $images) {
        // ****** MORE_PHOTO*******
        $rsMorePhoto = [];
        $i = 0;
        foreach ($images as $image) {
            $rsMorePhoto['n'.$i] = array("VALUE"=>CFile::MakeFileArray($image));
            $i++;
        }
        // *************************
        return $rsMorePhoto;
    }

    public function getLoadProductArray($iblock, $obj) {
        $PROP = [];
        foreach ($obj as $propItem) {
            $code = $propItem['code'];
//            echo '$propItem';
//            echo '<pre>';
//            print_r($propItem);
//            echo '</pre>';
            $PROP[$code] = self::getPropValue($iblock, $propItem);
        }
        echo '$PROP';
        echo '<pre>';
        print_r($PROP);
        echo '</pre>';

        $arLoadProductArray = Array(
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $iblock,
            "NAME" => self::getElementName($obj),
            "CODE" => $obj['_INTERNAL_ID']['value'],
            "ACTIVE" => "Y",
            "PROPERTY_VALUES" => $PROP,
            "DETAIL_TEXT" => $obj['DESCRIPTION']['value'],
        );

//        echo '<pre>';
//        print_r($arLoadProductArray);
//        echo '</pre>';
    }
}

$propListOne = Helper::propsToList(2);

$propListObj = new PropListObj($propListOne);

$start = 'REALTY_FEED__OFFER';

$obj = new FeedObj("feed.xml", $start);

$offer_data = $obj->getData($propListObj);
echo '<pre>';
echo 'количествео объектов - ';
print_r(count($offer_data));
echo '</pre>';
//
foreach ($offer_data as $element) {
    $el = new BXElement($IBLOCK_ID, $element);
    $temp = $el->creatOrUpdateElement($IBLOCK_ID);
//    echo '<pre>';
//    print_r($element);
//    echo '</pre>';

}


exit();
?>


