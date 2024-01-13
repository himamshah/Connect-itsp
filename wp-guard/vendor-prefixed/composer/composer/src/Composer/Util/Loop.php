<?php
/**
 * @license MIT
 *
 * Modified by Philo Hermans on 21-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare(strict_types=1);

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Anystack\WPGuard\V001\Composer\Util;

use Anystack\WPGuard\V001\React\Promise\CancellablePromiseInterface;
use Anystack\WPGuard\V001\Symfony\Component\Console\Helper\ProgressBar;
use Anystack\WPGuard\V001\React\Promise\PromiseInterface;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Loop
{
    /** @var HttpDownloader */
    private $httpDownloader;
    /** @var ProcessExecutor|null */
    private $processExecutor;
    /** @var PromiseInterface[][] */
    private $currentPromises = [];
    /** @var int */
    private $waitIndex = 0;

    public function __construct(HttpDownloader $httpDownloader, ?ProcessExecutor $processExecutor = null)
    {
        $this->httpDownloader = $httpDownloader;
        $this->httpDownloader->enableAsync();

        $this->processExecutor = $processExecutor;
        if ($this->processExecutor) {
            $this->processExecutor->enableAsync();
        }
    }

    public function getHttpDownloader(): HttpDownloader
    {
        return $this->httpDownloader;
    }

    public function getProcessExecutor(): ?ProcessExecutor
    {
        return $this->processExecutor;
    }

    /**
     * @param  PromiseInterface[] $promises
     * @param  ?ProgressBar       $progress
     */
    public function wait(array $promises, ?ProgressBar $progress = null): void
    {
        /** @var \Exception|null */
        $uncaught = null;

        \Anystack\WPGuard\V001\React\Promise\all($promises)->then(
            static function (): void {
            },
            static function ($e) use (&$uncaught): void {
                $uncaught = $e;
            }
        );

        // keep track of every group of promises that is waited on, so abortJobs can
        // cancel them all, even if wait() was called within a wait()
        $waitIndex = $this->waitIndex++;
        $this->currentPromises[$waitIndex] = $promises;

        if ($progress) {
            $totalJobs = 0;
            $totalJobs += $this->httpDownloader->countActiveJobs();
            if ($this->processExecutor) {
                $totalJobs += $this->processExecutor->countActiveJobs();
            }
            $progress->start($totalJobs);
        }

        $lastUpdate = 0;
        while (true) {
            $activeJobs = 0;

            $activeJobs += $this->httpDownloader->countActiveJobs();
            if ($this->processExecutor) {
                $activeJobs += $this->processExecutor->countActiveJobs();
            }

            if ($progress && microtime(true) - $lastUpdate > 0.1) {
                $lastUpdate = microtime(true);
                $progress->setProgress($progress->getMaxSteps() - $activeJobs);
            }

            if (!$activeJobs) {
                break;
            }
        }

        // as we skip progress updates if they are too quick, make sure we do one last one here at 100%
        if ($progress) {
            $progress->finish();
        }

        unset($this->currentPromises[$waitIndex]);
        if ($uncaught) {
            throw $uncaught;
        }
    }

    public function abortJobs(): void
    {
        foreach ($this->currentPromises as $promiseGroup) {
            foreach ($promiseGroup as $promise) {
                if ($promise instanceof CancellablePromiseInterface) {
                    $promise->cancel();
                }
            }
        }
    }
}