<?php
/**
 * @license MIT
 *
 * Modified by Philo Hermans on 21-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace Anystack\WPGuard\V001\Saloon\Exceptions;

class InvalidMockResponseCaptureMethodException extends SaloonException
{
    public function __construct()
    {
        parent::__construct('The provided capture method is invalid. It must be a string of a request/connector class or a url.');
    }
}
