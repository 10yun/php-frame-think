<?php

namespace shiyun\libs;

/**
 * 文件处理
 * @author ctocode-zhw
 */
class FileException extends \Exception {}
class LibsFileFactory
{
    // 模式 描述
    // r 打开文件为只读。文件指针在文件的开头开始。
    // w 打开文件为只写。删除文件的内容或创建一个新的文件，如果它不存在。文件指针在文件的开头开始。
    // a 打开文件为只写。文件中的现有数据会被保留。文件指针在文件结尾开始。创建新的文件，如果文件不存在。
    // x 创建新文件为只写。返回 FALSE 和错误，如果文件已存在。
    // r+ 打开文件为读/写、文件指针在文件开头开始。
    // w+ 打开文件为读/写。删除文件内容或创建新文件，如果它不存在。文件指针在文件开头开始。
    // a+ 打开文件为读/写。文件中已有的数据会被保留。文件指针在文件结尾开始。创建新文件，如果它不存在。
    // x+ 创建新文件为读/写。返回 FALSE 和错误，如果文件已存在。

    use TraitModeInstance;
    use TraitFileMimeType;

    // 设置路径
    protected string $filePath = '';
    public function setFilePath(string $path = '')
    {
        $this->filePath = $path;
        return $this;
    }


    protected string $orderBy = '';
    protected string $orderSort = '';
    public function setOrderBy($str)
    {
        $this->orderBy = $str;
        return $this;
    }
    public function setOrderSort($str)
    {
        $this->orderSort = $str;
        return $this;
    }


    // 写入内容
    public function writeContent($content = '')
    {
        $fileHandle = fopen($this->filePath, "w+") or die("无法打开文件！");
        $put_content = "<?php \n";

        if (is_array($content)) {
            foreach ($content as $val) {
                $put_content .= $val . "\n";
            }
        } else if (is_string($content)) {
            $put_content .= $content . "\n";
        }
        // 写入
        fwrite($fileHandle, $put_content);
        // 关闭
        fclose($fileHandle);
    }
    /**
     * 追加内容
     * 写入文件内容,写入数据str
     */
    function putContents($content = '')
    {
        if ($this->filePath)
            throw new FileException('文件：路径未配置，请setFilePath');
        // 无视文件，是否存在，直接创建，
        ctoFileDirCreate($this->filePath, 0777, _PATH_RUNTIME_);
        chmod($this->filePath, 0777);
        // ob_start ();
        try {
            file_put_contents($this->filePath, $content);
        } catch (\Exception $e) {
            throw new FileException('文件：写入缓存失败，请检查目录权限');
        }
    }
    /**
     * 读取内容
     */
    public function readContent()
    {
        $fileHandle = fopen($this->filePath, "r+") or die("无法打开文件！");
        // fread() 函数读取打开的文件。第一个参数包含待读取文件的文件名，第二个参数规定待读取的最大字节数。
        echo fread($fileHandle, filesize($this->filePath));
        // 读取单行文件 - fgets()文件的首行：
        echo fgets($fileHandle);
        // 输出单行直到 end-of-file
        while (!feof($fileHandle)) {
            echo fgets($fileHandle) . "<br>";
        }
        // 输出单字符直到 end-of-file
        while (!feof($fileHandle)) {
            echo fgetc($fileHandle);
        }
        // 关闭
        fclose($fileHandle);
    }
    /**
     * 移动文件
     * @param string $old_path 旧路径
     * @param string $new_path 新路径
     */
    public static function moveFile(string $old_path = '', string $new_path = '')
    {
        ctoFileDirCreate($new_path, 0777, _PATH_FILE_);
        $newdir = dirname($new_path);
        if (@rename($old_path, $new_path)) {
            return true;
        }
        return false; // '移动文件失败'
    }
    // 更改文件夹 文件
    // 修改文件名
    public static function changeName($path = '', $old_name = '', $new_name = '', $del_local = true)
    {
        $path = preg_replace("/(.+?)\/*$/", "\\1/", $path);
        return self::moveFile($path . $old_name, $path . $new_name); // '重命名文件失败'
    }
    /**
     * 获取文件后缀
     * @param string $filename
     * @return string
     */
    public static function getFileExt($filename = '')
    {
        return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
    }
    /**
     * 获取文件后缀类型
     * @param string $filename
     * @return string
     */
    public static function getExtType($ext = '')
    {
        $ext = strtolower($ext);
        $extArr = [
            'image' => 'gif,jpg,jpeg,png,bmp',
            'docs' => 'doc,docx,xls,xlsx,ppt,txt',
            'codefile' => 'htm,html,php,jsp,asp,py,go',
            'package' => 'zip,rar,gz,bz2',
            'flash' => 'swf,flv',
            'media' => 'swf,flv,mp3,wav,wma,wmv,mid,avi,mpg,asf,rm,rmvb'
        ];
        $dir = 'upload';
        foreach ($extArr as $key => $val) {
            $valArr = explode(",", $val);
            if (in_array($ext, $valArr)) {
                $dir = $key;
                break;
            }
        }
        return $dir;
    }
    /**
     * TODO 暂不支持递归
     * 获取文件夹下的文件名列表
     *  文件获取路径列表
     * @author ctocode-zhw
     * @version 2017-02-10
     * @param $dir string 文件路径
     * @param $dir string 资源路径
     * @param  $urlPrefix string 需要拼接的 文件url
     * @return mixed 文件名信息列表
     */
    public static function getDirLists($dir = null, $urlPrefix = null, $https = false)
    {
        if (empty($dir)) {
            return null;
        }
        $fileList = [];
        $prev = $https ? 'https://' : 'http://';
        if (false != ($handle = opendir($dir))) {
            $i = 0;
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && strpos($file, ".")) {
                    $fileList[$i] = array(
                        'uri' => $prev . $_SERVER['HTTP_HOST'] . '/' . $dir . '/' . $file,
                        'ur2' => !empty($urlPrefix) ? $urlPrefix . $file : '',
                        'file_name' => $file
                    );
                    $i++;
                }
            }
            closedir($handle);
        }
        return $fileList;
    }
    function ctoFileDirList2($dir, $deep = true)
    {
        if (is_dir($dir)) {
            $fp = opendir($dir);
            while (false !== $file = readdir($fp)) {
                if ($deep && is_dir($dir . '/' . $file) && $file != '.' && $file != '..') {
                    $file1 = iconv('gb2312', 'utf-8', $file);
                    $arr[] = $file1; // 保存目录名 可以取消注释
                    echo "<b><font color='green'>目录名：</font></b>", $file1, "<br><hr>";
                    ctoFileDirList2($dir . '/' . $file . '/');
                } else {
                    if ($file != '.' && $file != '..') {
                        $file = iconv('gb2312', 'UTF-8', $file);
                        echo "<b><font color='red'>文件名：</font></b>", $file, "<br><hr>";
                        $arr[] = $file;
                    }
                }
            }
            closedir($fp);
        } else {
            echo $dir . '不是目录';
        }
    }
    public function orderList_func($a, $b)
    {
        if ($a['is_dir'] && !$b['is_dir']) {
            return -1;
        } else if (!$a['is_dir'] && $b['is_dir']) {
            return 1;
        }
        if ($this->orderBy == 'size') {
            if ($a['filesize'] > $b['filesize']) {
                return 1;
            } else if ($a['filesize'] < $b['filesize']) {
                return -1;
            } else {
                return 0;
            }
        } else if ($this->orderBy == 'time') {
            if ($a['datetime'] > $b['datetime']) {
                return 1;
            } else if ($a['datetime'] < $b['datetime']) {
                return -1;
            } else {
                return 0;
            }
        } else if ($this->orderBy == 'type') {
            return strcmp($a['filetype'], $b['filetype']);
        }
        return strcmp($a['filename'], $b['filename']);
    }
    function orderList($a, $b)
    {
        $c = $this->orderList_func($a, $b);
        if ($this->orderDesc != 'asc') {
            $c = $c == 0 ? 0 : ($c == -1 ? 1 : -1);
        }
        return $c;
    }
    function extdir($dir = '')
    {
        $dir = str_replace("\\", "/", $dir);
        if (!preg_match('/\/$/', $dir)) {
            $dir .= '/';
        }
        return $dir;
    }
    function getDirList($dir = '', $orderBy = false, $orderdesc = 'asc')
    {
        $dir = $this->extdir($dir);
        if (!is_dir($dir)) {
            return array();
        }

        $orderBy = strtolower($orderBy);
        $orderBy = in_array($orderBy, array(
            'size',
            'name',
            'time',
            'type'
        )) ? $orderBy : false;
        $this->orderBy = $orderBy;
        $this->orderDesc = strtolower($orderdesc);

        $file_list = array();
        if ($handle = opendir($dir)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename[0] == '.' || $filename == 'Thumbs.db')
                    continue;
                $file = $dir . $filename;
                $arr = array();

                if (is_dir($file)) {
                    $arr['is_dir'] = true;
                    $arr['has_file'] = (count(scandir($file)) > 2);
                    $arr['filesize'] = 0;
                    $arr['is_photo'] = false;
                    $arr['filetype'] = '';
                } else {
                    $file_ext = self::getFileExt($file);
                    $arr['is_dir'] = false;
                    $arr['has_file'] = false;
                    $arr['filesize'] = filesize($file);
                    $arr['is_photo'] = in_array($file_ext, array(
                        'gif',
                        'jpg',
                        'jpeg',
                        'png',
                        'bmp'
                    ));
                    $arr['filetype'] = $file_ext;
                }

                if (preg_match("/[\x7f-\xff]/", $filename)) {
                    $filename = iconv('gb2312', 'utf-8', $filename);
                    // $filename = ctoStrIconv ( $filename, 'gb2312', 'utf-8' );
                }

                $arr['filename'] = $filename;
                $arr['datetime'] = filemtime($file);
                $arr['time'] = cc_time_diff_str($arr['datetime'], 'Y-m-d H:i');
                $arr['time'] = cc_time_diff_str($arr['datetime'], 'Y-m-d H:i');
                $file_list[$i] = $arr;
                $i++;
            }
            closedir($handle);
        }
        if ($this->orderBy == false) {
            return $file_list;
        }
        usort($file_list, array(
            'DirList',
            'orderList'
        ));
        return $file_list;
    }

    // ##
    // @action:获取文件大小,并且格式化显示形式,格式化文件大小
    public static function toExtSize($size = 0, $unit = 'Bty')
    {
        $a = 1024 * 1024 * 1024 * 1024;
        $b = 1024 * 1024 * 1024;
        $c = 1024 * 1024; // Megabyte
        $d = 1024; // Kilobyte
        $unit = strtolower($unit);

        $bsize = 0;
        if ($unit == 'bty' || $unit == 'b') {
            $bsize = $size;
        } else if ($unit == 'k' || $unit == 'kb') {
            $bsize = $size * $d;
        } else if ($unit == 'm' || $unit == 'mb') {
            $bsize = $size * $c;
        } else if ($unit == 'g' || $unit == 'gb') {
            $bsize = $size * $b;
        } else if ($unit == 't' || $unit == 'tb') {
            $bsize = $size * $a;
        }
        $bsize = 10 * $bsize;
        if ($bsize > $a * 10) {
            $rs = floor($bsize / $a) / 10;
            $rs .= ' TB';
        } else if ($bsize > $b * 10) {
            $rs = floor($bsize / $b) / 10;
            $rs .= ' GB';
        } else if ($bsize > $c * 10) {
            $rs = floor($bsize / $c) / 10;
            $rs .= ' MB';
        } else if ($bsize > $d * 10) {
            $rs = floor($bsize / $d) / 10;
            $rs .= ' KB';
        } else {
            $rs = $bsize / 10;
            $rs .= ' Bty';
        }
        return $rs;
    }
}
