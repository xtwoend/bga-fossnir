<?php

namespace App\Service;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\ServerFactory;

class Samba
{
    protected $server;
    protected $share;

    public function __construct($config = null) {
        $this->smb($config);
    }

    public function dir($dir)
    {
        return $this->share->dir($dir);
    } 

    public function list()
    {
        return $this->server->listShares();
    }

    public function rename($source, $target)
    {
        return $this->share->rename($source, $target);
    }

    public function read($filename){
        $fh = $this->share->read($filename);
        echo fread($fh, 4086);
        fclose($fh);
    }

    public function download($filename, $target){
        return $this->share->get($filename, $target);
    }

    public function server()
    {
        return $this->server;
    }

    public function smb($config){
        $username = $config['username'] ?? config('smb.username');
        $password = $config['password'] ?? config('smb.password');
        $workgroup = $config['workgroup'] ?? config('smb.workgroup');
        $host = $config['host'] ?? config('smb.host');
        $shareName = $config['sharename'] ?? config('smb.sharename');

        $serverFactory = new ServerFactory();
        $auth = new BasicAuth($username, $workgroup, $password);
        $this->server = $serverFactory->createServer($host, $auth);
        $this->share = $this->server->getShare($shareName);
        
        return $this->server;
    }
}