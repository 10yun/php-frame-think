<?php

namespace shiyun\middleware;

class BaseRespMiddle
{
    protected $code = 0;
    public function handle($request, \Closure $next)
    {
        // frameLogs('LOGS_CHANNEL_FILE', 'BaseRespMiddle->handle');
        $response = $next($request);
        $this->code = $response->getCode();
        return $response;
    }
    public function end(\think\Response $response)
    {
        // frameLogs('LOGS_CHANNEL_FILE', '---BaseRespMiddle->end ====1 ');
        // frameLogs('LOGS_CHANNEL_FILE', $this);
        // frameLogs('LOGS_CHANNEL_FILE', '---BaseRespMiddle->end ====2 ');
        if ($this->code == 200) {
            // 提交数据
            // frameLogs('LOGS_CHANNEL_FILE', 'dbCommit');
            // frameLogs('LOGS_CHANNEL_FILE', ' ');
            event('dbCommit');
        } else {
            // 回滚数据
            // frameLogs('LOGS_CHANNEL_FILE', 'dbRollback');
            // frameLogs('LOGS_CHANNEL_FILE', ' ');
            event('dbRollback');
        }
    }
}
