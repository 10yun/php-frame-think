<?php

// 文件删除
function ctoFileDel($dir, $type = TRUE)
{
    // 先删除目录下的文件：
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            if (!is_dir($fullpath)) {
                if (file_exists($fullpath)) {
                    unlink($fullpath);
                }
            } else {
                ctoFileDel($fullpath);
            }
        }
    }
    closedir($dh);
    if ($type == TRUE) {
        // 删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}

// 写入文件内容,写入数据str
function ctoFilePutContents($file_path, $content)
{
    if (empty($file_path))
        return '';
    // 无视文件，是否存在，直接创建，
    ctoFileDirCreate($file_path, 0777, _PATH_RUNTIME_);
    chmod($file_path, 0777);
    // ob_start ();
    try {
        file_put_contents($file_path, $content);
    } catch (\Exception $e) {
        echo '<div style="color:red;">' . '写入缓存失败!请检查目录权限!' . '</div>';
    }
}
/**
 * @action 写入php内容
 * @param string $file_name
 * @param string $content
 * @param string $is_root
 * @param string $min  去除空格 
 * @param string $slize 是否序列化
 */
/*
 * @可被删除或放弃的文件缓存 关于文件缓存格式 是 var_export | serialize *
 * @效率对比 这个有待商榷 ,有的地方说 serialize序列化的效率更高
 * @很多文档支持serialize
 * @在本系统中实测并未发现太大差别
 */
function ctoFileArrayStr($array = array(), $sql = 1)
{
    $rs = '';
    if (is_array($array)) {
        $rs = @serialize($array);
        if ($sql) {
            $rs = addslashes($rs);
        }
    }
    return $rs;
}
function ctoFilePutPhp($file_path = '', $content = '', $is_root = TRUE, $min = FALSE, $slize = FALSE)
{
    if ($slize == TRUE) { // 是否序列化
        $cache = array();
        $cache['data'] = $val;
        $cata = ctoFileArrayStr($cache, 0);
    }
    $put_content = var_export($content, true);
    if ($min == TRUE) {
        $put_content = preg_replace("'([\r\n])[\s]+'", "", $put_content);
    }

    // if($min){
    // $put_content = preg_replace("'([\r\n])[\s]+'", "",$cache);
    // }
    // $cata = "<?php \r\nif(!defined('DBGMS_ROOT')){\n\theader('HTTP/1.1 404 Not Found' );\n\texit('权限路径.No
    // direct script access allowed');\n}\nreturn ";
    $cata = "<?php \r\n";
    if ($is_root == TRUE) {
        // $cata .= "if(!defined('_CTOCODE_ROOT_')){\r\n";
        // $cata .= "\t" . "header('HTTP/1.1 404 Not Found');\r\n";
        // $cata .= "\t" . "exit('权限路径.No direct script access allowed');\r\n";
        // $cata .= "}\r\n";
        $cata .= "\r\n";
    }
    $cata .= 'return ';
    $cata .= $put_content;
    $cata .= ";\r\n?>";
    ctoFilePutContents($file_path, $cata);
}

/*
 * @action: 写入文件
 * 【文件】 $path,文件存放路径; $type,模式; $content,文件内容;
 */
function ctoFileFopen($file_path = NULL, $content = NULL, $open_type = "w+", $is_root)
{
    $put_content = '';
    $put_content .= "<?php \n";

    // 模式 描述
    // r 打开文件为只读。文件指针在文件的开头开始。
    // w 打开文件为只写。删除文件的内容或创建一个新的文件，如果它不存在。文件指针在文件的开头开始。
    // a 打开文件为只写。文件中的现有数据会被保留。文件指针在文件结尾开始。创建新的文件，如果文件不存在。
    // a 写入方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。

    // x 创建新文件为只写。返回 FALSE 和错误，如果文件已存在。
    // r+ 打开文件为读/写、文件指针在文件开头开始。
    // w+ 打开文件为读/写。删除文件内容或创建新文件，如果它不存在。文件指针在文件开头开始。
    // a+ 打开文件为读/写。文件中已有的数据会被保留。文件指针在文件结尾开始。创建新文件，如果它不存在。
    // a+ 读写方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。
    // x+ 创建新文件为读/写。返回 FALSE 和错误，如果文件已存在。

    // 创建写入
    $myfile = fopen($path, $open_type) or die("无法打开文件！");
    foreach ($content as $arr_name => $v0) {
        foreach ($v0 as $k1 => $v1) {
            if (!is_array($v1)) {
                if (is_numeric($v2)) {
                    $put_content .= "\${$arr_name}['$k1'] = $v1;\n";
                } else {
                    $put_content .= "\${$arr_name}['$k1'] = '$v1';\n";
                }
            } else {
                foreach ($v1 as $k2 => $v2) {
                    if (is_numeric($v2)) {
                        $put_content .= "\${$arr_name}['$k1']['$k2'] = $v2;\n";
                    } else {
                        $put_content .= "\${$arr_name}['$k1']['$k2'] = '$v2';\n";
                    }
                }
            }
        }
        $put_content .= "\n";
    }
    // 末尾追加
    foreach ($content as $k0 => $v0) {
        foreach ($v0 as $k1 => $v1) {
            foreach ($v1 as $k2 => $v2) {
                if (is_numeric($v2)) {
                    $put_content .= "\$" . "{$k0}['$k1']['$k2'] = $v2;\n";
                } else {
                    $put_content .= "\$" . "{$k0}['$k1']['$k2'] = \"$v2\";\n";
                }
            }
        }
        $put_content .= "\n";
    }
    $put_content .= "<?php \n";
    // 打开文件
    $myfile = fopen($path, "w+") or die("无法打开文件！");
    // 其他写入
    foreach ($content as $val) {
        $put_content .= $val . "\n";
    }
    // 写入
    fwrite($myfile, $put_content);
    // 关闭
    fclose($myfile);

    /* 读取文件 */
    $myfile = fopen($path, "r+") or die("无法打开文件！");
    // fread() 函数读取打开的文件。第一个参数包含待读取文件的文件名，第二个参数规定待读取的最大字节数。
    echo fread($myfile, filesize($path));
    // 读取单行文件 - fgets()文件的首行：
    echo fgets($myfile);
    // 输出单行直到 end-of-file
    while (!feof($myfile)) {
        echo fgets($myfile) . "<br>";
    }
    // 输出单字符直到 end-of-file
    while (!feof($myfile)) {
        echo fgetc($myfile);
    }
    // 关闭
    fclose($myfile);
    /* 读取文件 */
    $html_text = file($path);
    foreach ($html_text as $key => $val) {
        echo "Line <b> $key</b> : " . htmlspecialchars($val) . "<br />\n";
    }
}

/**
 * @action 多级文件夹创建，文件夹不一定存在
 * @author ctocode-zhw
 * @version 2017-07-18
 */
function ctoFileDirCreate($dir_all, $mode = 0777, $dir_base = '')
{
    $dir_all = preg_replace('/^(.*)\/(.*)/', '\\1', $dir_all);
    if (trim($dir_all) == '') // exit ( '路径不能为空' );
        return '';
    // 项目根目录
    $dir_base = !empty($dir_base) ? $dir_base : _PATH_RUNTIME_;

    if (!str_contains($dir_all, _PATH_RUNTIME_)  && !str_contains($dir_all, _PATH_FILE_)) {
        // 是否存在基本该项目中/data和/file文件夹中，如果不是，防止恶意文件，强制移动到 /data文件价下
        $dir_base = _PATH_RUNTIME_;
    }
    // 拆解dir路径
    $dir_need = str_replace($dir_base, '', $dir_all);
    $dir_need = ctoFileDirReplace($dir_need);
    // 判断首位/
    $dir_need = $dir_need[0] == '/' ? substr($dir_need, 1, strlen($dir_need)) : $dir_need;
    $dir_need = $dir_need[strlen($dir_need) - 1] == '/' ? substr($dir_need, 0, strlen($dir_need) - 1) : $dir_need;
    $dir_arr = explode('/', $dir_need);
    if (count($dir_arr) < 2) {
        if (!@mkdir($dir_base . '/' . $dir_need, $mode))
            return '创建目录失败';
        return '';
    }
    $dir_join = $dir_base . '';
    foreach ($dir_arr as $key => $val) {
        if ($val == '')
            continue;
        $dir_join .= '/' . trim($val);
        if (!is_dir($dir_join) || !is_writeable($dir_join)) { // 目录不存在或者不可写的话，创建
            if (!is_dir($dir_join)) {
                if (!@mkdir($dir_join, $mode))
                    return '创建目录失败';
            } else {
                chmod($dir_join, $mode);
            }
        }
    }
    return '';
    try {
        mkdir($val, 0777);
    } catch (\Exception $e) {
        $msg = '目录不存在并且创建失败!请检查目录权限!';
        return FALSE;
    }
}
/**
 * @action 文件夹删除,清空一个目录
 * @author ctocode-zhw
 * @version 2017-07-18
 */
function ctoFileDirDel($dir)
{
    if (empty($dir)) {
        return '';
    }
    if (is_dir($dir)) {
        $dr = dir($dir);
        while ($file = $dr->read()) {
            if ($file == "." || $file == "..") {
                continue;
            } elseif (is_dir("$dir/$file")) {
                ctoFileDirDel("$dir/$file");
            } else {
                @unlink("$dir/$file");
            }
        }
        $dr->close();
        @rmdir($dir);
    } else {
        return '文件夹不存在';
    }
    return '';
}
/*
 *
 * 删除指定目录中的所有目录及文件（或者指定文件）
 * 可扩展增加一些选项（如是否删除原目录等）
 * 删除文件敏感操作谨慎使用
 * @param $dir 目录路径
 * @param array $file_type指定文件类型
 */
function ctoFileDeleteAll($dir, $file_type = '')
{
    if (is_dir($dir)) {
        $files = scandir($dir);
        // 打开目录 //列出目录中的所有文件并去掉 . 和 ..
        foreach ($files as $filename) {
            if ($filename != '.' && $filename != '..') {
                if (!is_dir($dir . '/' . $filename)) {
                    if (empty($file_type)) {
                        unlink($dir . '/' . $filename);
                    } else {
                        if (is_array($file_type)) {
                            // 正则匹配指定文件
                            if (preg_match($file_type[0], $filename)) {
                                unlink($dir . '/' . $filename);
                            }
                        } else {
                            // 指定包含某些字符串的文件
                            if (false != stristr($filename, $file_type)) {
                                unlink($dir . '/' . $filename);
                            }
                        }
                    }
                } else {
                    $this->ctoFileDeleteAll($dir . '/' . $filename);
                    rmdir($dir . '/' . $filename);
                }
            }
        }
    } else {
        if (file_exists($dir))
            unlink($dir);
    }
}
// dir 目录名称替换
function ctoFileDirReplace($dir = '')
{
    if ($dir != '') {
        $dir = str_replace("\\", "/", $dir);
        $dir = str_replace("\\", "/", $dir);
        $dir = preg_replace('/(\/|\\\\){1,}/', '/', $dir);
        $dir = preg_replace("/(.+?)\/*$/", "/\\1", $dir);
        $dir = preg_replace("/\/{1,}/", '/', $dir);
    }
    return $dir;
}
