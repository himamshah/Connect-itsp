<?php
/**
 * @license MIT
 *
 * Modified by Philo Hermans on 21-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace Anystack\WPGuard\V001\Saloon\Contracts;

interface Pipeline
{
    /**
     * Add a pipe to the pipeline
     *
     * @param callable(mixed $payload): (mixed) $callable
     * @param bool $prepend
     * @param string|null $name
     * @return $this
     * @throws \Anystack\WPGuard\V001\Saloon\Exceptions\DuplicatePipeNameException
     */
    public function pipe(callable $callable, bool $prepend = false, ?string $name = null): static;

    /**
     * Process the pipeline.
     *
     * @param mixed $payload
     * @return mixed
     */
    public function process(mixed $payload): mixed;

    /**
     * Set the pipes on the pipeline.
     *
     * @param array<\Saloon\Data\Pipe> $pipes
     * @return $this
     */
    public function setPipes(array $pipes): static;

    /**
     * Get all the pipes in the pipeline
     *
     * @return array<\Saloon\Data\Pipe>
     */
    public function getPipes(): array;
}
