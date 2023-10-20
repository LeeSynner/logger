<?php

namespace Synsei\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;

class Logger extends AbstractLogger
{
    private string $logFile;

    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("Don't exist dir for logs");
        }
        $this->logFile = join('/', [trim($path), "log"]);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $resource = fopen($this->logFile, 'a+');

        $dateTime = $_SERVER["REQUEST_TIME"];
        $method = $_SERVER["REQUEST_METHOD"];
        $path = $_SERVER["REQUEST_URI"];
        $interpolatedMessage = $this->interpolate($message, $context);
        if ($resource) {
            fwrite($resource, "[$dateTime] [$level] [$method] [$path] $interpolatedMessage" . PHP_EOL);
            fclose($resource);
        }
    }

    private function interpolate($message, array $context = array())
    {
        $replace = array();
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }
}
