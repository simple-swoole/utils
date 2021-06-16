<?php

declare(strict_types=1);
/**
 * This file is part of Simps.
 *
 * @link     https://simps.io
 * @document https://doc.simps.io
 * @license  https://github.com/simple-swoole/simps/blob/master/LICENSE
 */
namespace Simps\Utils;

use Swoole\Process;
use Swoole\Server;
use Swoole\Timer;

/**
 * 'process'   => [
 *      [\Simps\Utils\AutoReload::class, 'start'],
 * ],
 * Class AutoReload.
 */
class AutoReload
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Process
     */
    protected $hotReloadProcess;

    /**
     * 文件类型.
     * @var array
     */
    protected $reloadFileTypes = ['.php' => true];

    /**
     * 监听文件.
     * @var array
     */
    protected $lastFileList = [];

    /**
     * 是否正在重载.
     * @var bool
     */
    protected $reloading = false;

    /**
     * AutoReload constructor.
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * start.
     * @return Process
     */
    public static function start(Server $server)
    {
        $autoLoad = new self($server);

        $autoLoad->hotReloadProcess = new Process([$autoLoad, 'hotReloadProcessCallBack'], false, 2, false);

        return $autoLoad->hotReloadProcess;
    }

    /**
     * hotReloadProcessCallBack.
     */
    public function hotReloadProcessCallBack(Process $worker)
    {
        $this->hotReloadProcess->signal(SIGUSR1, [$this, 'signalCallBack']);

        $this->run(BASE_PATH);

        $currentOS = PHP_OS;

        $currentPID = $this->hotReloadProcess->pid;

        echoSuccess("自动重载代码初始化({$currentOS})PID: {$currentPID} ...");
    }

    public function signalCallBack()
    {
        echoSuccess('重载时间: ' . date('Y-m-d H:i:s'));

        $res = $this->server->reload();

        $res ? echoSuccess('重载成功') : echoError('重载失败');
    }

    /**
     * 添加文件类型
     * addFileType.
     * @param $type
     * @return $this
     */
    public function addFileType($type)
    {
        $type = trim($type, '.');

        $this->reloadFileTypes['.' . $type] = true;

        return $this;
    }

    /**
     * watch.
     * @param $dir
     */
    public function watch($dir)
    {
        $files = FileHelper::scanDirectory($dir);

        $dirtyList = [];

        foreach ($files as $file) {
            //检测文件类型
            $fileType = strrchr($file, '.');
            if (isset($this->reloadFileTypes[$fileType])) {
                $fileInfo = new \SplFileInfo($file);
                $mtime = $fileInfo->getMTime();
                $inode = $fileInfo->getInode();
                $dirtyList[$inode] = $mtime;
            }
        }

        // 当数组中出现脏值则发生了文件变更
        if (array_diff_assoc($dirtyList, $this->lastFileList)) {
            $this->lastFileList = $dirtyList;
            if ($this->reloading) {
                $this->sendReloadSignal();
            }
        }

        $this->reloading = true;
    }

    /**
     * run.
     * @param mixed $dir
     */
    public function run($dir)
    {
        $this->watch($dir);

        Timer::tick(1000, function () use ($dir) {
            $this->watch($dir);
        });
    }

    /**
     * sendReloadSignal.
     */
    protected function sendReloadSignal()
    {
        Process::kill($this->hotReloadProcess->pid, SIGUSR1);
    }
}
