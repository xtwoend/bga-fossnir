<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Service;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\Options;
use Icewind\SMB\ServerFactory;
use function Hyperf\Config\config;

class Samba
{
    protected $server;

    protected $share;

    public function __construct($config = null)
    {
        $this->smb($config);
    }

    public function dir($dir)
    {
        return $this->share->dir($dir);
    }

    public function mkdir($path)
    {
        return $this->share->mkdir($path);
    }

    public function list()
    {
        return $this->server->listShares();
    }

    public function rename($source, $target)
    {
        return $this->share->rename($source, $target);
    }

    public function read($filename)
    {
        $fh = $this->share->read($filename);
        echo fread($fh, 4086);
        fclose($fh);
    }

    public function download($filename, $target)
    {
        return $this->share->get($filename, $target);
    }

    /**
     * Get files list in folder.
     *
     * @param string $path Folder path
     * @param string $sort Sort method (mtime, size or name)
     * @param string $order Order (asc or desc)
     *
     * @return IFileInfo[] Folder content
     *
     * @throws CoreException
     */
    public function getFiles($path = '/', $sort = 'mtime', $order = 'desc')
    {
        $entries = $this->getEntries($path, $sort, $order);
        return array_filter($entries, [$this, 'filterByFile']);
    }

    /**
     * Get folder content.
     *
     * @param string $path Folder path
     * @param string $sort Sort method (mtime, size or name)
     * @param string $order Order (asc or desc)
     *
     * @return null|IFileInfo[] Folder content
     *
     * @throws CoreException
     */
    public function getEntries($path = '/', $sort = 'mtime', $order = 'desc')
    {
        $entries = $this->dir($path);

        if ($entries !== null) {
            usort($entries, [$this, 'compareByTime']);
            if ($order !== 'desc') {
                $entries = array_reverse($entries);
            }
        }
        return $entries;
    }

    /**
     * get only latest file.
     * @param mixed $dir
     * @param mixed $date
     */
    public function getLatestFile($dir, $date)
    {
        $files = $this->getEntries($dir);
        $filters = [];
        foreach ($files as $file) {
            $filters[] = $file;
            if ($file->getMTime() < $date) {
                break;
            }
        }
        return $filters;
    }

    public function server()
    {
        return $this->server;
    }

    public function smb($config)
    {
        $username = $config['username'] ?? config('smb.username');
        $password = $config['password'] ?? config('smb.password');
        $workgroup = $config['workgroup'] ?? config('smb.workgroup');
        $host = $config['host'] ?? config('smb.host');
        $shareName = $config['sharename'] ?? config('smb.sharename');
        $timeout = $config['timeout'] ?? config('smb.timeout');
        $minProtocol = $config['smb_version_min'] ?? config('smb.smb_version_min');
        $maxProtocol = $config['smb_version_max'] ?? config('smb.smb_version_max');

        $option = new Options();
        $option->setTimeout($timeout);
        $option->setMinProtocol($minProtocol);
        $option->setMaxProtocol($maxProtocol);

        $serverFactory = new ServerFactory($option);
        $auth = new BasicAuth($username, $workgroup, $password);
        $this->server = $serverFactory->createServer($host, $auth);
        $this->share = $this->server->getShare($shareName);

        return $this->server;
    }

    /**
     * Test if item is a file.
     *
     * @param IFileInfo $currentItem Item to test
     *
     * @return true if item if file
     */
    private function filterByFile($currentItem)
    {
        return $currentItem->isDirectory() === false;
    }

    /**
     * Compare by time for sort list.
     *
     * @param IFileInfo $firstFile Data of the first file
     * @param IFileInfo $secondFile Data of the second file
     *
     * @return int Sort information
     */
    private function compareByTime($firstFile, $secondFile)
    {
        if ($firstFile->getMTime() === $secondFile->getMTime()) {
            return 0;
        }
        return ($firstFile->getMTime() < $secondFile->getMTime()) ? -1 : 1;
    }
}
