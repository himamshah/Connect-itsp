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

namespace Anystack\WPGuard\V001\Composer\Advisory;

use Anystack\WPGuard\V001\Composer\Semver\Constraint\ConstraintInterface;
use Anystack\WPGuard\V001\Composer\Semver\VersionParser;
use JsonSerializable;

class PartialSecurityAdvisory implements JsonSerializable
{
    /**
     * @var string
     * @readonly
     */
    public $advisoryId;

    /**
     * @var string
     * @readonly
     */
    public $packageName;

    /**
     * @var ConstraintInterface
     * @readonly
     */
    public $affectedVersions;

    /**
     * @param array<mixed> $data
     * @return SecurityAdvisory|PartialSecurityAdvisory
     */
    public static function create(string $packageName, array $data, VersionParser $parser): self
    {
        $constraint = $parser->parseConstraints($data['affectedVersions']);
        if (isset($data['title'], $data['sources'], $data['reportedAt'])) {
            return new SecurityAdvisory($packageName, $data['advisoryId'], $constraint, $data['title'], $data['sources'], new \DateTimeImmutable($data['reportedAt'], new \DateTimeZone('UTC')), $data['cve'] ?? null, $data['link'] ?? null);
        }

        return new self($packageName, $data['advisoryId'], $constraint);
    }

    public function __construct(string $packageName, string $advisoryId, ConstraintInterface $affectedVersions)
    {
        $this->advisoryId = $advisoryId;
        $this->packageName = $packageName;
        $this->affectedVersions = $affectedVersions;
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = (array) $this;
        $data['affectedVersions'] = $data['affectedVersions']->getPrettyString();

        return $data;
    }
}
