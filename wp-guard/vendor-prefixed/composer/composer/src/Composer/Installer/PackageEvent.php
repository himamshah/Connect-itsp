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

namespace Anystack\WPGuard\V001\Composer\Installer;

use Anystack\WPGuard\V001\Composer\Composer;
use Anystack\WPGuard\V001\Composer\IO\IOInterface;
use Anystack\WPGuard\V001\Composer\DependencyResolver\Operation\OperationInterface;
use Anystack\WPGuard\V001\Composer\Repository\RepositoryInterface;
use Anystack\WPGuard\V001\Composer\EventDispatcher\Event;

/**
 * The Package Event.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class PackageEvent extends Event
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var bool
     */
    private $devMode;

    /**
     * @var RepositoryInterface
     */
    private $localRepo;

    /**
     * @var OperationInterface[]
     */
    private $operations;

    /**
     * @var OperationInterface The operation instance which is being executed
     */
    private $operation;

    /**
     * Constructor.
     *
     * @param OperationInterface[] $operations
     */
    public function __construct(string $eventName, Composer $composer, IOInterface $io, bool $devMode, RepositoryInterface $localRepo, array $operations, OperationInterface $operation)
    {
        parent::__construct($eventName);

        $this->composer = $composer;
        $this->io = $io;
        $this->devMode = $devMode;
        $this->localRepo = $localRepo;
        $this->operations = $operations;
        $this->operation = $operation;
    }

    public function getComposer(): Composer
    {
        return $this->composer;
    }

    public function getIO(): IOInterface
    {
        return $this->io;
    }

    public function isDevMode(): bool
    {
        return $this->devMode;
    }

    public function getLocalRepo(): RepositoryInterface
    {
        return $this->localRepo;
    }

    /**
     * @return OperationInterface[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * Returns the package instance.
     */
    public function getOperation(): OperationInterface
    {
        return $this->operation;
    }
}
