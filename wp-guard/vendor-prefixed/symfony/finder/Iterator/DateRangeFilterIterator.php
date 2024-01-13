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

namespace Anystack\WPGuard\V001\Symfony\Component\Finder\Iterator;

use Anystack\WPGuard\V001\Symfony\Component\Finder\Comparator\DateComparator;

/**
 * DateRangeFilterIterator filters out files that are not in the given date range (last modified dates).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @extends \FilterIterator<string, \SplFileInfo>
 */
class DateRangeFilterIterator extends \FilterIterator
{
    private $comparators = [];

    /**
     * @param \Iterator<string, \SplFileInfo> $iterator
     * @param DateComparator[]                $comparators
     */
    public function __construct(\Iterator $iterator, array $comparators)
    {
        $this->comparators = $comparators;

        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function accept()
    {
        $fileinfo = $this->current();

        if (!file_exists($fileinfo->getPathname())) {
            return false;
        }

        $filedate = $fileinfo->getMTime();
        foreach ($this->comparators as $compare) {
            if (!$compare->test($filedate)) {
                return false;
            }
        }

        return true;
    }
}
