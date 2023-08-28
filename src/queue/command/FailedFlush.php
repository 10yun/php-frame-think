<?php

namespace shiyunQueue\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class FailedFlush extends Command
{
    protected function configure()
    {
        $this->setName('queue:flush')
            ->setDescription('Flush all of the failed queue jobs');
    }
    // 删除所有失败的队列作业
    public function deleteData()
    {
        $this->app->get('queue_failer')->flush();
        return sendRespSucc('所有失败的作业已成功删除');
    }
    /**
     * 删除一个失败的队列作业
     * @param $id 失败作业的ID
     */
    public function deleteById($id)
    {
        if (empty($id)) {
            return sendRespError('没有与给定ID匹配的失败作业。');
        }
        if ($this->app['queue_failer']->forget($this->input->getArgument('id'))) {
            $this->output->info('Failed job deleted successfully!');
        }
    }
}
