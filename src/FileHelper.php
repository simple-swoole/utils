<?php

declare(strict_types=1);

namespace Simps\Utils;

class FileHelper
{
    /**
     * 检测目录并循环创建目录
     * mkdir
     * @param $catalogue
     * @return bool
     */
    public static function mkdir($catalogue)
    {
        if (!file_exists($catalogue)) {
            self::mkdir(dirname($catalogue));
            mkdir($catalogue, 0777);
        }
        return true;
    }

    /**
     * 写入日志
     *
     * @param $path
     * @param $content
     * @return bool|int
     */
    public static function writeLog($path, $content,$flags = FILE_APPEND, $context = null)
    {
        self::mkdir(dirname($path));
        return file_put_contents($path, "\r\n" . $content, $flags, $context);
    }

    /**
     * 遍历目录
     * scanDirectory
     * @param $dirPath
     * @return array
     */
    static function scanDirectory($dirPath)
    {
        if (!is_dir($dirPath))
            return [];

        $dirPath = rtrim($dirPath,'/') . '/';

        $dirs = [$dirPath];

        $fileContainer = array();
        try {
            do {
                $workDir = array_pop($dirs);
                $scanResult = scandir($workDir);
                foreach ($scanResult as $files) {
                    if ($files == '.' || $files == '..') continue;
                    $realPath = $workDir . $files;
                    if (is_dir($realPath)) {
                        array_push($dirs, $realPath . '/');
                    } elseif (is_file($realPath)) {
                        $fileContainer[] = $realPath;
                    }
                }
            } while ($dirs);
        } catch (\Throwable $throwable) {
            return [];
        }

        return $fileContainer;
    }

    /**
     * 获取文件夹大小
     * getDirSize
     * @param $dir
     * @return false|int
     */
    public static function getDirSize($dir)
    {
        $handle = opendir($dir);
        $sizeResult = 0;
        while (false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile != "." && $FolderOrFile != "..") {
                if (is_dir("$dir/$FolderOrFile")) {
                    $sizeResult += self::getDirSize("$dir/$FolderOrFile");
                } else {
                    $sizeResult += filesize("$dir/$FolderOrFile");
                }
            }
        }

        closedir($handle);
        return $sizeResult;
    }

    /**
     * 基于数组创建目录
     * createDirOrFiles
     * @param $files
     */
    public static function createDirOrFiles($files)
    {
        foreach ($files as $key => $value) {
            if (substr($value, -1) == '/') {
                mkdir($value);
            } else {
                file_put_contents($value, '');
            }
        }
    }

    /**
     * removeDirectory
     * @param       $dir
     * @param array $options
     */
    public static function removeDirectory($dir, $options = [])
    {
        if (!is_dir($dir)) {
            return;
        }
        if (!empty($options['traverseSymlinks']) || !is_link($dir)) {
            if (!($handle = opendir($dir))) {
                return;
            }
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    static::removeDirectory($path, $options);
                } else {
                    static::unlink($path);
                }
            }
            closedir($handle);
        }
        if (is_link($dir)) {
            static::unlink($dir);
        } else {
            rmdir($dir);
        }
    }

    /**
     * unlink
     * @param $path
     * @return bool
     */
    public static function unlink($path)
    {
        $isWindows = DIRECTORY_SEPARATOR === '\\';

        if (!$isWindows) {
            return unlink($path);
        }

        if (is_link($path) && is_dir($path)) {
            return rmdir($path);
        }

        try {
            return unlink($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * getMimeType
     * @param $file
     * @return mixed|null
     */
    public static function getMimeType($file)
    {
        if (extension_loaded('fileinfo')) {
            $info = finfo_open(FILEINFO_MIME_TYPE);
            if ($info) {
                $result = finfo_file($info, $file);
                finfo_close($info);

                if ($result !== false) {
                    return $result;
                }
            }
        }
        return  null;
    }
}
