<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by Philo Hermans on 21-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare(strict_types=1);

namespace Anystack\WPGuard\V001\PhpParser\Node;

use Anystack\WPGuard\V001\PhpParser\NodeAbstract;

class IntersectionType extends ComplexType
{
    /** @var (Identifier|Name)[] Types */
    public $types;

    /**
     * Constructs an intersection type.
     *
     * @param (Identifier|Name)[] $types      Types
     * @param array               $attributes Additional attributes
     */
    public function __construct(array $types, array $attributes = []) {
        $this->attributes = $attributes;
        $this->types = $types;
    }

    public function getSubNodeNames() : array {
        return ['types'];
    }

    public function getType() : string {
        return 'IntersectionType';
    }
}
