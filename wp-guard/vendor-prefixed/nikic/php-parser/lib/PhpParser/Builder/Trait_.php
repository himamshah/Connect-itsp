<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by Philo Hermans on 21-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare(strict_types=1);

namespace Anystack\WPGuard\V001\PhpParser\Builder;

use Anystack\WPGuard\V001\PhpParser;
use Anystack\WPGuard\V001\PhpParser\BuilderHelpers;
use Anystack\WPGuard\V001\PhpParser\Node;
use Anystack\WPGuard\V001\PhpParser\Node\Stmt;

class Trait_ extends Declaration
{
    protected $name;
    protected $uses = [];
    protected $properties = [];
    protected $methods = [];

    /** @var Node\AttributeGroup[] */
    protected $attributeGroups = [];

    /**
     * Creates an interface builder.
     *
     * @param string $name Name of the interface
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * Adds a statement.
     *
     * @param Stmt|Anystack\WPGuard\V001\PhpParser\Builder $stmt The statement to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addStmt($stmt) {
        $stmt = BuilderHelpers::normalizeNode($stmt);

        if ($stmt instanceof Stmt\Property) {
            $this->properties[] = $stmt;
        } elseif ($stmt instanceof Stmt\ClassMethod) {
            $this->methods[] = $stmt;
        } elseif ($stmt instanceof Stmt\TraitUse) {
            $this->uses[] = $stmt;
        } else {
            throw new \LogicException(sprintf('Unexpected node of type "%s"', $stmt->getType()));
        }

        return $this;
    }

    /**
     * Adds an attribute group.
     *
     * @param Node\Attribute|Node\AttributeGroup $attribute
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addAttribute($attribute) {
        $this->attributeGroups[] = BuilderHelpers::normalizeAttribute($attribute);

        return $this;
    }

    /**
     * Returns the built trait node.
     *
     * @return Stmt\Trait_ The built interface node
     */
    public function getNode() : Anystack\WPGuard\V001\PhpParser\Node {
        return new Stmt\Trait_(
            $this->name, [
                'stmts' => array_merge($this->uses, $this->properties, $this->methods),
                'attrGroups' => $this->attributeGroups,
            ], $this->attributes
        );
    }
}
