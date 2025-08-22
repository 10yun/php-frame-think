<?php

namespace shiyun\libs;

// $logsObj = LibLogger::getInstance()->setDir('runtime')->writeLog ( 'SELECT FORM ' );
/**
 * 文件日志
 * ----------
 * @author ctocode-lww
 * @version 2018-10-17
 */
class LibLogger
{
    use \shiyun\libs\TraitModeInstance;

    protected $baseDir = '';
    protected $logDir = '';
    protected $logGroup = '';
    protected $logName = '';
    protected $printType = 'var_export';
    protected $channelType = 'file';
    function __construct()
    {
        // $this->setBaseDir(_PATH_RUNTIME_);
        $this->setDir(_PATH_RUNTIME_);
    }
    // 设置存储驱动通道
    public function setSaveChannel($channel = '')
    {
        $this->channelType = $channel;
        return $this;
    }
    public function setBaseDir($baseDir = '')
    {
        if (!is_dir($baseDir))
            mkdir($baseDir, 0777);
        $this->baseDir = $baseDir;
        return $this;
    }
    public function setDir($dirPath = '')
    {
        if (!is_dir($dirPath))
            mkdir($dirPath, 0777);
        $this->logDir = $dirPath;
        return $this;
    }
    public function setGroup($group = '')
    {
        $this->logGroup = $group;
        return $this;
    }
    public function setName($name = '')
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $name = $name . '.log';
        }
        $this->logName = $name;
        return $this;
    }
    public function setPrintType($type = '')
    {
        return $this;
    }
    /**
     * 记录数据信息
     * @param string $content 日志内容
     * @param string $file_name 文件名
     */
    public function writeInfo($content)
    {
        $this->writeLog($content, 'info');
    }
    /**
     * 记录错误信息
     * 错误日志LOG
     * @param string $content 日志内容
     * @param string $file_name 文件名
     */
    public function writeError($content)
    {
        $this->writeLog($content, 'error');
    }
    /**
     * 记录调试信息
     * @param string $content 日志内容
     * @param string $file_name 文件名
     */
    public function writeDebug($content)
    {
        $this->writeLog($content, 'debug');
    }

    /**
     * 写入日志
     * @param string $content 日志内容
     * @param string $file_name 文件名
     */
    public function writeLog($content, $type = 'debug')
    {
        $currTime = time();
        $currDate = date('Y-m-d H:i:s', $currTime);

        $fileDirArr = [];
        $fileDirArr[] = $this->logDir;
        $fileDirArr[] = "/log_{$type}/";
        if (!empty($this->logGroup)) {
            $fileDirArr[] = $this->logGroup;
        }
        $fileDirArr[] = date("Y-m", $currTime);
        $fileDirPath =  implode("/", $fileDirArr);
        // 目录不存在
        if (!is_dir($fileDirPath)) {
            mkdir($fileDirPath, 0777, true);
        }
        $fileDate = date("d", $currTime);
        $fileName = !empty($this->logName) ? $this->logName  : "{$fileDate}.log";
        $filePath = $fileDirPath . '/' . $fileName;
        // 如果文件不存在
        if (!file_exists($filePath)) {
            file_put_contents($filePath, "========== {$type}日志 - {$currDate} ==========\n");
        }
        // 如果没权限
        if (!is_writeable($filePath))
            chmod($filePath, 0777);

        $logContArr = [];
        $logContArr[] = "【{$currDate}】[{$type}]";
        // $logContArr[] = (is_array($content) || is_object($content)) ? print_r($content, 1) : $content;
        $logContArr[] = (is_array($content) || is_object($content)) ? var_export($content, true) : $content;
        $logContStr = implode("\n", $logContArr) . "\n";

        switch ($this->channelType) {
            case 'mongodb':
                break;
            case 'redis':
                break;
            case 'file':
            default:
                // $logfile = fopen($filePath, "a+");
                // fwrite($logfile, $logContStr);
                // fclose($logfile);
                @file_put_contents($filePath, $logContStr, FILE_APPEND);
                break;
        }
    }
}
