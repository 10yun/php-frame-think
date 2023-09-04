<?php

namespace shiyunQueue\command;

use think\console\Command;
use think\console\Table;
use think\helper\Arr;

class FailedShow extends Command
{
    /**
     * The table headers for the command.
     */
    protected array $headers = ['ID', 'Connection', 'Queue', 'Class', 'Fail Time'];

    protected function configure()
    {
        $this->setName('queue:failed')
            ->setDescription('List all of the failed queue jobs');
    }
    public function handle()
    {
        if (count($jobs = $this->getFailedJobs()) === 0) {
            $this->output->info('No failed jobs!');
            return;
        }
        /**
         * 在控制台中显示失败的作业。
         */
        $table = new Table();
        $table->setHeader($this->headers);
        $table->setRows($jobs);
        $this->table($table);
    }
    /**
     * 将失败的作业编译为可显示的格式。
     * Compile the failed jobs into a displayable format.
     *
     * @return array
     */
    protected function getFailedJobs()
    {
        $config = $this->app->config->get('queue.failed', []);
        $type = \think\helper\Arr::pull($config, 'type', 'none');
        $queueFailerObj = $this->app->invokeClass("\shiyunQueue\drive\{$type}Failed::class", [$config]);

        $failed = $queueFailerObj->all();
        return collect($failed)->map(function ($failed) {
            return $this->parseFailedJob((array) $failed);
        })->filter()->all();
    }

    /**
     * 解析失败的作业行。
     * Parse the failed job row.
     *
     * @param array $failed
     * @return array
     */
    protected function parseFailedJob(array $failed)
    {
        $row = array_values(Arr::except($failed, ['payload', 'exception']));
        array_splice($row, 3, 0, $this->extractJobName($failed['payload']));
        return $row;
    }

    /**
     * 从有效负载中提取失败的作业名称。
     * Extract the failed job name from payload.
     * @param string $payload
     * @return string|null
     */
    private function extractJobName($payload)
    {
        $payload = json_decode($payload, true);
        if ($payload && (!isset($payload['data']['command']))) {
            return $payload['job'] ?? null;
        } elseif ($payload && isset($payload['data']['command'])) {
            return $this->matchJobName($payload);
        }
    }
    /**
     * 匹配有效负载中的作业名称。
     * Match the job name from the payload.
     * @param array $payload
     * @return string
     */
    protected function matchJobName($payload)
    {
        preg_match('/"([^"]+)"/', $payload['data']['command'], $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return $payload['job'] ?? null;
    }
}
