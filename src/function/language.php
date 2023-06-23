<?php

/**
 * 【ctocode】      常用函数 - language相关处理
 * ============================================================================
 * @author       作者         ctocode-zhw
 * @version 	  版本	  v5.7.1.20210514
 * @copyright    版权所有   2015-2027，并保留所有权利。
 * @link         网站地址   https://www.10yun.com
 * @contact      联系方式   QQ:343196936
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */
function ctoLanguageToStr($str = '', $type = 'py_xx')
{
    if (empty($str)) {
        return '';
    }
    // $upper_lower = ! empty ( $request_data['upper_lower'] ) ? $request_data['upper_lower'] : 'lower';
    $lang = '';
    switch ($type) {
            // 拼音_首字母_大写
        case 'py_dx_szm':
            // ===========获取商品编号,格式为 ： 分类拼音首字符 + 商户id + 时间 + 6微随机数
            $strarr = array();
            // 分割中文
            preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $strarr);
            // preg_match_all("/./u", $str, $arr);
            foreach ($strarr[0] as $val) {
                // 转拼音,转大写
                $pinyin = '';
                $pinyin = ctoLanguagePinyin($val);
                $pinyin = strtoupper($pinyin);
                // 取首字符,拼接编号
                $lang .= substr($pinyin, 0, 1);
            }
            break;
            // 拼音小写
        case 'py_xx':
        default:
            $lang = ctoLanguagePinyin($str);
            break;
    }
    return $lang;
}
/**
 * @action 汉字转拼音
 * @param string $str 待转换的字符串
 * @param string $charset 字符串编码
 * @param bool $ishead 是否只提取首字母
 * @return string 返回结果
 * @version 2016-09-29
 * @author ctocode-zhw 
 */
function ctoLanguagePinyin($str, $charset = "utf-8", $ishead = 0)
{
    $restr = '';
    $str = trim($str);
    if ($charset == "utf-8") {
        $str = iconv("utf-8", "gb2312", $str);
    }
    $slen = strlen($str);
    $pinyins = array();
    if ($slen < 2) {
        return $str;
    }
    $pinyin_file_path = dirname(__DIR__) . '/fonts/pinyin.dat';
    $fp = fopen($pinyin_file_path, 'r');
    while (!feof($fp)) {
        $line = trim(fgets($fp));
        $pinyins[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
    }
    fclose($fp);

    for ($i = 0; $i < $slen; $i++) {
        if (ord($str[$i]) > 0x80) {
            $c = $str[$i] . $str[$i + 1];
            $i++;
            if (isset($pinyins[$c])) {
                if ($ishead == 0) {
                    $restr .= $pinyins[$c];
                } else {
                    $restr .= $pinyins[$c][0];
                }
            } else {
                $restr .= "_";
            }
        } else if (preg_match("/[a-z0-9]/i", $str[$i])) {
            $restr .= $str[$i];
        } else {
            $restr .= "_";
        }
    }
    return $restr;
}
// 方法
function ctoLanguagePinyin2($_String, $_Code = 'UTF8')
{ // GBK页面可改为gb2312，其他随意填写为UTF8
    $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" . "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" . "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" . "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" . "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" . "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" . "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" . "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" . "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" . "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" . "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" . "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" . "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" . "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" . "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" . "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
    $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" . "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" . "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" . "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" . "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" . "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" . "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" . "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" . "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" . "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" . "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" . "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" . "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" . "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" . "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" . "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" . "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" . "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" . "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" . "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" . "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" . "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" . "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" . "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" . "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" . "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" . "|-10270|-10262|-10260|-10256|-10254";
    $_TDataKey = explode('|', $_DataKey);
    $_TDataValue = explode('|', $_DataValue);
    $_Data = array_combine($_TDataKey, $_TDataValue);
    arsort($_Data);
    reset($_Data);
    if ($_Code != 'gb2312')
        $_String = ctoLanguagePinyin2_U2_Utf8_Gb($_String);
    $_Res = '';
    for ($i = 0; $i < strlen($_String); $i++) {
        $_P = ord(substr($_String, $i, 1));
        if ($_P > 160) {
            $_Q = ord(substr($_String, ++$i, 1));
            $_P = $_P * 256 + $_Q - 65536;
        }
        $_Res .= ctoLanguagePinyin2_Pinyin($_P, $_Data);
    }
    return preg_replace("/[^a-z0-9]*/", '', $_Res);
}
function ctoLanguagePinyin2_Pinyin($_Num, $_Data)
{
    if ($_Num > 0 && $_Num < 160) {
        return chr($_Num);
    } elseif ($_Num < -20319 || $_Num > -10247) {
        return '';
    } else {
        foreach ($_Data as $k => $v) {
            if ($v <= $_Num)
                break;
        }
        return $k;
    }
}
function ctoLanguagePinyin2_U2_Utf8_Gb($_C)
{
    $_String = '';
    if ($_C < 0x80) {
        $_String .= $_C;
    } elseif ($_C < 0x800) {
        $_String .= chr(0xC0 | $_C >> 6);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x10000) {
        $_String .= chr(0xE0 | $_C >> 12);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x200000) {
        $_String .= chr(0xF0 | $_C >> 18);
        $_String .= chr(0x80 | $_C >> 12 & 0x3F);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    }
    return iconv('UTF-8', 'GB2312', $_String);
}
function _cc_lang_money_to_capital_cn($num)
{
    $char = array("零",  "一", "二", "三",  "四", "五",  "六",  "七", "八",  "九");
    $dw = array("",  "十",  "百",  "千", "万", "亿", "兆");
    $retval = "";
    $proZero = false;
    for ($i = 0; $i < strlen($num); $i++) {
        if ($i > 0)
            $temp = (int) (($num % pow(10, $i + 1)) / pow(10, $i));
        else
            $temp = (int) ($num % pow(10, 1));

        if ($proZero == true && $temp == 0)
            continue;

        if ($temp == 0)
            $proZero = true;
        else
            $proZero = false;

        if ($proZero) {
            if ($retval == "")
                continue;
            $retval = $char[$temp] . $retval;
        } else
            $retval = $char[$temp] . $dw[$i] . $retval;
    }
    if ($retval == "一十")
        $retval = "十";
    return $retval;
}
/**
 * ASCII 转为 字符串
 * @param string $ascii ascii
 * @return string $str 字符串
 */
function _cc_ascii_decode($sacii)
{
    $asc = str_split(strtolower($sacii), 2);
    $str = '';
    for ($i = 0; $i < count($asc); $i++) {
        $str .= chr(hexdec($asc[$i][0] . $asc[$i][1]));
    }
    return mb_convert_encoding($str, 'UTF-8', 'GBK');
}
/**
 * 字符串转为ASCII
 * @param string $str 需要转换的字符串
 * @return string $asc ASCII码
 */
function _cc_ascii_encode($str)
{
    $str = mb_convert_encoding($str, 'GB2312');
    $str = mb_convert_encoding($str, 'GBK');
    $asc = '';
    for ($i = 0; $i < strlen($str); $i++) {
        $temp_str = dechex(ord($str[$i]));
        $asc .= $temp_str[0] . $temp_str[1];
        // $asc .= $temp_str[1] . $temp_str[0];
    }
    return strtoupper($asc);
}
/**
 * 中文转化为英文首字母
 * @param $string
 * @param int $length  转化的长度
 * @return bool|string
 */
function getEnByCnByString($string, $length = 10)
{
    if (empty($string)) {
        return false;
    }
    preg_match_all("/./u", $string, $arr);
    $exp = $arr[0];
    if (empty($exp)) {
        return false;
    }
    $sum = 1;
    $res = '';
    foreach ($exp as $key => $value) {
        $cha = _cc_lang_first_letter($value);
        $res .= $cha;
        if (!empty($cha)) {
            $sum++;
        }
        if ($sum > $length) {
            return $res;
        }
    }
    return $res;
}
/**
 * 获取单个首字母
 * @param $str 字符串
 * @param $isZh 是否支持中文
 */
function _cc_lang_first_letter($str = '', $isZh = true)
{
    // $c = ereg ( '[a-zA-Z]', strtoupper ( substr ( $s0, 0, 1 ) ) );
    // if($c){
    // return strtoupper ( substr ( $s0, 0, 1 ) );
    // }
    // if($fchar >= ord ( "a" ) and $fchar <= ord ( "Z" ))
    // return strtoupper ( $s0{0} );
    // if(is_numeric ( substr ( $s0, 0, 1 ) )){
    // $s0 = _cc_lang_money_to_capital_cn ( substr ( $s0, 0, 1 ) );
    // }
    // $s = $s0;
    if (empty($str)) {
        return '';
    }
    $fir = $fchar = ord($str[0]);
    if ($fchar >= ord('A') && $fchar <= ord('z'))
        return strtoupper($str[0]);

    $encode = mb_detect_encoding($str, array("ASCII", "GB2312", "GBK", "UTF-8"));
    if ($encode) {
    }
    $s1 = @iconv('UTF-8', 'gb2312', $str);
    $s2 = @iconv('gb2312', 'UTF-8', $s1);
    $s = $s2 == $str ? $s1 : $str;
    if (!isset($s[0]) || !isset($s[1])) {
        return '';
    }
    $asc = ord($s[0]) * 256 + ord(empty($s[1]) ? $s[0] : $s[1]) - 65536;
    // $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
    // var_dump(ord($s[1]));
    // dd('---_cc_lang_first_letter----', $str, $s[0], $asc, chr("-52410"));

    // 如果是数字
    if (is_numeric($str)) {
        return $str[0];
    }
    // $len = preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str);
    // if ($len) {
    //     // $s = _cc_ascii_encode($str);
    //     return true;
    // }


    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776 || $asc == -9743) return 'B';
    if ($asc >= -19775 && $asc <= -19219) return 'C';
    if ($asc >= -19218 && $asc <= -18711 || $asc == -9767) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if ($asc >= -18526 && $asc <= -18240) return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if ($asc >= -17922 && $asc <= -17418) return 'H';
    if ($asc >= -17417 && $asc <= -16475) return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641 || $asc == -7182 || $asc == -6928) return 'L';
    if ($asc >= -15640 && $asc <= -15166) return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631 || $asc == -6745) return 'P';
    if ($asc >= -14630 && $asc <= -14150 || $asc == -7703) return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319) return 'S';
    if ($asc >= -13318 && $asc <= -12839) return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if ($asc >= -12556 && $asc <= -11848) return 'X';
    if ($asc >= -11847 && $asc <= -11056) return 'Y';
    if ($asc >= -11055 && $asc <= -10247) return 'Z';
    else return '#';

    return null;
}
