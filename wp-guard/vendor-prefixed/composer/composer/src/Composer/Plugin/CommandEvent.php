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

namespace Anystack\WPGuard\V001\Composer\Plugin;

use Anystack\WPGuard\V001\Composer\EventDispatcher\Event;
use Anystack\WPGuard\V001\Symfony\Component\Console\Input\InputInterface;
use Anystack\WPGuard\V001\Symfony\Component\Console\Output\OutputInterface;

/**
 * An event for all commands.
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
class CommandEvent extends Event
{
    /**
     * @var string
     */
    private $commandName;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Constructor.
     *
     * @param string          $name        The event name
     * @param string          $commandName The command name
     * @param mixed[]         $args        Arguments passed by the user
     * @param mixed[]         $flags       Optional flags to pass data not as argument
     */
    public function __construct(string $name, string $commandName, InputInterface $input, OutputInterface $output, array $args = [], array $flags = [])
    {
        parent::__construct($name, $args, $flags);
        $this->commandName = $commandName;
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Returns the command input interface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * Retrieves the command output interface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * Retrieves the name of the command being run
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }
}
