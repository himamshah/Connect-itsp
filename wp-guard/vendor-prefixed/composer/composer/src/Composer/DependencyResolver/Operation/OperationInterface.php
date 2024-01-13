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

namespace Anystack\WPGuard\V001\Composer\DependencyResolver\Operation;

/**
 * Solver operation interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface OperationInterface
{
    /**
     * Returns operation type.
     *
     * @return string
     */
    public function getOperationType();

    /**
     * Serializes the operation in a human readable format
     *
     * @param  bool   $lock Whether this is an operation on the lock file
     * @return string
     */
    public function show(bool $lock);

    /**
     * Serializes the operation in a human readable format
     *
     * @return string
     */
    public function __toString();
}
