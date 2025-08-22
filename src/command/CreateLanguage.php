<?php

declare(strict_types=1);

namespace shiyun\command;

use ReflectionClass;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use shiyun\libs\TranslateYoudao;
use Exception;

/**
 * 生成多语言
 */
class CreateLanguage extends Command
{
    protected function configure()
    {
        $this->setName('CreateApiFlag')
            ->addOption('type', null, Option::VALUE_OPTIONAL, '语言')
            ->setDescription('生成多语言 flag');
    }

    protected function execute(Input $input, Output $output)
    {
        // try {
        //     // 译文
        //     $translations = [];
        //     if (file_exists("translate.json")) {
        //         $tmps = json_decode(file_get_contents("translate.json"), true);
        //         foreach ($tmps as $tmp) {
        //             if (!isset($tmp['key'])) {
        //                 continue;
        //             }
        //             $translations[$tmp['key']] = $tmp;
        //         }
        //     }
        //     foreach (['api', 'web'] as $type) {
        //         // 读取文件
        //         $content = file_exists("original-{$type}.txt") ? file_get_contents("original-{$type}.txt") : "";
        //         $array = array_values(array_filter(array_unique(explode("\n", $content))));
        //         // 提取要翻译的
        //         $datas = [];
        //         $needs = [];
        //         foreach ($array as $text) {
        //             $text = trim($text);
        //             if ($tmp = json_decode($text, true)) {
        //                 $key = key($tmp);
        //                 $value = current($tmp);
        //             } else {
        //                 $key = $value = $text;
        //             }
        //             if (isset($translations[$key])) {
        //                 $datas[] = $translations[$key];
        //             } else {
        //                 $needs[$key] = $value;
        //             }
        //         }
        //         define('YOUDAO_APP_KEY', '');
        //         define('YOUDAO_SEC_KEY', '');
        //         $waits = array_chunk($needs, 200, true);
        //         // 分组翻译
        //         $YD = new TranslateYoudao(YOUDAO_APP_KEY, YOUDAO_SEC_KEY);
        //         $func = function ($text) {
        //             if (!$text) {
        //                 return null;
        //             }
        //             return preg_replace_callback("/^\(\*\)\s*[A-Z]/", function ($match) {
        //                 return strtolower($match[0]);
        //             }, ucfirst($text));
        //         };
        //         foreach ($waits as $items) {
        //             $text = implode("\n", $items);
        //             $TCS = explode("\n", $YD->translate($text, "zh-CHS", "zh-CHT"));    // 繁体
        //             $ENS = explode("\n", $YD->translate($text, "zh-CHS", "en"));        // 英语
        //             $KOS = explode("\n", $YD->translate($text, "zh-CHS", "ko"));        // 韩语
        //             $JAS = explode("\n", $YD->translate($text, "zh-CHS", "ja"));        // 日语
        //             $DES = explode("\n", $YD->translate($text, "zh-CHS", "de"));        // 德语
        //             $FRS = explode("\n", $YD->translate($text, "zh-CHS", "fr"));        // 法语
        //             $IDS = explode("\n", $YD->translate($text, "zh-CHS", "id"));        // 印度尼西亚
        //             $index = 0;
        //             foreach ($items as $key => $item) {
        //                 $tmp = [];
        //                 $tmp['key'] = $key;
        //                 $tmp['zh'] = $key != $item ? $item : "";
        //                 $tmp["zh-CHT"] = $func($TCS[$index]);
        //                 $tmp["en"] = $func($ENS[$index]);
        //                 $tmp["ko"] = $func($KOS[$index]);
        //                 $tmp["ja"] = $func($JAS[$index]);
        //                 $tmp["de"] = $func($DES[$index]);
        //                 $tmp["fr"] = $func($FRS[$index]);
        //                 $tmp["id"] = $func($IDS[$index]);
        //                 $datas[] = $translations[$key] = $tmp;
        //                 $index++;
        //             }
        //         }
        //         // 按长度排序
        //         $inOrder = [];
        //         foreach ($datas as $index => $item) {
        //             $key = $item['key'];
        //             if (str_contains($key, '(*)')) {
        //                 $inOrder[$index] = strlen($key);
        //             } else {
        //                 $inOrder[$index] = strlen($key) + 10000000000;
        //             }
        //         }
        //         array_multisort($inOrder, SORT_DESC, $datas);
        //         // 合成数组
        //         $results = ['key' => []];
        //         $index = 0;
        //         foreach ($datas as $items) {
        //             $results['key'][$items['key']] = $index++;
        //             foreach ($items as $key => $item) {
        //                 if ($key === 'key') {
        //                     continue;
        //                 }
        //                 if (!isset($results)) {
        //                     $results[$key] = [];
        //                 }
        //                 $results[$key][] = $item;
        //             }
        //         }
        //         // 生成文件
        //         if ($type === 'api') {
        //             if (!is_dir("../public/language/api")) {
        //                 mkdir("../public/language/api", 0777, true);
        //             }
        //             foreach ($results as $key => $item) {
        //                 $file = "../public/language/api/$key.json";
        //                 file_put_contents($file, json_encode($item, JSON_UNESCAPED_UNICODE));
        //             }
        //         } elseif ($type === 'web') {
        //             if (!is_dir("../public/language/web")) {
        //                 mkdir("../public/language/web", 0777, true);
        //             }
        //             foreach ($results as $key => $item) {
        //                 $file = "../public/language/web/$key.js";
        //                 file_put_contents($file, "if(typeof window.LANGUAGE_DATA===\"undefined\")window.LANGUAGE_DATA={};window.LANGUAGE_DATA[\"{$key}\"]=" . json_encode($item, JSON_UNESCAPED_UNICODE));
        //             }
        //         }
        //         print_r("[$type] translate success\ntotal: " . count($results['key']) . "\nadd: " . count($needs) . "\n\n");
        //     }
        //     file_put_contents("translate.json", json_encode(array_values($translations), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        // } catch (Exception $e) {
        //     print_r("[$type] error, " . $e->getMessage());
        // }
    }
}
