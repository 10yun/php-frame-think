<?php

/**
 * 	@action 将xml转为array
 */
function cc_xml_to_arr($xml = '')
{
    // 先把xml转换为simplexml对象，再把simplexml对象转换成 json，再将 json 转换成数组。

    try {
        // $simple_xml = simplexml_load_string($string);
        $simple_xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $array = json_decode(json_encode($simple_xml), true);
        return $array;
    } catch (Exception $exception) {
        return [];
    }
}
/**
 * @action array转xml
 * 遍历数组方法
 * @param array $arr 数组
 */
function cc_arr_to_xml(array $arr = [], bool $isCdata = true)
{
    // if (is_null($arr)) {
    //     $arr = $this->parameters;
    // }
    // if (!is_array($arr) || empty($arr)) {
    //     die("参数不为数组无法解析");
    // }
    $xml = "<xml>";
    foreach ($arr as $key => $val) {
        if (is_numeric($val)) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        } else {
            if ($isCdata) {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            } else {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            }
        }
    }
    $xml .= "</xml>";
    return $xml;
}
