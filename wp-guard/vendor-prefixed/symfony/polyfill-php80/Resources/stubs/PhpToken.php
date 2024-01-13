<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by Philo Hermans on 21-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

if (\PHP_VERSION_ID < 80000 && extension_loaded('tokenizer')) {
    class ANYSTACK_WP_GUARD_PhpToken extends Anystack\WPGuard\V001\Symfony\Polyfill\Php80\PhpToken
    {
    }
}
