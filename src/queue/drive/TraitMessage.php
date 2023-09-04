<?php

namespace shiyunQueue\drive;

/**
 * @method $this addMessage(...$data) 执行数据
 * @method $this setMsgDelay(int $delay) 延迟执行秒数
 * @method $this setMsgEncrypt(boolean $flag) 是否加密
 */
trait TraitMessage
{
    /**
     * 数据延迟,延迟执行秒数,延迟时间
     * 在n秒后执行
     */
    protected int $msgDelay = 0;
    // 消息实际时间
    protected int $msgActualTime = 0;
    /**
     * 执行时间
     */
    protected int $msgExecuteTime = 0;
    /**
     * 当前时间
     */
    protected int $msgCurrTime = 0;
    /**
     * 是否加密
     */
    protected bool $msgEncrypt = false;
    // 数据
    protected array|string $msgData;
    // 原有数据
    protected array|string $msgOriginalData;

    public function initMsgSett()
    {
        $now = time();
        $this->msgCurrTime = $now;
    }
    public function clearMsgSett()
    {
        $this->msgEncrypt = false;
        $this->msgData = [];
        $this->msgOriginalData = [];
        $this->msgDelay = 0;
    }
    /**
     * 追加数据
     */
    public function addMessage($msg = null)
    {
        if (empty($msg)) {
            $msg = '';
        }
        // if (is_array($msg)) {
        //     $this->msgOriginalData = array_merge($this->msgOriginalData, $msg);
        // } else if (is_string($msg)) {
        //     $this->msgOriginalData[] = $msg;
        // }
        if (is_string($msg)) {
            $this->msgOriginalData = $msg ?? '';
        } else if (is_array($msg)) {
            if (!empty($this->msgOriginalData)) {
                $this->msgOriginalData = array_merge($this->msgOriginalData, $msg);
            } else {
                $this->msgOriginalData = $msg;
            }
        }
        return $this;
    }
    /**
     * 设置数据
     */
    public function setMessage($msg = null)
    {
        $this->msgOriginalData = $msg;
        return $this;
    }
    public function getMessage()
    {
        $msgLast = [];
        $msgLast['msgEncrypt'] = $this->msgEncrypt ?? false;
        $msgLast['msgID'] = rand();
        $msgLast['msgCurrTime'] = $this->msgCurrTime;
        $msgLast['msgCurrDate'] = date('Y-m-d H:i:s', $this->msgCurrTime);
        $msgLast['msgDelay'] = $this->msgDelay;
        $msgLast['attempts'] = 0;
        $msgLast['queueName'] = $this->queueName;
        $msgLast['data'] = $this->msgOriginalData;
        // $msgLast['jobFunc'] = $this->jobFunc ?? null;
        // $msgLast['allowError'] = $this->allowError;
        // $msgLast['log'] = $this->addLog;

        // 是否加密
        if ($this->msgEncrypt === true) {
            $oldData = $msgLast['data'];
            $encryptData = \shiyunUtils\libs\LibEncryptArr::encrypt($oldData);
            $msgLast['data'] = $encryptData;
            // $xxxx2 = \shiyunUtils\libs\LibEncryptArr::decrypt($encryptData);
        }
        return $msgLast;
    }
    /**
     * 设置延迟
     * 设置延迟时间
     * @param int $delay 延迟/秒
     * @return $this
     */
    public function setMsgDelay(int $delay = 0)
    {
        $this->msgDelay = $delay;
        $this->msgActualTime = $this->msgCurrTime + $this->msgDelay;
        return $this;
    }
    /**
     * 数据加密
     */
    public function setMsgEncrypt(bool $flag = false)
    {
        $this->msgEncrypt = $flag;
        return $this;
    }
}
