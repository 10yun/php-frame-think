<?php

namespace shiyun\command\make;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class MakeController extends Command
{
    use TraitCommon;
    protected function configure()
    {
        $this->setName('MakeController')
            ->addOption('dir', 'd', Option::VALUE_OPTIONAL, '模块')
            ->addOption('role', 'r', Option::VALUE_OPTIONAL, '角色')
            ->addOption('name', 'c', Option::VALUE_OPTIONAL, '控制器')
            ->setDescription('Say Hello');
    }

    protected function execute(Input $input, Output $output)
    {
        $dir = $input->getOption('dir') ?? '_channel/module';
        $roleInput = $input->getOption('role') ?? '';
        $nameInput = $input->getOption('name') ?? 'common/Xxx';
        if (!str_contains($dir, "/",)) {
            $this->output->writeln('<info> Directory does not exist “/” !</info>');
            return false;
        }
        $className = $this->getClassName($nameInput);
        // 命名空间
        $namespaceArr1 = $this->getNamespaceController($dir, $nameInput, $roleInput);
        $namespaceClass = trim(implode('\\', $namespaceArr1));
        /**
         * flag + restful
         */
        // echo $this->getAnnoFlag($dir, $nameInput, $roleInput);
        // echo '<br>';
        // echo $this->getAnnoRestful($dir, $nameInput, $roleInput);
        // echo '<br>';
        // 
        $namespaceArr2 = $this->getNamespaceModel($dir, $nameInput);
        $namespaceModel = trim(implode('\\', $namespaceArr2));
        //
        $namespaceArr3 = $this->getNamespaceValidate($dir, $nameInput);
        $namespaceValidate = trim(implode('\\', $namespaceArr3));
        // var_dump($namespaceValidate, $namespaceModel);
        // return false;
        // 创建目录
        $fileDir = _PATH_PROJECT_ . implode("/", $namespaceArr1);
        if (!is_dir($fileDir)) {
            @mkdir($fileDir, 0755, true);
        }
        // 获取模版内容
        $stubContent = file_get_contents(__DIR__ . '/stubs/controller.stub');
        // 解析模版内容
        $putContent = str_replace(['{%namespaceClass%}', '{%namespaceModel%}', '{%namespaceValidate%}', '{%className%}',], [
            $namespaceClass,
            $namespaceModel,
            $namespaceValidate,
            $className,
        ], $stubContent);
        // 写入模版内容
        $filePath = "{$fileDir}/$className.php";
        file_put_contents($filePath, $putContent);

        $output->writeln(' 写入成功 ' . $filePath);
    }
}
