<?php

namespace shiyunQueue\drive;

use DateTimeInterface;
use think\helper\Str;
use InvalidArgumentException;

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
     * 当前 IP
     */
    protected string $msgIp = '';

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
        $this->msgIp = __cc_ip_getAddr();
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
     * 设置 payload['data'] 数据
     */
    public function setMsgData($msg = null)
    {
        $this->msgOriginalData = $msg;
        return $this;
    }
    /**
     * 设置 payload['msgDelay'] 数据
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
    /**
     * 获取 payload 最后的数据体
     */
    protected function createPayload(string|object|null $job = null)
    {
        // 随机 ID
        $randomID = Str::random(32);
        $payload = [];
        $payload['id'] = $randomID;
        $payload['attempts'] = 0;
        $payload['maxTries'] = null;
        $payload['timeout'] = null;
        $payload['exchangeName'] = $this->exchangeName;
        $payload['queueName'] = $this->queueName;
        // $payload['jobFunc'] = $this->jobFunc ?? null;
        // $payload['allowError'] = $this->allowError;
        // $payload['log'] = $this->addLog;

        $payload['msgID'] = $randomID;
        $payload['msgIp'] = $this->msgIp;
        $payload['msgCurrTime'] = $this->msgCurrTime;
        $payload['msgCurrDate'] = date('Y-m-d H:i:s', $this->msgCurrTime);
        $payload['msgDelay'] = $this->msgDelay;
        $payload['msgEncrypt'] = $this->msgEncrypt ?? false;
        $payload['data'] = $this->msgOriginalData;

        if (is_object($job)) {
            $payload =  [
                'job'       => 'shiyunQueue\drive\CallQueuedHandler@call',
                'maxTries'  => $job->tries ?? null,
                'timeout'   => $job->timeout ?? null,
                'timeoutAt' => $this->getJobExpiration($job),
                'data'      => [
                    'commandName' => get_class($job),
                    'command'     => serialize(clone $job),
                ],
            ];
        } else {
            $payload['job'] = $job;
        }
        // 是否加密
        if ($this->msgEncrypt === true) {
            $oldData = $payload['data'];
            $encryptData = \shiyunUtils\libs\LibEncryptArr::encrypt($oldData);
            $payload['data'] = $encryptData;
            // $xxxx2 = \shiyunUtils\libs\LibEncryptArr::decrypt($encryptData);
        }
        $payload = json_encode($payload);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('Unable to create payload: ' . json_last_error_msg());
        }
        return $payload;
    }
    public function getJobExpiration($job)
    {
        if (!method_exists($job, 'retryUntil') && !isset($job->timeoutAt)) {
            return;
        }
        $expiration = $job->timeoutAt ?? $job->retryUntil();
        return $expiration instanceof DateTimeInterface
            ? $expiration->getTimestamp() : $expiration;
    }

    protected function setMeta($payload, $key, $value)
    {
        $payload       = json_decode($payload, true);
        $payload[$key] = $value;
        $payload       = json_encode($payload);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('【queue】Unable to create payload: ' . json_last_error_msg());
        }
        return $payload;
    }
}
