<?php

declare(strict_types=1);

namespace Mitra\React;

use React\EventLoop\LoopInterface;
use React\Socket\ServerInterface;

final class ProcessManager
{

    /**
     * @var ServerInterface
     */
    private $socket;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var int
     */
    private $maxProcesses;

    /**
     * @var array<int>
     */
    private $processes;

    /**
     * @var bool
     */
    private $running = false;

    /**
     * @var Process
     */
    private $processData;

    /**
     * @var callable|null
     */
    private $processInterruptCallable;

    public function __construct(ServerInterface $socket, LoopInterface $loop, int $maxProcesses)
    {
        $this->socket = $socket;
        $this->loop = $loop;
        $this->maxProcesses = $maxProcesses;
    }

    public function run()
    {
        if ($this->running) {
            throw new \RuntimeException('Process manager is already running');
        }

        for ($i = 1; $i <= $this->maxProcesses; $i++) {
            $this->socket->pause();

            $this->processes[] = $this->fork(function () {
                $this->socket->resume();
                // Terminate process if SIGINT received (see line 103)
                $this->loop->addSignal(SIGINT, function () {
                    if (null !== $this->processInterruptCallable) {
                        ($this->processInterruptCallable)($this->processData);
                    }

                    $this->loop->stop();
                });
                $this->loop->run();
            });
        }

        // Terminate all processes by sending an interrupt signal to them..
        $terminateProcesses = function () {
            foreach ($this->processes as $pid) {
                posix_kill($pid, SIGINT);
                $status = 0;
                pcntl_waitpid($pid, $status);
            }

            $this->loop->stop();
        };

        // Terminate child processes on various signals
        $this->loop->addSignal(SIGUSR2, $terminateProcesses);
        $this->loop->addSignal(SIGINT, $terminateProcesses);
        $this->loop->addSignal(SIGTERM, $terminateProcesses);

        $this->loop->run();

        $this->running = true;
    }

    /**
     * @return int
     */
    public function getMaxProcesses(): int
    {
        return $this->maxProcesses;
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    public function getCurrentProcess(): Process
    {
        return $this->processData;
    }

    /**
     * @param callable|null $processInterruptCallable
     */
    public function setProcessInterruptCallable(?callable $processInterruptCallable): void
    {
        $this->processInterruptCallable = $processInterruptCallable;
    }

    private function fork(callable $child)
    {
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new \RuntimeException('Cant fork a process');
        } elseif ($pid > 0) {
            return $pid;
        } else {
            posix_setsid();
            $this->processData = new Process(posix_getpid());
            $child();
            exit(0);
        }
    }
}
