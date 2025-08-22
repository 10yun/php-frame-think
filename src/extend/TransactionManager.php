<?php

declare(strict_types=1);

namespace shiyun\extend;

use think\facade\Db;
use think\Response;
use think\db\BaseQuery;
use Throwable;

/**
 * 事务服务类
 * @author 福州十云科技有限公司
 * @version 2108
 * @package shiyun\extend
 */
class TransactionManager
{
    // 是否 debug调试记录
    protected bool $isDebug = false;

    // 需要触发事务的HTTP方法
    protected array $allowMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    protected string $xaID = '';
    // 是否分布式
    protected bool $isDistributed = false;
    // 记录配置
    protected array $connectConfig = [];
    // 记录所有参与事务的连接
    protected array $connectNeed = [];
    // 已创建的对象
    protected array $connections = [];
    // 事务启动状态
    protected bool $stateStartActive = false;
    // 事务回滚状态
    protected bool $stateRolledBack = false;
    public function __construct()
    {
        $this->isDebug = syGetConfig('shiyun.app.transaction_debug', false);
        $this->isDistributed = syGetConfig('shiyun.app.transaction_distributed', false);
    }
    /**
     * 检查是否需要开启事务
     */
    public function shouldStartTransaction($request = null): bool
    {
        if (app()->runningInConsole()) {
            $this->transactionLog('命令行环境跳过事务');
            return false;
        }
        if ($request === null) {
            $request = app('request');
        }
        $method = strtoupper($request->method());
        // 如果是HTTP请求检查方法
        if ($request && !in_array($method, $this->allowMethods)) {
            return false;
        }
        return true;
    }
    public function getUniqueXid($connectName, string $suffix = ''): string
    {
        $config = $this->connectConfig[$connectName];
        return $config['database'] . $suffix;
        return $config['hostname'] . '_' . $config['database'] . $suffix;
    }
    /**
     * 开启事务
     */
    public function begin(): void
    {
        if ($this->stateStartActive) {
            $this->transactionLog('事务已启动，跳过重复启动');
            return;
        }
        $this->connectConfig = syGetConfig('database.connections', []);
        // 获取所有需要事务管理的连接
        $this->connectNeed = $this->getTransactionConnections();
        if ($this->isDistributed) {
            $this->xaID = uniqid('xa');
            // $this->xaID = uniqid('xa_' . date('His') . '_', true);

            $this->transactionLog('启动分布式事务');
        } else {
            $this->transactionLog('启动普通事务');
        }

        // $this->transactionLog(__CLASS__ . '---' . __FUNCTION__);
        // $this->transactionLog($this->connectNeed);

        foreach ($this->connectNeed as $key => $connName) {
            try {
                if ($connName instanceof BaseQuery) {
                    $dbObj = $connName->getConnection();
                }

                if (!empty(Db::getInstance()[$connName])) {
                    $dbObj = Db::getInstance()[$connName];
                } else {
                    $dbObj = Db::connect($connName);
                }
                // dd($dbObj);
                // $this->transactionLog($dbObj);
                // $pdo = $dbObj->getPdo();
                // if (!$pdo) {
                //     $this->transactionLog("连接 {$conn} PDO未创建");
                //     continue;
                // }
                // if ($pdo->inTransaction()) {
                //     $this->transactionLog("连接 {$conn} 已在事务中");
                //     continue;
                // }
                // 启动事务
                if ($this->isDistributed) {
                    $dbXaID = $this->getUniqueXid($connName, '_' . $this->xaID);
                    $this->transactionLog('启动分布式事务，XA ID: ' . $dbXaID);
                    $dbObj->startTransXa($dbXaID);
                } else {
                    $dbObj->startTrans();
                }
                $this->connections[$connName] = $dbObj;
                $this->transactionLog("连接 [{$connName}] 成功: ");
            } catch (Throwable $e) {
                $this->transactionLog("连接 [{$connName}] 启动事务失败: ");
                $this->transactionLog($e->getMessage());
                $this->transactionLog('--是否存在---' . is_null(Db::getInstance()[$connName]),);
                // var_dump(Db::getInstance()[$connName]->getBuilder());
                // var_dump(Db::getInstance()[$connName]->getConnection());
                // var_dump(Db::getInstance()[$connName]->getPdo());
                // var_dump(Db::getInstance()[$connName]->linkID);
                throw $e; // 直接抛出异常，让中间件 rollback()
            }
        }
        $this->stateStartActive = true;
    }
    /**
     * 提交事务
     */
    public function commit(): void
    {
        if (!$this->stateStartActive) {
            $this->transactionLog('无活跃事务，跳过提交');
            return;
        }

        if ($this->isDistributed) {
            try {
                // 先准备阶段
                foreach ($this->connections as $connName => $conn) {
                    $dbXaID = $this->getUniqueXid($connName, '_' . $this->xaID);
                    $conn->prepareXa($dbXaID);
                    $this->transactionLog("连接 [{$connName}] prepareXa 成功");
                }

                // 再提交阶段
                foreach ($this->connections as $connName => $conn) {
                    $dbXaID = $this->getUniqueXid($connName, '_' . $this->xaID);
                    $conn->commitXa($dbXaID);
                    $this->transactionLog("连接 [{$connName}] commitXa 成功");
                }
            } catch (Throwable $e) {
                $this->transactionLog("分布式事务提交失败: {$e->getMessage()}");
                $this->rollback(); // 自动触发回滚所有连接
                throw $e;
            }
        } else {
            foreach ($this->connections as $connName => $conn) {
                try {
                    $conn->commit();
                    $this->transactionLog("连接 [{$connName}] commit 成功");
                } catch (Throwable $e) {
                    $this->transactionLog("连接 [{$connName}] commit 失败: {$e->getMessage()}");
                }
            }
        }

        $this->reset();
    }

    /**
     * 回滚事务
     */
    public function rollback(): void
    {
        if (!$this->stateStartActive) {
            $this->transactionLog('跳过回滚：无活跃事务');
            return;
        }
        if ($this->stateRolledBack) {
            $this->transactionLog('跳过回滚：事务已回滚');
            return;
        }
        try {
            if ($this->isDistributed) {
                foreach ($this->connections as $connName => $conn) {
                    $dbXaID = $this->getUniqueXid($connName, '_' . $this->xaID);
                    $conn->prepareXa($dbXaID);
                    $this->transactionLog("连接 [{$connName}] 回滚 prepareXa ");
                }
                foreach ($this->connections as $connName => $conn) {
                    $conn->rollbackXa($dbXaID);
                    $this->transactionLog("连接 [{$connName}] 回滚成功");
                }
            } else {
                foreach ($this->connections as $connName => $conn) {
                    $conn->rollback();
                    $this->transactionLog("连接 [{$connName}] 回滚成功");
                }
            }
        } catch (Throwable $e) {
            $this->transactionLog("连接 [{$connName}] 回滚失败: {$dbXaID}");
            $this->transactionLog("连接 [{$connName}] 回滚失败: {$e->getMessage()}");
        }
        $this->stateRolledBack = true;
        $this->reset();
    }
    /**
     * 检查响应并自动处理事务
     */
    public function checkResponse($response): void
    {
        if (!$this->stateStartActive) {
            $this->transactionLog('无活跃事务，跳过响应检查');
            return;
        }
        if ($response instanceof Response) {
            $status = $response->getCode();
            $this->transactionLog("响应状态码: {$status}");

            if ($status >= 500) {
                $this->transactionLog('服务异常，触发回滚');
                $this->rollback();
            } elseif ($status >= 200 && $status < 300) {
                $this->transactionLog('成功响应，保持事务');
            } else {
                $this->transactionLog('请求异常（非2xx），触发回滚');
                $this->rollback();
            }
        }
    }
    /**
     * 获取需要事务管理的所有数据库连接
     */
    protected function getTransactionConnections(): array
    {
        // 默认连接
        $connections = [Db::getConfig('default')];
        // dd($connections);
        // 从配置或请求中获取其他需要事务的连接
        $extra = syGetConfig('shiyun.app.transaction_connections', []);
        if (!empty($extra)) {
            $connections = array_unique(array_merge($connections, $extra));
        }
        return $connections;
    }
    public function transactionLog($str): void
    {
        if ($this->isDebug) {
            \shiyun\libs\LibLogger::newInstance()
                ->setGroup('TransactionManager')
                ->writeDebug($str);
        }
    }
    public function isActive(): bool
    {
        return $this->stateStartActive && !$this->stateRolledBack;
    }
    protected function reset(): void
    {
        $this->connections = [];
        $this->xaID = '';
        $this->isDistributed = false;
        $this->stateStartActive = false;
        $this->stateRolledBack = false;
    }
}
