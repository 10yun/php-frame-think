<?php

function parse_phpdoc_to_markdown($php_code = '')
{


    // 正则表达式匹配文件头部的 @title 标签
    preg_match('/\/\*\*([\s\S]*?)\*\//', $php_code, $matches);

    $markdownTitle = '';
    $markdownCH = '';
    $markdownEN = '';

    // 如果文件头部包含 @title，作为一级标题
    if (preg_match('/@title\s+(.+)/', $matches[1], $titleMatch)) {
        $markdownTitle = $titleMatch[1];
        $markdownCH .= "# " . $titleMatch[1] . "\n\n";
        $markdownEN .= "# " . $titleMatch[1] . "\n\n";
    }

    // 正则表达式匹配所有函数的 PHPDoc 注释
    preg_match_all('/\/\*\*([\s\S]*?)\*\//', $php_code, $matches);

    foreach ($matches[1] as $match) {
        // 获取 @action、@param、@return 注释
        preg_match('/@action\s+(.+)/', $match, $actionMatch);
        preg_match_all('/@param\s+(\S+)\s+(\$\w+)\s+(.+)/', $match, $paramMatches);
        preg_match('/@return\s+(\S+)\s+(.+)/', $match, $returnMatch);

        $functionName = "";

        // 提取函数名
        if (preg_match('/function\s+(\w+)/', $php_code, $functionMatch)) {
            $functionName = $functionMatch[1];
        }

        // 添加函数名
        if (!empty($functionName)) {
            $markdownCH .= "### 函数: `$functionName`\n\n";
            $markdownEN .= "### Function: `$functionName`\n\n";
        }

        // 如果有 @action
        if (isset($actionMatch[1])) {
            $markdownCH .= "**说明**: " . $actionMatch[1] . "\n\n";
            $markdownEN .= "**Action**: " . $actionMatch[1] . "\n\n";
        }

        // 如果有 @param
        if (isset($paramMatches[1])) {
            $markdownCH .= "**参数**:\n";
            $markdownEN .= "**Parameters**:\n";
            foreach ($paramMatches[1] as $index => $type) {
                $markdownCH .= "- `{$paramMatches[2][$index]}` ({$type}): {$paramMatches[3][$index]}\n";
                $markdownEN .= "- `{$paramMatches[2][$index]}` ({$type}): {$paramMatches[3][$index]}\n";
            }
            $markdownCH .= "\n";
            $markdownEN .= "\n";
        }

        // 如果有 @return
        if (isset($returnMatch[1])) {
            $markdownCH .= "**返回**: `{$returnMatch[1]}` - {$returnMatch[2]}\n\n";
            $markdownEN .= "**Return**: `{$returnMatch[1]}` - {$returnMatch[2]}\n\n";
        }
    }

    return [
        'title' => $markdownTitle,
        'cn' => $markdownCH,
        'en' => $markdownEN
    ];
}
// 使用示例
$functionFileAll = glob(dirname(__DIR__) . '/src/function/*.php');
// mkdir(__DIR__ . "/function/en/", 0777, true);
// mkdir(__DIR__ . "/function/cn/", 0777, true);
foreach ($functionFileAll as $key => $val) {
    $fileName = basename($val);
    $fileName = str_replace(".php", "", $fileName);
    $filePath = $val;
    // 读取 PHP 文件内容
    $fileContent = file_get_contents($filePath);
    // 解析为 markdown 格式
    try {
        $markdown = parse_phpdoc_to_markdown($fileContent);
        // 输出到 markdown 文件
        // file_put_contents(__DIR__ . "/en_{$fileName}.md", $markdown['en']);
        file_put_contents(__DIR__ . "/cn/{$fileName}.md", $markdown['cn']);
    } catch (\Exception $e) {
        echo "问题：" . $filePath . "\n";
        break;
    }
}
