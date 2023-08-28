<?php

namespace shiyun\extend;

use think\Event;
use shiyun\support\Db;

class DbAutoTransaction
{
    private $start = false;
    protected $fen_bu_shi = false;
    protected $xaID = '';
    public function __construct()
    {
        $methods = request()->method() ?? '';
        if (in_array($methods, [
            'POST', 'DELETE', 'PUT', 'PATCH'
        ])) {
            if ($this->fen_bu_shi) {
                $this->xaID = uniqid("");
                // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->__construct--- 分布式' . $this->xaID);
            }
            $this->onTaskStart();
        }
    }
    public function onTaskStart()
    {
        try {
            if (!$this->start) {
                $this->start = true;
                // 分布式事务
                if ($this->fen_bu_shi) {
                    // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->dbStartTask--- 分布式');
                    // transactionXa
                    Db::startTransXa($this->xaID);
                } else {
                    // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->dbStartTask--- 单一');
                    Db::startTrans();
                }
            }
        } catch (\Exception $th) {
            //throw $th;
            // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->dbStartTask--- 错误' . $th->getMessage());
            // var_dump($h);
            // dd('---');
        }
    }
    public function onTaskSuccess()
    {
        try {
            if ($this->start) {
                // 分布式事务
                if ($this->fen_bu_shi) {
                    // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->dbCommit--- 分布式');
                    Db::prepareXa($this->xaID);
                    Db::commitXa($this->xaID);
                } else {
                    // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->dbCommit--- 单一');
                    Db::commit();
                }
            }
        } catch (\Exception $th) {
            //throw $th;
            // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->dbCommit---' . $th->getMessage());
            // var_dump($h);t
            // dd('---');
        }
    }
    public function onTaskFail()
    {
        try {
            if ($this->start) {
                // 分布式事务
                if ($this->fen_bu_shi) {
                    // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->dbRollback--- 分布式');
                    Db::rollbackXa($this->xaID);
                } else {
                    // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->dbRollback--- 单一');
                    Db::rollback();
                }
            }
        } catch (\Exception $th) {
            //throw $th;
            // frameLogs('LOGS_CHANNEL_FILE', '---DbAutoTransaction->dbRollback---' . $th->getMessage());
            // var_dump($h);t
            // dd('---');
        }
    }
    public function subscribe(Event $event)
    {
        $event->listen('dbStartTask', [$this, 'onTaskStart']);
        $event->listen('dbCommit', [$this, 'onTaskSuccess']);
        $event->listen('dbRollback', [$this, 'onTaskFail']);
    }
}
