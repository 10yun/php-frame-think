<?php

declare(strict_types=1);

namespace shiyun\middleware;

use shiyun\extend\TransactionManager;
use shiyun\support\Db;
use think\facade\Log;
use think\Event;
use think\Request;

/**
 * 【自动事物】触发事务事件
 * @author 福州十云科技有限公司
 * @version 2108
 * @package shiyun\middleware
 */
class AutoTransactionMiddle
{
    protected $manager;
    public function __construct(TransactionManager $manager)
    {
        $this->manager = $manager;
    }
    public function handle($request, \Closure $next)
    {
        if (!$this->manager->shouldStartTransaction($request)) {
            return $next($request);
        }
        try {
            // 开启事务
            $this->manager->begin();
            // 执行业务逻辑
            $response = $next($request);

            // 获取状态码
            $statusCode = $response->getCode();

            // 验证成功
            // $this->manager->checkResponse($response);
            $this->manager->transactionLog("中间件状态：{$statusCode}");
            // 检查响应状态
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->manager->commit();
            } else {
                // 非成功响应，回滚（需判断是否已回滚）
                $this->manager->rollback();
            }
            return $response;
        } catch (\Throwable $e) {
            $this->manager->transactionLog("中间件异常: " . $e->getMessage());
            $this->manager->rollback();
            // 重新抛出异常
            throw $e;
        }
    }
    // public function end(\think\Response $response)
    // {
    //     if ($this->code == 200) {
    //         // 提交数据
    //         // event('dbCommit');
    //     } else {
    //         // 回滚数据
    //         // event('dbRollback');
    //     }
    // }
}
