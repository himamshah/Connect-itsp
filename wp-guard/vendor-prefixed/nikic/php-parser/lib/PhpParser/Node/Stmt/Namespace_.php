<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by Philo Hermans on 21-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare(strict_types=1);

namespace Anystack\WPGuard\V001\PhpParser\Node\Stmt;

use Anystack\WPGuard\V001\PhpParser\Node;

class Namespace_ extends Node\Stmt
{
    /* For use in the "kind" attribute */
    const KIND_SEMICOLON = 1;
    const KIND_BRACED = 2;

    /** @var null|Node\Name Name */
    public $name;
    /** @var Node\Stmt[] Statements */
    public $stmts;

    /**
     * Constructs a namespace node.
     *
     * @param null|Node\Name   $name       Name
     * @param null|Node\Stmt[] $stmts      Statements
     * @param array            $attributes Additional attributes
     */
    public function __construct(Node\Name $name = null, $stmts = [], array $attributes = []) {
        $this->attributes = $attributes;
        $this->name = $name;
        $this->stmts = $stmts;
    }

    public function getSubNodeNames() : array {
        return ['name', 'stmts'];
    }
    
    public function getType() : string {
        return 'Stmt_Namespace';
    }
}
