<?php

namespace shiyun\middleware;

class BaseRespMiddle
{
    protected $code = 0;
    public function handle($request, \Closure $next)
    {
        // frameLogsFile('BaseRespMiddle->handle');
        $response = $next($request);
        $this->code = $response->getCode();
        return $response;
    }
    public function end(\think\Response $response)
    {
        // frameLogsFile('---BaseRespMiddle->end ====1 ');
        // frameLogsFile($this);
        // frameLogsFile('---BaseRespMiddle->end ====2 ');
        if ($this->code == 200) {
            // 提交数据
            // frameLogsFile('dbCommit');
            // frameLogsFile(' ');
            event('dbCommit');
        } else {
            // 回滚数据
            // frameLogsFile('dbRollback');
            // frameLogsFile(' ');
            event('dbRollback');
        }
    }
}
