<?php declare(strict_types=1);
/**
 * @category     Services
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

namespace BentlerDesign\Services;

use InvalidArgumentException;
use Throwable;

class Logger
{
    /**
     * @var resource
     */
    private $handler;

    /**
     * @var string
     */
    private $hostname;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $filePath)
    {
        if (!is_writable(dirname($filePath))) {
            throw new InvalidArgumentException("Cannot write to directory containing log file: '{$filePath}'.");
        }

        $this->handler = fopen($filePath, 'ab');
        $this->hostname = gethostname();
    }

    public function log(string $message): Logger
    {
        fwrite($this->handler, date('[Y-m-d\TH:i:s.uP]') . " {$this->hostname}: {$message}" . PHP_EOL);

        return $this;
    }

    public function logException(Throwable $e): Logger
    {
        $traces = explode("\n", $e->getTraceAsString());

        $this->log(get_class($e) . "({$e->getCode()}) : {$e->getMessage()}");
        $this->log("Thrown In : {$e->getFile()}::{$e->getLine()}");

        foreach ($traces as $trace) {
            if ("" !== $trace) {
                $this->log($trace);
            }
        }

        return $this;
    }

    public function __destruct()
    {
        fclose($this->handler);
    }
}
