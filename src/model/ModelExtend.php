<?php

namespace shiyun\model;

use think\Model;
use think\facade\Db;

/**
 * 【ctocode】      核心文件 - 模型
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
/**
 * 对原有的 Model 验证器进行扩展
 * @author ctocode-zhw
 * @return \think\Model
 * @method void getPrimary() 获取主键
 * @method void setPrimary($newPrimary) 设置主键
 * @method void setWhere($wsql) 设置参数
 * @method void setLimit($wsql) 设置分页
 * @method void getSelect($wsql) 获取多条
 * @method void getCount($wsql) 获取数量
 * @method void getFind($wsql) 获取单条
 */
class ModelExtend extends Model
{
    use ModelTraitsCrud, ModelTraitCheck, ModelTraitParse;
    protected $table = ''; // 当前模型.表单名称
    protected $tabConnect = '';
    protected $tableType = '';
    protected $tableName = ''; // 当前模型.除开后缀的名字
    protected $tablePrimary = ''; // 当前模型.表单主键

    // 是否自动解析
    protected $_autoAnalys = false;

    /* 数据库连接对象 */
    protected $_dbObj;
    /* */
    protected $dbprefix = ''; // 当前模型.表单前缀
    protected $time;
    // 当前模型的【表单字段】
    protected $_tableFields = array();
    // 【字段】表单提交的数据
    protected $_fieldFormData = array();
    // 【字段】根据数据表-和表单提交的数据，处理解析数据
    protected $_fieldParseData = array();
    // 文件资源路径,指向ms下的file文件夹
    protected $page = 1;
    protected $pagesize = 10;

    protected $exDb = null;
    // 扩展where
    protected $whereArr = array();
    /**
     * 
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (!empty($this->tableName)) {
            $this->table = $this->tableName;
            // $this->table = $this->dbprefix . $this->tableName;
        }
        if (!empty($this->tablePrimary)) {
            $this->pk = $this->tablePrimary;
        }
        if (!empty($this->connection)) {
            $this->tabConnect = $this->connection . '.' . $this->tableName;
        } else {
            $this->tabConnect = $this->tableName;
        }
        parent::__construct($data);

        $this->page = !empty($data['page']) ? $data['page'] : 1;
        if (!empty($data['pagesize'])) {
            $this->pagesize = $data['pagesize'];
        }

        // $model = new static ();
        $this->exDb = $this->db();
        // 控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }
    // 获取主键
    public function getPrimary()
    {
        return $this->tablePrimary;
    }
    // 设置主键
    public function setPrimary($newPrimary = '')
    {
        $this->tablePrimary = $newPrimary;
        return $this;
    }
    /**
     * @action 初始化
     */
    public function _initialize()
    {
    }
    // protected function exWhere($wsql = [])
    // {
    //     if (is_array($this->whereArr) && !empty($this->whereArr)) {
    //         foreach ($this->whereArr as $key => $val) {
    //             if (!empty($wsql[$key])) {
    //                 $this->exDb->where($key, $val, $wsql[$key] ?? '');
    //             }
    //         }
    //     }
    //     return $this->exDb;
    // }
    protected function exLimit($wsql = [])
    {
        $page = !empty($wsql['page']) ? $wsql['page'] : 1;
        $page = is_numeric($page) ? intval($page) : 1;
        $pagesize = !empty($wsql['pagesize']) ? $wsql['pagesize'] : 10;
        $pagesize = $pagesize == 'all' ? $pagesize : intval($pagesize);
        $last = [
            'page' => ($page - 1) * $pagesize,
            'pagesize' => $pagesize
        ];
        $this->exDb->limit($last['page'], $last['pagesize']);
        return $this->exDb;
    }
    // 解析列表
    protected function exParseData($data = null, $type = '')
    {
        $parse_data = array();
        foreach ($data as $key => $val) {
            $parse_data[$key] = (array) $val->getData();
            ksort($parse_data[$key]);
        }
        return $parse_data;
    }
    // 分页
    protected function parseSqlLimit($wsql = [])
    {
        $page = !empty($wsql['page']) ? $wsql['page'] : 1;
        $page = is_numeric($page) ? intval($page) : 1;
        $pagesize = !empty($wsql['pagesize']) ? $wsql['pagesize'] : 10;
        $pagesize = $pagesize == 'all' ? $pagesize : intval($pagesize);
        return $pagesize == 'all' ? ' ' : ' LIMIT ' . ($page - 1) * $pagesize . ",{$pagesize} ";
    }
}
