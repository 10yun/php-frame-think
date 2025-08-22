<?php

// 富文本
function cc_html_purifier_xss($content)
{
    require _PATH_PROJECT_ . "/vendor/ezyang/htmlpurifier/library/HTMLPurifier.php";
    $config = HTMLPurifier_Config::createDefault();
    // 允许 TinyMCE 常用标签
    $config->set('HTML.Allowed', 'p,br,strong,em,b,i,u,ul,ol,li,a[href|target],img[src|alt|width|height|style],span[style],div[style]');
    $config->set('CSS.AllowedProperties', ['color', 'font-weight', 'text-decoration', 'font-style', 'background-color', 'text-align']);
    $config->set('HTML.SafeIframe', true);
    $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www.10yun.com/)%'); // 允许嵌入 iframe 来源
    $purifier = new HTMLPurifier($config);
    $cleanHtml = $purifier->purify($content);
    return $cleanHtml;
}
function cc_html_encode(string $content = '')
{
    return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
}
function cc_html_decode(string $content = '')
{
    return htmlspecialchars_decode($content);
}

/**
 * 转译
 */
function analysRichTextEn($text = '') {}
/**
 * 解除 转译
 */
function analysRichTextDe($text = '')
{
    // $article_content = mb_strcut ( strip_tags ( $val['article_content'] ), 0, 100, 'utf-8' ) . '...';
    // $article_content = analysEmojiDe ( $article_content);
    // $article_content= str_replace ( "\n", "<br/>", $article_content );
    // $article_content = strip_tags ( htmlspecialchars_decode ( $article_content) );
    // $article_content= cc_str_strcut ( htmlspecialchars_decode ($article_content ), 50 );
    // $article_content= str_replace ( "&nbsp;", "",$article_content);
    // $article_content = str_replace ( "&nbsp;", "", strip_tags ( $article_content ) );

    // 提问内容涉及表情包的处理

    /**
     *
    if(! empty ($text) && preg_match ('/<span.*?>/is', htmlspecialchars_decode ($text) )){
    }
     */
    $text_last = '';
    $text_last = htmlspecialchars_decode($text);
    return $text_last;
}
/**
 * 清除html
 */
function cc_filter_htmltag_all($str = '')
{
    // 用于替换
    $replace_arr1 = array(
        "'<script[^>]*?>.*?<!-- </script> -->'si" => "", // 去掉 javascript
        "'<script[^>]*?>.*?</script>'si" => "", // 去掉 javascript
        "'javascript[^>]*?>.*?'si" => "", // 去掉 javascript
        "'<style[^>]*?>.*?</style>'si" => "", // 去掉 css
        "'<[/!]*?[^<>]*?>'si" => "", // 去掉 HTML 标记
        "'<[\/\!]*?[^<>]*?>'si" => "", // 去掉 HTML 标记
        "'<!--[/!]*?[^<>]*?>'si" => "", // 去掉 注释标记
        "'([rn])[s]+'" => "", // 去掉空白字符
        "'([\r\n])[\s]+'" => "", // 去掉空白字符

        // "\1",
        // "\\1",
        // 替换 HTML 实体
        "'&(quot|#34);'i" => "\"",
        "'&(amp|#38);'i" => "&",
        "'&(lt|#60);'i" => "<",
        "'&(gt|#62);'i" => ">",
        "'&(nbsp|#160);'i" => " ",
        "'&(iexcl|#161);'i" => chr(161),
        "'&(cent|#162);'i" => chr(162),
        "'&(pound|#163);'i" => chr(163),
        "'&(copy|#169);'i" => chr(169),
        "'&#(d+);'e" => "chr(\1)",
        "'&#(\d+);'e" => "chr(\\1)",


    );
    $out = preg_replace(array_keys($replace_arr1), array_values($replace_arr1), $str);
    $replace_arr2 = [
        "<" => "",
        ">" => "",
        "alert" => "",
        "java" => "",
        "script" => "",
        "(" => "",
        ")" => "",
    ];
    $out = str_replace(array_keys($replace_arr2), array_values($replace_arr2), $out);
    return $out;
}
/**
 * @action 过滤富文本的img,a标签和纯文本
 * @author ctocode-zwj
 * @param $content 需要过滤的内容
 * @return array
 */
function cc_filter_html($content)
{
    $content = preg_replace("/<p.*?>|<\/p>/is", "", $content);
    $content = preg_replace("/<span.*?>|<\/span>/is", "", $content); // 过滤span标签
    $pregImgRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
    $content = preg_replace($pregImgRule, '#@#ctocode-img-${1}#@#', $content);
    $pregARule = "/<a[^<>]+href *\= *[\"']?([^ '\"]+).*<\/a>/i";
    $content = preg_replace($pregARule, '#@#ctocode-a-${1}#@#', $content);
    $content = explode('#@#', $content);
    $content = array_filter($content);
    $data = array();
    foreach ($content as $v) {
        $resultImg = explode('ctocode-img-', $v);
        $resultA = explode('ctocode-a-', $v);
        if (!empty($resultImg[1])) {
            // img
            $data[] = array(
                'img' => $resultImg[1]
            );
        } else if (!empty($resultA[1])) {
            // a
            $data[] = array(
                'a' => $resultA[1]
            );
        } else {
            $appEmotion = "";
            $appEmotion = preg_replace_callback('/@E(.{6}==)/', function ($r) {
                return base64_decode($r[1]);
            }, strip_tags($v));
            $data[] = array(
                'text' => $appEmotion
            );
        }
    }
    return $data;
}

function cc_filter_html_a_link($url)
{
    // $pattern = '/<\s*a\s.*?href\s*=\s*(["\'])?(?(1)(.*?)\1|([^\s>]+))[^>]*>?(.*?)<\/a>/isx';
    // preg_match_all($pattern, $document, $links, PREG_SET_ORDER);
    // $match = ['link' => [], 'content' => [], 'all' => []];
    // foreach ($links as $link) {
    //     if (!empty($link[2])) {
    //         $match['link'][] = $link[2];
    //     } elseif (!empty($link[3])) {
    //         $match['link'][] = $link[3];
    //     }
    //     if (!empty($link[4])) {
    //         $match['content'][] = $link[4];
    //     }
    //     if (!empty($link[0])) {
    //         $match['all'][] = $link[0];
    //     }
    // }
    // return $match;

    $html = '';
    preg_match_all("/<a(s*[^>]+s*)href=([\"|']?)([^\"'>\s]+)([\"|']?)/ies", $html, $out);
    $arrLink = $out[3];
    $arrUrl = parse_url($url);
    $dir = '';
    if (isset($arrUrl['path']) && !empty($arrUrl['path'])) {
        $dir = str_replace("\\", "/", $dir = dirname($arrUrl['path']));
        if ($dir == "/") {
            $dir = "";
        }
    }
    if (is_array($arrLink) && count($arrLink) > 0) {
        // $arrLink=array_unique($arrLink); //函数移除数组中的重复的值，并返回结果数组。
        foreach ($arrLink as $key => $val) {
            $val = strtolower($val);
            if (preg_match('/nofollow/', $val)) {
                unset($arrLink[$key]);
            } elseif (preg_match('/^#*$/isU', $val)) { // 过滤特殊字符的链接
                unset($arrLink[$key]);
            } elseif (!preg_match('/html/', $val)) { // 过滤.html除外的链接
                unset($arrLink[$key]);
            } elseif (preg_match('/^\//isU', $val)) {
                $arrLink[$key] = 'http://' . $arrUrl['host'] . $val;
            } elseif (preg_match('/^javascript/isU', $val)) {
                unset($arrLink[$key]);
            } elseif (preg_match('/^mailto:/isU', $val)) { // 过滤邮件链接
                unset($arrLink[$key]);
            } elseif (!preg_match('/^\//isU', $val) && strpos($val, 'http://') === FALSE) {

                $arrLink[$key] = 'http://' . $arrUrl['host'] . $dir . '/' . $val;
            }
        }
    }
    sort($arrLink);
    return $arrLink;
}
// 常见正则处理
function cc_html_regular($type = '', $content = '')
{
    if (empty($type) || empty($content)) {
        return '';
    }
    $type = strtoupper($type);
    $reg = '';

    $arr = [
        'html_head_title_text' => '|<title>(.*?)<\/title>|i',
        'html_h1_text' => '/<h1[\s\S]*?>([\s\S]*?)/<\/h1>/i',
        'html_div_html' => '/<div\s+[^>]*>[^<>]*<\/div>(?:\s*<a\s+[^<>]*>[^<>]*<\/a>)*/',
        'html_li_html' => '/<li>(.*?)<\/li>*/',
        'script_src' => '/<script +.*src="([^"]+)"/i',
        // link 的href
        'link_href' => '/<link.+?href=(\'|")(.+?)\\1/s',
        'link_href1' => '/<link +.*href="([^"]+)"/i'
    ];

    switch ($type) {
        case 'a_href':
        case 'a_html':
            $reg = '|<a href="(.*?)">(.*?)</a>|';
            break;
        case 'a_href_html': // a 的 herf,html ===后续可根据需求添加 class
            $reg = '/<a.*?(?: |\\t|\\r|\\n)?href=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>(.+?)<\/a.*?>/sim';
            preg_match_all($reg, $content, $matches, PREG_PATTERN_ORDER);
            return $matches;
        case 'img_src': // img 的 src 链接
            $reg = '/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim';
            preg_match_all($reg, $content, $matches, PREG_PATTERN_ORDER);
            return $matches;
            break;
        default:
            return '';
    }
    // preg_match 函数用于进行正则表达式匹配，成功返回 1 ，否则返回 0 。
    // 第一次匹配成功后就会停止匹配，如果要实现全部结果的匹配，即搜索到subject结尾处，则需使用 preg_match_all()
    // preg_match ( $reg, $content, $matches );
    preg_match_all($reg, $content, $matches);
    return $matches;

    $reg = '/^(#|javascript.*?|ftp://.+|http://.+|.*?href.*?|play.*?|index.*?|.*?asp)+$/i';
    //
    $reg = '/^(down.*?.html|d+_d+.htm.*?)$/i';
    $rex = "/([hH][rR][eE][Ff])s*=s*['\"]*([^>'\"s]+)[\"'>]*s*/i";
    $reg = '/^(down.*?.html)$/i';
}
