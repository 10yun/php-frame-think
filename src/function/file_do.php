<?php

/**
 * 【ctocode】      常用函数 - file_do相关处理
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

// TODO 由mime类型 推测文件后缀 很不安全 慎用 仅在无可奈何的情况下用
function mime_ext($type = '')
{
    $type = strtolower(trim($type));
    if ($type != '') {
        $types = mime_type('baiyu');
        foreach ($types as $key => $val) {
            if ($type == $val) {
                return $key;
            }
        }
    }
    return '';
}
// 由文件后缀 得到mime类型
function mime_type($ext = '')
{
    $mime_types = array(
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'txt' => 'text/plain',

        'doc' => 'application/msword',
        'xsl' => 'application/xml',
        'xslt' => 'application/xslt+xml',
        'xhtml' => 'application/xhtml+xml',
        "rar" => "application/x-rar-compressed",
        'zip' => 'application/zip',
        'gz' => 'application/x-gzip',
        "zip" => "application/x-zip-compressed",
        'swf' => 'application/x-shockwave-flash',
        "swf" => "application/x-shockwave-flash",
        'flv' => 'video/x-flv',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'pdf' => 'application/pdf',

        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'ico' => 'image/x-icon',
        'jp2' => 'image/jp2',
        'xml' => 'application/xml',
        "lrc" => "application/lrc",
        'wbmp' => 'image/vnd.wap.wbmp',

        '3gp' => 'video/3gpp',
        'ai' => 'application/postscript',
        'aif' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'asc' => 'text/plain',
        'atom' => 'application/atom+xml',
        'au' => 'audio/basic',
        'avi' => 'video/x-msvideo',
        'bcpio' => 'application/x-bcpio',
        'bin' => 'application/octet-stream',
        'cdf' => 'application/x-netcdf',
        'cgm' => 'image/cgm',
        'class' => 'application/octet-stream',
        'cpio' => 'application/x-cpio',
        'cpt' => 'application/mac-compactpro',
        'csh' => 'application/x-csh',
        'dcr' => 'application/x-director',
        'dif' => 'video/x-dv',
        'dir' => 'application/x-director',
        'djv' => 'image/vnd.djvu',
        'djvu' => 'image/vnd.djvu',
        'dll' => 'application/octet-stream',
        'dmg' => 'application/octet-stream',
        'dms' => 'application/octet-stream',
        'dtd' => 'application/xml-dtd',
        'dv' => 'video/x-dv',
        'dvi' => 'application/x-dvi',
        'dxr' => 'application/x-director',
        'eps' => 'application/postscript',
        'etx' => 'text/x-setext',
        'exe' => 'application/octet-stream',
        'ez' => 'application/andrew-inset',
        'gram' => 'application/srgs',
        'grxml' => 'application/srgs+xml',
        'gtar' => 'application/x-gtar',
        'hdf' => 'application/x-hdf',
        'hqx' => 'application/mac-binhex40',
        'ice' => 'x-conference/x-cooltalk',
        'ics' => 'text/calendar',
        'ief' => 'image/ief',
        'ifb' => 'text/calendar',
        'iges' => 'model/iges',
        'igs' => 'model/iges',
        'jnlp' => 'application/x-java-jnlp-file',
        'js' => 'application/x-javascript',
        'kar' => 'audio/midi',
        'latex' => 'application/x-latex',
        'lha' => 'application/octet-stream',
        'lzh' => 'application/octet-stream',
        'm3u' => 'audio/x-mpegurl',
        'm4a' => 'audio/mp4a-latm',
        'm4p' => 'audio/mp4a-latm',
        'm4u' => 'video/vnd.mpegurl',
        'm4v' => 'video/x-m4v',
        'mac' => 'image/x-macpaint',
        'man' => 'application/x-troff-man',
        'mathml' => 'application/mathml+xml',
        'me' => 'application/x-troff-me',
        'mesh' => 'model/mesh',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mif' => 'application/vnd.mif',
        'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mp2' => 'audio/mpeg',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpga' => 'audio/mpeg',
        'ms' => 'application/x-troff-ms',
        'msh' => 'model/mesh',
        'mxu' => 'video/vnd.mpegurl',
        'nc' => 'application/x-netcdf',
        'oda' => 'application/oda',
        'ogg' => 'application/ogg',
        'ogv' => 'video/ogv',
        'pbm' => 'image/x-portable-bitmap',
        'pct' => 'image/pict',
        'pdb' => 'chemical/x-pdb',
        'pgm' => 'image/x-portable-graymap',
        'pgn' => 'application/x-chess-pgn',
        'pic' => 'image/pict',
        'pict' => 'image/pict',
        'pnm' => 'image/x-portable-anymap',
        'pnt' => 'image/x-macpaint',
        'pntg' => 'image/x-macpaint',
        'ppm' => 'image/x-portable-pixmap',
        'ppt' => 'application/vnd.ms-powerpoint',
        'ps' => 'application/postscript',
        'qt' => 'video/quicktime',
        'qti' => 'image/x-quicktime',
        'qtif' => 'image/x-quicktime',
        'ra' => 'audio/x-pn-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'ras' => 'image/x-cmu-raster',
        'rdf' => 'application/rdf+xml',
        'rgb' => 'image/x-rgb',
        'rm' => 'application/vnd.rn-realmedia',
        'roff' => 'application/x-troff',
        'rtf' => 'text/rtf',
        'rtx' => 'text/richtext',
        'sgm' => 'text/sgml',
        'sgml' => 'text/sgml',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'silo' => 'model/mesh',
        'sit' => 'application/x-stuffit',
        'skd' => 'application/x-koan',
        'skm' => 'application/x-koan',
        'skp' => 'application/x-koan',
        'skt' => 'application/x-koan',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'snd' => 'audio/basic',
        'so' => 'application/octet-stream',
        'spl' => 'application/x-futuresplash',
        'src' => 'application/x-wais-source',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'svg' => 'image/svg+xml',
        't' => 'application/x-troff',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tr' => 'application/x-troff',
        'tsv' => 'text/tab-separated-values',
        'ustar' => 'application/x-ustar',
        'vcd' => 'application/x-cdlink',
        'vrml' => 'model/vrml',
        'vxml' => 'application/voicexml+xml',
        'wav' => 'audio/x-wav',
        'wbxml' => 'application/vnd.wap.wbxml',
        'webm' => 'video/webm',
        'wml' => 'text/vnd.wap.wml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmls' => 'text/vnd.wap.wmlscript',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'wmv' => 'video/x-ms-wmv',
        'wrl' => 'model/vrml',
        'xbm' => 'image/x-xbitmap',
        'xht' => 'application/xhtml+xml',
        'xls' => 'application/vnd.ms-excel',
        'xpm' => 'image/x-xpixmap',
        'xul' => 'application/vnd.mozilla.xul+xml',
        'xwd' => 'image/x-xwindowdump',
        'xyz' => 'chemical/x-xyz',
        "apk" => "application/vnd.android.package-archive",
        "bin" => "application/octet-stream",
        "cab" => "application/vnd.ms-cab-compressed",
        "gb" => "application/chinese-gb",
        "gba" => "application/octet-stream",
        "gbc" => "application/octet-stream",
        "jad" => "text/vnd.sun.j2me.app-descriptor",
        "jar" => "application/java-archive",
        "nes" => "application/octet-stream",
        "sis" => "application/vnd.symbian.install",
        "sisx" => "x-epoc/x-sisx-app",
        "smc" => "application/octet-stream",
        "smd" => "application/octet-stream",
        "wap" => "text/vnd.wap.wml wml",
        "mrp" => "application/mrp",
        "wma" => "audio/x-ms-wma"
    );
    $ext = strtolower(trim($ext));
    if ($ext == 'baiyu') {
        return $mime_types;
    }
    if ($ext == '') {
        return 'application/octet-stream';
    }
    return isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
}
