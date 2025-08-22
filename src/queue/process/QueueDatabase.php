<?php

namespace shiyunQueue\process;

class QueueDatabase
{
    protected $processes    = 1;
    protected $socket       = 'tcp://0.0.0.0:16015';
    protected $workerName   = 'queue_mysql';

    /**
     * 为队列作业数据库表创建迁移
     */
    public function createQueueTable()
    {
        // 数据库驱动时，数据库名称-即交换机名称
        $databaseConfig = syGetConfig('shiyun.process_queue.connections.database');
        $table  = $databaseConfig['table'];
        $table  = $databaseConfig['exchange_name'];

        $className = \think\helper\Str::studly("create_{$table}_table");
        // $pdoObj = new \PDO(
        //     "mysql:host={$databaseConfig['connect_host']};port={$databaseConfig['connect_port']}",
        //     $databaseConfig['connect_user'],
        //     $databaseConfig['connect_password'],
        // );

        $sql_create = "CREATE TABLE NOT EXISTS $table
            job_queue   string 
            job_payload   longText 
            job_attempts  tinyInteger  setUnsigned
            job_reserve_time  unsignedInteger  setNullable
            job_available_time  unsignedInteger  
            job_create_time  unsignedInteger  
            index job_name
        ";
        // $pdoObj->exec($path);
        echo "队列-数据库-创建成功\n";
        /**
         * 创建 失败任务表
         */
        // 为失败的队列作业数据库表创建迁移
        $sql_create = "CREATE TABLE NOT EXISTS $table
           failed_connection text 
           failed_queue      text 
           failed_payload      longText 
           failed_exception      longText 
           failed_fail_time      timestamp  default 'CURRENT_TIMESTAMP'
        ";
        // $pdoObj->exec($sql_create);

        echo "队列-数据库-创建成功\n";
    }
}
