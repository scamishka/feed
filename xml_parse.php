<?php
$_SERVER["DOCUMENT_ROOT"] = "/var/www/sites.everest24.ru/data/www/an.everest24.ru";
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

require_once 'helper.php';
require_once 'FeedObj.php';
require_once 'PropListObj.php';
require_once 'do_echo_class.php';

CModule::IncludeModule('iblock');

$IBLOCK_ID = 2;
$IBLOCK_ID_MANAGERS = 10;
$IBLOCK_ID_LOCATION = 11;
$__time = time();
global $__time;


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

    public function creatOrUpdateLocation($iblockIdLocation) {
        $loc = new CIBlockElement;
        $arLoadLocationArray = self::getLoadLocationArray($iblockIdLocation, $this->data);
//        var_dump($arLoadManagerArray);
        $LOCATION_ID = $this->getElementId($iblockIdLocation, $arLoadLocationArray['CODE']);
        if($LOCATION_ID) {
            unset($arLoadLocationArray["IBLOCK_ID"]);
            CIBlockElement::SetPropertyValuesEx($LOCATION_ID, $iblockIdLocation,
                array('7' => Array ("VALUE" => array("del" => "Y"))));

            $res_loc = $loc->Update($LOCATION_ID, $arLoadLocationArray);
            return "UPdate Location ID: " . $arLoadLocationArray["CODE"] .PHP_EOL;
        } else {
            if ($res_loc = $loc->Add($arLoadLocationArray)) {
                return "New Location ID: " . $res_loc .PHP_EOL;
            }else {
//                var_dump($arLoadManagerArray);
//                exit();
                return "Location Error: " . $loc->LAST_ERROR .PHP_EOL;
            }
        }
    }

    public function creatOrUpdateManager($iblockIdManager) {
        $man = new CIBlockElement;
        $arLoadManagerArray = self::getLoadManagerArray($iblockIdManager, $this->data);
//        var_dump($arLoadManagerArray);
        $MANAGER_ID = $this->getElementId($iblockIdManager, $arLoadManagerArray['CODE']);
        if($MANAGER_ID) {
            unset($arLoadManagerArray["IBLOCK_ID"]);
            CIBlockElement::SetPropertyValuesEx($MANAGER_ID, $iblockIdManager,
                array('7' => Array ("VALUE" => array("del" => "Y"))));

            $res = $man->Update($MANAGER_ID, $arLoadManagerArray);
            return "UPdate Manager ID: " . $arLoadManagerArray["CODE"] .PHP_EOL;
        } else {
            if ($res = $man->Add($arLoadManagerArray)) {
                return "New Manager ID: " . $res .PHP_EOL;
            }else {
//                var_dump($arLoadManagerArray);
//                exit();
                return "Manager Error: " . $man->LAST_ERROR .PHP_EOL;
            }
        }
    }

    public function creatOrUpdateElement($iblockId, $iblockManagerId) {
        $name = self::getElementName($this->data);

        $el = new CIBlockElement;

        $arLoadProductArray = self::getLoadProductArray($iblockId, $iblockManagerId, $this->data);

        $PRODUCT_ID = $this->getElementId($iblockId, $arLoadProductArray['CODE']);
//        echo '$arLoadProductArray';
//        echo '<pre>';
//        print_r($arLoadProductArray);
//        echo '</pre>';

        if($PRODUCT_ID) {
            unset($arLoadProductArray["IBLOCK_ID"]);
            CIBlockElement::SetPropertyValuesEx($PRODUCT_ID, $iblockId, array('7' => Array ("VALUE" => array("del" => "Y"))));

            $res = $el->Update($PRODUCT_ID, $arLoadProductArray);
            return "UPdate ID: " . $arLoadProductArray["CODE"] .' - ' . $name .PHP_EOL;
        } else {
            if ($res = $el->Add($arLoadProductArray)) {
                return "New ID: " . $res .' - ' . $name .PHP_EOL;
            }else {
                return "Error: " . $el->LAST_ERROR .' - ' . $name .PHP_EOL;
            }
        }
    }

    public function getElementId($iblockId, $productCode) {
        return CIBlockFindTools::GetElementID(false, $productCode, false, false, array("IBLOCK_ID" => $iblockId));
    }

    public function getElementName($obj) {

        $area = ($obj['AREA']['value']['value'])?', '.$obj['AREA']['value']['value'] . ' м2':''; // площадь
        $address = ($obj['LOCATION__ADDRESS']['value'])?', '.$obj['LOCATION__ADDRESS']['value']:''; // адрес
        $location = ($obj['LOCATION__LOCALITY_NAME']['value'])?', '.$obj['LOCATION__LOCALITY_NAME']['value']:''; // город

        $strLoc = $area.$address.$location;

//        $strLoc = $obj['AREA']['value']['value'] . ' м2, '
//            . $obj['LOCATION__ADDRESS']['value'] . ', '
//            . $obj['LOCATION__LOCALITY_NAME']['value'];

        switch ($obj['CATEGORY']['value']) {
            case "flat":

                if($obj['ROOMS']['value']['value']){
                    $strRooms = $obj['ROOMS']['value']['value'] . '-комн.' .' квартира';
                    return $strRooms . $strLoc;
                } else if(!empty($obj['STUDIO']['value']) && $obj['STUDIO']['value'] > 0) {
                    $strStudio = 'Квартира-студия';
                    return $strStudio . $strLoc;
                } else {
                    return $strLoc;
                }
                break;
            case "house":
                $cat = 'Дом';
                return $cat.$strLoc;
                break;
            case "lot";
                $cat = 'Участок';
                return $cat.$strLoc;
                break;
            case "commercial";
                $cat = 'Коммерческая';
                return $cat.$strLoc;
                break;
            case "townhouse";
                $cat = 'Таунхауз';
                return $cat.$strLoc;
                break;
            case "дуплекс";
                $cat = 'Дуплекс';
                return $cat.$strLoc;
                break;
            case "часть дома";
                $cat = 'Часть дома';
                return $cat.$strLoc;
                break;
            case "room";
                $cat = 'Комната';
                return $cat.$strLoc;
                break;
            default:
                return $strLoc;
        }
    }

    public function addPropTypeList($iblockId, $propCode, $propValue) {
        $ENUM_ID = 0;
        $property_enums = CIBlockPropertyEnum::GetList(
            Array(),
            Array("IBLOCK_ID"=>$iblockId, "CODE"=>$propCode,  "XML_ID" => $propValue)
        );
        while($enum_fields = $property_enums->GetNext())
        {
            $ENUM_ID =  $enum_fields["ID"];
//        echo 'enum - ' . $ENUM_ID;
        }
        return $ENUM_ID;
    }

    public function getPropValue($iblockId, $prop) {
        $prop_value = $prop['value'];
        $prop_code = $prop['code'];
        /** @var PropObj $cur_prop */
        $cur_prop = $prop['prop'];
        $prop_name = $cur_prop->getName();
        $prop_type = $cur_prop->getType();

        if($prop_value) {
            switch ($prop_type) {
                case "N": // свойство NUMBER
                    $PROP[$prop_code] = $prop_value;
                    return $PROP[$prop_code];
                    break;
                case "L": // LIST
                    $ENUM_ID = self::addPropTypeList($iblockId, $prop_code, $prop_value);
                    $PROP[$prop_code] = array("VALUE" => $ENUM_ID);
                    return $PROP[$prop_code];
                    break;
                case "S": // STRING
                    $PROP[$prop_code] = $prop_value;
                    return $PROP[$prop_code];
                    break;
                case "F": // свойство файл
                    if (!is_array($prop_value)) {
                        $prop_value = [$prop_value];
                    }
                    $PROP[$prop_code] = '';
                    $PROP[$prop_code] = self::addPropTypeFile($prop_value);
                    return $PROP[$prop_code];
                    break;
                default:
                    $PROP[$prop_code] = $prop_value;
                    return $PROP[$prop_code];
                    break;
            }
        } else
            return null;
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

    public function getLoadProductArray($elementIblock, $additionalIblock, $obj) {
        $PROP = [];
        foreach ($obj as $propItem) {
            $code = $propItem['code'];
            $PROP[$code] = self::getPropValue($elementIblock, $propItem);
        };

        if ($obj['SALES_AGENT__ID']['value']) {
            $object_manager_id = $this->getElementId($additionalIblock, $obj['SALES_AGENT__ID']['value']);
            $PROP['SALES_AGENT__ID'] = $object_manager_id;
        }

        $name = self::getElementName($obj);
        $code = $obj['_INTERNAL_ID']['value'];
        $detail_text = $obj['DESCRIPTION']['value'];

        return Array(
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $elementIblock,
            "NAME" => $name,
            "CODE" => $code,
            "ACTIVE" => "Y",
            "PROPERTY_VALUES" => $PROP,
            "DETAIL_TEXT" => $detail_text,
        );
    }

    public function getLoadManagerArray($iblock, $obj) {

        $PROP = [];
        foreach ($obj as $propItem) {
//            var_dump($propItem['code']);
            $code = $propItem['code'];
            $PROP[$code] = self::getPropValue($iblock, $propItem);
        };

        return Array(
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $iblock,
            "NAME" => $obj['SALES_AGENT__NAME']['value'],
            "CODE" => $obj['SALES_AGENT__ID']['value'],
            "ACTIVE" => "Y",
            "PROPERTY_VALUES" => $PROP,
        );
    }

    public function getLoadLocationArray($iblock, $obj) {

        $PROP = [];
        foreach ($obj as $propItem) {
//            var_dump($propItem['code']);
            $code = $propItem['code'];
            $PROP[$code] = self::getPropValue($iblock, $propItem);
        };

        $name = $obj['LOCATION__LOCALITY_NAME']['value'] . $obj['LOCATION__MICRO_LOCALITY_NAME']['value'];
        $code = md5($name);

        global $__time;

        $PROP['IDENT_ID'] = $code;
        $PROP['DATA_CHANGE'] = $__time;

        return Array(
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $iblock,
            "NAME" => $name,
            "CODE" => $code,
            "ACTIVE" => "Y",
            "PROPERTY_VALUES" => $PROP,
        );
    }

    public function deleteElement ($iblock) {

        $arSelect = Array("ID", "CODE", "PROPERTY_*");
        $arFilter = Array("IBLOCK_ID"=>$iblock);


        //get list while fetch data change < $__time
        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
        var_dump($res);
        exit();
        while($ob = $res->GetNextElement())
        {
            $arProps = $ob->GetProperties();
            var_dump($arProps);
            exit();
//                $del = CIBlockElement::Delete($ob);
//                if ($del) {
//                    echo 'delete elem - '. $del;
//                } else {
//                    echo 'delete elem error ';
//                }
        }


    }

}

$propListOne = Helper::propsToList($IBLOCK_ID);
$propListObj = new PropListObj($propListOne);

$propListMan = Helper::propsToList($IBLOCK_ID_MANAGERS);
$propListObjMan = new PropListObj($propListMan);

$propListLoc = Helper::propsToList($IBLOCK_ID_LOCATION);
$propListObjLoc = new PropListObj($propListLoc);

$start = 'REALTY_FEED__OFFER';

$obj = new FeedObj("feed_full_test.xml", $start); // указываем файл фида или ссылку

/* для менеджеров*/
$offer_data = $obj->getData($propListObjMan);
$count=0;
$count_DIFF = 20;
$countAll = 0;
foreach ($offer_data as $element) {
    $man = new BXElement($IBLOCK_ID_MANAGERS, $element);
    $temp = $man->creatOrUpdateManager($IBLOCK_ID_MANAGERS);
    if ($count == $count_DIFF) {
        $countAll += $count;
        do_echo("man count - $countAll".PHP_EOL);
        do_echo ("man result: $temp".PHP_EOL);
        $count = 0;
    }
    $count++;
    do_echo ('man result - '.$temp.PHP_EOL);
}

/* для объектов*/
$offer_data = $obj->getData($propListObj);
$count=0;
$count_DIFF = 20;
$countAll = 0;
foreach ($offer_data as $element) {
    $el = new BXElement($IBLOCK_ID, $element);
    $temp = $el->creatOrUpdateElement($IBLOCK_ID, $IBLOCK_ID_MANAGERS);
    if ($count == $count_DIFF) {
        $countAll += $count;
        do_echo("elem count - $countAll".PHP_EOL);
        do_echo ("elem result: $temp".PHP_EOL);
        $count = 0;
    }
    $count++;
    do_echo ('elem result - '.$temp.PHP_EOL);
}


///для районов
$offer_data = $obj->getData($propListObjLoc);
$count = 0;
$count_DIFF = 20;
$countAll = 0;
foreach ($offer_data as $element) {
    $loc = new BXElement($IBLOCK_ID_LOCATION, $element);
    $temp = $loc->creatOrUpdateLocation($IBLOCK_ID_LOCATION);
    if ($count == $count_DIFF) {
        $countAll += $count;
        do_echo("loc count - $countAll".PHP_EOL);
        do_echo ("loc result: $temp".PHP_EOL);
        $count = 0;
    }
    $count++;
    do_echo ('loc result - '.$temp.PHP_EOL);
}

//деактивация устаревших объектов без изменений 7 дней
$countElements = 0;
$old_date = date("d.m.Y 00:00:00", strtotime("-7 day"));
$arSelect = array("ID", "IBLOCK_ID", "NAME");
$arFilter = array(
    "IBLOCK_ID" => $IBLOCK_ID,
    "ACTIVE" => "Y",
    "DATE_MODIFY_TO" => $old_date);
$res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
while ($ob = $res->GetNextElement()) {

    $arFields = $ob->GetFields();
    $elementId = $arFields["ID"];
    $countElements++;
    $el = new CIBlockElement;
    $arLoadProductArray = Array("ACTIVE" => "N");
    $res2 = $el->Update($elementId, $arLoadProductArray);
    $PROPS["AUTO_DEACTIVATE"] = "Y";
    CIBlockElement::SetPropertyValuesEx($elementId, $IBLOCK_ID, $PROPS);
    do_echo("Deactivate - ".$elementId.PHP_EOL);
}
do_echo("countElements deactivate - ".$countElements.PHP_EOL);

//generate cache
$res_cache = [];
//getlist while fetch инфоблок локации

//$temp = BXElement::deleteElement($IBLOCK_ID_LOCATION);

$arSelect = Array(
    "ID", "CODE",
    "PROPERTY_LOCATION__LOCALITY_NAME",
    "PROPERTY_LOCATION__MICRO_LOCALITY_NAME",
);
$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID_LOCATION);

//get list while fetch data change < $__time
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
$count = 0;
while($ob = $res->GetNext())
{
    $city = $ob["PROPERTY_LOCATION__LOCALITY_NAME_VALUE"];
    $micro = $ob["PROPERTY_LOCATION__MICRO_LOCALITY_NAME_VALUE"];

    $cur_micros = Helper::arrayGetDefault($res_cache, $city, []);
    $cur_micros[$micro] = $micro;
    $res_cache[$city] = $cur_micros;
    $count++;
}
file_put_contents('cache.json', json_encode($res_cache));
do_echo("cache done {$count}");
