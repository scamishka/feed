<?php
/* Created by cetacs on 13.05.2021 */

class Helper
{
    // в замен устаревшей
    static function each($array, $need_reset = true)
    {
        if ($need_reset) {
            reset($array);
        }
        return [key($array), current($array)];
    }

    static function extractPath($data, $path)
    {
        $result = $data;
        foreach ($path as $root_name) {
            $result = Helper::arrayGetDefault($result, $root_name, '');
//            echo 'extract';
//            echo '<pre>';
//            print_r($result);
//            echo '</pre>';
        }

        return $result;
    }

    static function fromBXName($name)
    {
        $parts = explode('__', $name);
        $result = [];
        foreach ($parts as $part) {

            $str = mb_strtolower($part);
            $strFirst = mb_strcut($str, 0, 1);
            if ($strFirst == '_') {
                $str = substr_replace($str, '@', 0, 1);
            }

            $result[] = str_ireplace("_", "-", $str);
        }

        return $result;
    }

    static function arrayGetDefault($ar, $key, $def)
    {
        if (!is_array($ar)) {
            return $def;
        }
        return array_key_exists($key, $ar) ? $ar[$key] : $def;
    }

    static function xmlToArray($xml, $options = array())
    {
        $defaults = array(
            'namespaceSeparator' => ':',//you may want this to be something other than a colon
            'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
            'alwaysArray' => array(),   //array of xml tag names which should always become arrays
            'autoArray' => true,        //only create arrays for tags which appear more than once
            'textContent' => '$',       //key used for the text content of elements
            'autoText' => true,         //skip textContent key if node has no attributes or child nodes
            'keySearch' => false,       //optional search and replace on tag and attribute names
            'keyReplace' => false       //replace values for above search values (as passed to str_replace())
        );
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace

        //get attributes from all namespaces
        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                //replace characters in attribute name
                if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        //get child nodes from all namespaces
        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                //recurse into child nodes
                $childArray = self::xmlToArray($childXml, $options);
                list($childTagName, $childProperties) = Helper::each($childArray, true);

                //replace characters in tag name
                if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                //add namespace prefix, if any
                if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;

                if (!isset($tagsArray[$childTagName])) {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? array($childProperties) : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                    === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
                }
            }
        }

        //get text content of node
        $textContentArray = array();
        $plainText = trim((string)$xml);
        if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;

        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return array(
            $xml->getName() => $propertiesArray
        );
    }

    static function propsToList($iBlockId) {
        $prop_list = [];
        $arFilter = array(
            'IBLOCK_ID' => $iBlockId
        );
        $rsProperty = CIBlockProperty::GetList(
            array(),
            $arFilter
        );
        while($element = $rsProperty->Fetch())
        {
//            array_push($prop_list, $element['CODE']);
//            $prop_list[$element['ID']]['ID'] = $element['ID'];
//            $prop_list[$element['ID']]['NAME'] = $element['NAME'];
//            $prop_list[$element['ID']]['SORT'] = $element['SORT'];
            $prop_list[$element['CODE']]['CODE'] = $element['CODE'];
            $prop_list[$element['CODE']]['PROPERTY_TYPE'] = $element['PROPERTY_TYPE'];
//            $prop_list[$element['ID']]['MULTIPLE'] = $element['MULTIPLE'];
        }

        return $prop_list;
    }
}
