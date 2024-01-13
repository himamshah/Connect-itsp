<?php
/**
 * @license MIT
 *
 * Modified by Philo Hermans on 21-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace Anystack\WPGuard\V001\JsonMapper;

use Anystack\WPGuard\V001\JsonMapper\Handler\PropertyMapper;
use Anystack\WPGuard\V001\JsonMapper\Middleware\MiddlewareInterface;

class JsonMapperFactory
{
    /** @var JsonMapperBuilder */
    private $builder;

    public function __construct(JsonMapperBuilder $builder = null)
    {
        $this->builder = $builder ?? JsonMapperBuilder::new();
    }

    public function create(PropertyMapper $propertyMapper = null, MiddlewareInterface ...$handlers): JsonMapperInterface
    {
        $builder = clone ($this->builder);
        $builder->withPropertyMapper($propertyMapper ?? new PropertyMapper());
        foreach ($handlers as $handler) {
            $builder->withMiddleware($handler);
        }

        return $builder->build();
    }

    public function default(): JsonMapperInterface
    {
        $builder = clone ($this->builder);
        return $builder->withDocBlockAnnotationsMiddleware()
            ->withNamespaceResolverMiddleware()
            ->build();
    }

    public function bestFit(): JsonMapperInterface
    {
        if (PHP_VERSION_ID <= 70400) {
            return $this->default();
        }

        $builder = clone ($this->builder);
        return $builder->withDocBlockAnnotationsMiddleware()
            ->withTypedPropertiesMiddleware()
            ->withNamespaceResolverMiddleware()
            ->build();
    }
}
