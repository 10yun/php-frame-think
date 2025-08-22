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
 * @method void setLimit($wsql) 设置分页
 * @method void getSelect($wsql) 获取多条
 * @method void getCount($wsql) 获取数量
 * @method void getFind($wsql) 获取单条
 *
 */
class ModelExtend extends Model
{
    // /**
    //  * @var ModelTraitParse
    //  */
    use ModelTraitsCrud, ModelTraitParse;
    // protected $connection = '';
    // protected $table = ''; // 当前模型.表单名称
    // protected $pk = '';
    /**
     * 
     */
    protected $tabConnect = '';
    protected $tableType = '';
    protected $tableName = ''; // 当前模型.除开后缀的名字
    protected $tablePrimary = ''; // 当前模型.表单主键
    protected $dbprefix = ''; // 当前模型.表单前缀
    protected $time;
    // 当前模型的【表单字段】
    protected $_tableFields = array();
    // 【字段】表单提交的数据
    protected $_fieldFormData = array();
    // 【字段】根据数据表-和表单提交的数据，处理解析数据
    protected $_fieldParseData = array();
    // 文件资源路径,指向ms下的file文件夹

    /* 数据库连接对象 */
    protected $exDb = null;
    /**
     * 
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        // $model = new static ();
        $this->exDb = $this->db();
        // 控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }
    protected function init(): void
    {
        if (!empty($this->tableName)) {
            $this->setOption('table',  $this->tableName);
        }
        if (!empty($this->tablePrimary)) {
            $this->setOption('pk',  $this->tablePrimary);
        }
        if (!empty($this->connection)) {
            $this->tabConnect = $this->connection . '.' . $this->tableName;
        } else {
            $this->tabConnect = $this->tableName;
        }
    }
    // protected function getOptions(): array
    // {
    //     $options = parent::getOptions();
    //     if (!empty($this->tableName)) {
    //         $options['table'] = $this->tableName;
    //     }
    //     if (!empty($this->tablePrimary)) {
    //         $options['pk'] = $this->tablePrimary;
    //     }
    //     return $options;
    // }

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
    public function _initialize() {}
    public function getLastId()
    {
        return $this->id;
    }
    public function orderBy($key, $sort)
    {
        return self::order($key, $sort);
    }
    public function selectArray()
    {
        return self::select()->toArray();
    }
}
