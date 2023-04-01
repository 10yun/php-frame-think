<?php

/**
 * 【ctocode】     核心文件
 * ============================================================================
 * @author       作者         ctocode-zhw
 * @copyright    版权所有   2015-2027，并保留所有权利。
 * @copyright    版权所有   【福州十云科技有限公司】，并保留所有权利。
 * @link         网站地址   https://www.10yun.com
 * @contact      联系方式   QQ:343196936 ，QQ:240337740
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */

declare(strict_types=1);

namespace shiyun\connection;

/**
 * 批量注册 - 容器和依赖注入
 * @var array $batchPathArr
 * @version 2019-12-13
 */
class LoadAppProvider
{
    public function boot()
    {
        // $batchPathArr = glob(root_path() . '/addons/*/repositories/provider.php');
        // if (!empty($batchPathArr)) {
        //     foreach ($batchPathArr as $itemPath) {
        //         $itemData = include_once $itemPath;
        //         $tp6_def = array_merge($tp6_def, $itemData);
        //     }
        // }
    }
}
