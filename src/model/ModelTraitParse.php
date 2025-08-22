<?php

namespace shiyun\model;

trait ModelTraitParse
{
    /**
     * @action SQL语句验证函数,防注入 防CC、至强抗DDoS 等
     * @author ctocode-zhw
     * @version 2017-03-14
     */
    protected function sanitizeInput($str)
    {
        if (is_null($str)) {
            return '';
        }
        // 移除不必要的空格
        $str = trim($str);
        // 转义特殊字符（防SQL注入）
        if (function_exists('mysqli_real_escape_string') && $this->db) {
            $str = mysqli_real_escape_string($this->db, $str);
        } else {
            $str = addslashes($str); // 备用方案
        }
        return $str;
    }
    /**
     * 判断  >=
     */
    public function whereAndEgtEmpty(string $field, $value): string
    {
        return !empty($value) ? " AND {$field} >= '{$value}' " : '';
    }
    /**
     * 判断  <=
     */
    public function whereAndEltEmpty(string $field, $value): string
    {
        return !empty($value) ? " AND {$field} <= '{$value}' " : '';
    }
    /**
     * 判断 >
     */
    public function whereAndGtEmpty(string $field, $value): string
    {
        return isset($value) ? " AND {$field} > '{$value}' " : '';
    }
    /**
     * 判断 <
     */
    public function whereAndLtEmpty(string $field, $value): string
    {
        return isset($value) ? " AND {$field} < '{$value}' " : '';
    }
    /**
     * empty 追加 AND !=
     */
    public function whereAndNeEmpty(string $field, $value): string
    {
        return !empty($value) ? " AND {$field} <> '{$value}' " : '';
    }
    /**
     * 根据字段和值生成 AND LIKE 查询条件
     * 
     * @param string|array $field 字段名(可以是字符串或数组)
     * @param string|null $value 搜索值
     * @return string 生成的SQL条件语句
     */
    public function whereAndLikeEmpty(string|array $field, ?string $value): string
    {
        // 如果值为空，直接返回空字符串
        if (empty($value)) {
            return '';
        }
        // 统一对搜索值进行 XSS 过滤
        $safeValue = $this->sanitizeInput($value);
        // 处理数组字段情况
        if (is_array($field)) {
            // 过滤空字段
            $validFields = array_filter($field);
            if (empty($validFields)) {
                return '';
            }
            // 为每个字段生成LIKE条件
            $conditions = array_map(
                fn($f) => "{$f} LIKE '%{$safeValue}%'",
                $validFields
            );
            return ' AND (' . implode(' OR ', $conditions) . ')';
        }
        // 处理字符串字段情况
        return " AND {$field} LIKE '%{$safeValue}%'";
    }

    /**
     * TODO 暂时不用
     * 生成 OR 连接的等值查询条件
     * @param string $field 字段名
     * @param string|array|int $value 支持字符串、数组或数字
     *      @test string '1,2,3'
     *      @test array [1, 2, 3]
     * @return string 生成的SQL条件
     */
    public function whereAndOrEmpty(string $field, string|array|int $value): string
    {
        // 如果值为空，直接返回空字符串
        if (empty($value) && $value !== 0 && $value !== '0') {
            return '';
        }
        // 统一转为数组处理
        $values = is_array($value) ? $value : explode(',', $value);
        $values = array_filter($values); // 过滤空值
        if (empty($values)) {
            return '';
        }
        // 对每个值进行安全处理
        $conditions = array_map(
            function ($val) use ($field) {
                // 统一对搜索值进行 XSS 过滤
                $safeVal = $this->sanitizeInput($val);
                return "{$field} = '{$safeVal}'";
            },
            $values
        );
        return ' AND (' . implode(' OR ', $conditions) . ')';
    }
    /**
     * empty 追加 AND
     */
    public function whereAndEmpty(string $field, $value): string
    {
        return !empty($value) ? " AND {$field} = '{$value}' " : '';
    }
    /**
     * isset 追加 AND
     */
    public function whereAndIsset(string $field, $value): string
    {
        return isset($value) ? " AND {$field} = '{$value}' " : '';
    }
    public function whereAndInIsset(string $field, $value): string
    {
        $str_value = null;
        if (isset($value)) {
            if (is_string($value) || isset($value)) {
                $str_value = $this->whereInFromStr($value);
            }
            if (is_array($value)) {
                $str_value = $this->whereInFromArr($value);
            }
        }
        return !empty($str_value) ? " AND {$field} IN({$str_value}) " : '';
    }
    /**
     * empty 追加 AND IN
     */
    public function whereAndInEmpty(string $field, $value): string
    {
        $str_value = null;
        if (!empty($value)) {
            if (is_string($value)) {
                $str_value = $this->whereInFromStr($value);
            }
            if (is_array($value)) {
                $str_value = $this->whereInFromArr($value);
            }
        }
        return !empty($str_value) ? " AND {$field} IN({$str_value}) " : '';
    }
    public function whereAndNotInEmpty(string $field, $value): string
    {
        $str_value = null;
        if (!empty($value)) {
            if (is_string($value)) {
                $str_value = $this->whereInFromStr($value);
            }
            if (is_array($value)) {
                $str_value = $this->whereInFromArr($value);
            }
        }
        return !empty($str_value) ? " AND {$field} NOT IN ({$str_value}) " : '';
    }

    public function whereAndFindTag(string $field, $value): string
    {
        if (empty($value)) {
            return '';
        }
        $tag_sql = $this->whereFindTag($field, $value);
        return !empty($tag_sql) ? " AND $tag_sql " : '';
    }
    /**
     * tag 标签 查询条件生成
     * 
     * @param string $field 数据库字段名
     * @param string $tags 标签字符串(多个用逗号分隔)
     * @param string $operator 逻辑运算符 AND/OR
     * @return string 生成的SQL条件语句
     */
    public function whereFindTag(string $field, $tags, string $operator = 'OR')
    {
        // 验证参数
        if (empty($field) || empty($tags)) {
            //  throw new \Exception('字段名和关键词不能为空');
            return '';
        }
        $operator = strtoupper($operator);
        if (!in_array($operator, ['AND', 'OR'])) {
            $operator = 'OR';
        }
        // 处理标签字符串
        // 统一处理中英文逗号和空格
        if (is_string($tags)) {
            $tags = str_replace(['，', ' '], [',', ''], trim($tags));
            $tagArray = array_filter(array_map('trim', explode(',', $tags)));
        } else if (is_array($tags)) {
            $tagArray = $tags;
            $tagArray = array_unique($tagArray);
        }
        if (empty($tagArray)) {
            return '';
        }
        // 构建SQL条件
        $conditions = [];
        foreach ($tagArray as $tag) {
            if (!empty($tag)) {
                $conditions[] = sprintf(
                    "FIND_IN_SET('%s', REPLACE(TRIM(%s), ' ', '')) > 0",
                    addslashes($tag), // 简单防注入，建议用预处理更好
                    $field
                );
            }
        }
        return empty($conditions) ? '' : '(' . implode(" {$operator} ", $conditions) . ')';
    }
    public function whereInFromArr(array $arr = [])
    {
        if (empty($arr) || !is_array($arr)) {
            return '';
        }
        $arr = array_unique($arr);
        $output = "'" . implode("','", $arr) . "'";
        return $output;
    }
    /**
     * 字符串转引号。如：  "xxx-aaa,xxx-bbb" 变成 'xxx-aaa','xxx-bbb'
     */
    public function whereInFromStr(string $str = '')
    {
        if (empty($str)) {
            return '';
        }
        $str = trim($str);
        if (str_contains($str, ",")) {
            // 使用explode()函数按逗号分割字符串  
            $parts = explode(',', $str);
            // 使用implode()函数将分割后的数组元素用带有引号的逗号重新组合  
            $output = "'" . implode("','", $parts) . "'";
            return $output;
        }
        return '';
    }
    protected function parseSqlOrderBy(array $wsql = [])
    {
        if (!empty($wsql['orderBy'])) {
        }
    }
    // 分页
    protected function limitParse(array $wsql = [])
    {
        $page = !empty($wsql['page']) ? $wsql['page'] : 1;
        $page = is_numeric($page) ? intval($page) : 1;
        $pagesize = !empty($wsql['pagesize']) ? $wsql['pagesize'] : 10;
        $pagesize = $pagesize == 'all' ? $pagesize : intval($pagesize);
        return $pagesize == 'all' ? ' ' : ' LIMIT ' . ($page - 1) * $pagesize . ",{$pagesize} ";
    }
    // 分页所有
    protected function limitAll() {}
}
