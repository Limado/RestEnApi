<?php

namespace RestEnApi;

class ApiLogger
{
    private static $instance;
    public $enabled = true;
    private $path;

    private $fileName = array(
        'INFO' => 'renapi_access.log',
        'NOTICE' => 'renapi_access.log',
        'WARNING' => 'renapi_error.log',
        'ERROR' => 'renapi_error.log',
        'DEBUG' => 'renapi_debug.log',
        'DUMMY' => 'renapi_dummy.log',
    );

    private $fileNamePrefix;
    private $owner = 'apache';
    private $group = 'apache';
    private $mode = 664;
    private $level;
    private $title = ' RestENApi ';
    private $sessionId;

    private $fileHandler;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public function write($message, $level = '')
    {
        if ($this->enabled) {
            $level = ($level) ? $level : $this->getLevel();
            $level = ($level) ? $level : 'INFO';
            $level = strtoupper($level);

            if (!array_key_exists($level, $this->fileName)) {
                return false;
            }

            $date = date('Y-m-d H:i:s');
            $sessionId = ($this->getSessionId()) ? ' ' . $this->getSessionId() . ' ' : null;
            $body = "[{$date}]{$sessionId}{$this->getTitle()} {$level} - \"{$message}\"\n";
            $fileName = $this->getFileNamePrefix() . $this->fileName[$level];

            $this->fileHandler = fopen($this->getPath() . $fileName, 'a');
            fwrite($this->fileHandler, $body);
            fclose($this->fileHandler);
        }

        return true;
    }

    public function setFileNamePrefix($fileNamePrefix, $append = false)
    {
        if ($append) {
            $this->fileNamePrefix .= $fileNamePrefix . '_';
        } else {
            $this->fileNamePrefix = $fileNamePrefix . '_';
        }
    }

    public function getFileNamePrefix()
    {
        return $this->fileNamePrefix;
    }

    public function setLevel($level)
    {
        $this->level = strtoupper($level);
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    private function setFileAttributes($fileName)
    {
        chown($this->getPath() . $fileName, $this->owner);
        chgrp($this->getPath() . $fileName, $this->group);
        chmod($this->getPath() . $fileName, $this->mode);
    }
}

class Tools
{
/**
 * En caso que el servidor no sea apache o no reconozca la funcion
 */
    public static function getallheaders()
    {
        $contenTypes = ["content-type", "content_type", "contenttype"];
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
            if (in_array(strtolower($name), $contenTypes)) {
                $headers["Content-Type"] = $value;
            }
            if (strtolower($name) == "authentication") {
                $headers["Authentication"] = $value;
            }
        }
        return $headers;
    }
}
