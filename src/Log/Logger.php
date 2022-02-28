<?php

namespace OSN\Framework\Log;

use Monolog\Handler\StreamHandler;
use \Monolog\Logger as Monologger;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    protected Monologger $logger;

    public function __construct()
    {
        $this->logger = new Monologger('app');
        $this->logger->pushHandler(new StreamHandler(basepath(env('LOG_FILE'))));
    }

    /**
     * System is unusable.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function emergency(\Stringable|string $message, array $context = [])
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function alert(\Stringable|string $message, array $context = [])
    {
        $this->logger->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function critical(\Stringable|string $message, array $context = [])
    {
        $this->logger->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function error(\Stringable|string $message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function warning(\Stringable|string $message, array $context = [])
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function notice(\Stringable|string $message, array $context = [])
    {
        $this->logger->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function info(\Stringable|string $message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function debug(\Stringable|string $message, array $context = [])
    {
        $this->logger->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, \Stringable|string $message, array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }
}