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

namespace Anystack\WPGuard\V001\Composer\Repository;

use Anystack\WPGuard\V001\Composer\Factory;
use Anystack\WPGuard\V001\Composer\IO\IOInterface;
use Anystack\WPGuard\V001\Composer\Config;
use Anystack\WPGuard\V001\Composer\EventDispatcher\EventDispatcher;
use Anystack\WPGuard\V001\Composer\Pcre\Preg;
use Anystack\WPGuard\V001\Composer\Util\HttpDownloader;
use Anystack\WPGuard\V001\Composer\Util\ProcessExecutor;
use Anystack\WPGuard\V001\Composer\Json\JsonFile;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class RepositoryFactory
{
    /**
     * @return array|mixed
     */
    public static function configFromString(IOInterface $io, Config $config, string $repository, bool $allowFilesystem = false)
    {
        if (0 === strpos($repository, 'http')) {
            $repoConfig = ['type' => 'composer', 'url' => $repository];
        } elseif ("json" === pathinfo($repository, PATHINFO_EXTENSION)) {
            $json = new JsonFile($repository, Factory::createHttpDownloader($io, $config));
            $data = $json->read();
            if (!empty($data['packages']) || !empty($data['includes']) || !empty($data['provider-includes'])) {
                $repoConfig = ['type' => 'composer', 'url' => 'file://' . strtr(realpath($repository), '\\', '/')];
            } elseif ($allowFilesystem) {
                $repoConfig = ['type' => 'filesystem', 'json' => $json];
            } else {
                throw new \InvalidArgumentException("Invalid repository URL ($repository) given. This file does not contain a valid composer repository.");
            }
        } elseif (strpos($repository, '{') === 0) {
            // assume it is a json object that makes a repo config
            $repoConfig = JsonFile::parseJson($repository);
        } else {
            throw new \InvalidArgumentException("Invalid repository url ($repository) given. Has to be a .json file, an http url or a JSON object.");
        }

        return $repoConfig;
    }

    public static function fromString(IOInterface $io, Config $config, string $repository, bool $allowFilesystem = false, ?RepositoryManager $rm = null): RepositoryInterface
    {
        $repoConfig = static::configFromString($io, $config, $repository, $allowFilesystem);

        return static::createRepo($io, $config, $repoConfig, $rm);
    }

    /**
     * @param  array<string, mixed> $repoConfig
     */
    public static function createRepo(IOInterface $io, Config $config, array $repoConfig, ?RepositoryManager $rm = null): RepositoryInterface
    {
        if (!$rm) {
            @trigger_error('Not passing a repository manager when calling createRepo is deprecated since Composer 2.3.6', E_USER_DEPRECATED);
            $rm = static::manager($io, $config);
        }
        $repos = self::createRepos($rm, [$repoConfig]);

        return reset($repos);
    }

    /**
     * @return RepositoryInterface[]
     */
    public static function defaultRepos(?IOInterface $io = null, ?Config $config = null, ?RepositoryManager $rm = null): array
    {
        if (null === $rm) {
            @trigger_error('Not passing a repository manager when calling defaultRepos is deprecated since Composer 2.3.6, use defaultReposWithDefaultManager() instead if you cannot get a manager.', E_USER_DEPRECATED);
        }

        if (null === $config) {
            $config = Factory::createConfig($io);
        }
        if (null !== $io) {
            $io->loadConfiguration($config);
        }
        if (null === $rm) {
            if (null === $io) {
                throw new \InvalidArgumentException('This function requires either an IOInterface or a RepositoryManager');
            }
            $rm = static::manager($io, $config, Factory::createHttpDownloader($io, $config));
        }

        return self::createRepos($rm, $config->getRepositories());
    }

    /**
     * @param  EventDispatcher   $eventDispatcher
     * @param  HttpDownloader    $httpDownloader
     */
    public static function manager(IOInterface $io, Config $config, ?HttpDownloader $httpDownloader = null, ?EventDispatcher $eventDispatcher = null, ?ProcessExecutor $process = null): RepositoryManager
    {
        if ($httpDownloader === null) {
            $httpDownloader = Factory::createHttpDownloader($io, $config);
        }
        if ($process === null) {
            $process = new ProcessExecutor($io);
            $process->enableAsync();
        }

        $rm = new RepositoryManager($io, $config, $httpDownloader, $eventDispatcher, $process);
        $rm->setRepositoryClass('composer', 'Anystack\WPGuard\V001\Composer\Repository\ComposerRepository');
        $rm->setRepositoryClass('vcs', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('package', 'Anystack\WPGuard\V001\Composer\Repository\PackageRepository');
        $rm->setRepositoryClass('pear', 'Anystack\WPGuard\V001\Composer\Repository\PearRepository');
        $rm->setRepositoryClass('git', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('bitbucket', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('git-bitbucket', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('github', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('gitlab', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('svn', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('fossil', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('perforce', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('hg', 'Anystack\WPGuard\V001\Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('artifact', 'Anystack\WPGuard\V001\Composer\Repository\ArtifactRepository');
        $rm->setRepositoryClass('path', 'Anystack\WPGuard\V001\Composer\Repository\PathRepository');

        return $rm;
    }

    /**
     * @return RepositoryInterface[]
     */
    public static function defaultReposWithDefaultManager(IOInterface $io): array
    {
        $manager = RepositoryFactory::manager($io, $config = Factory::createConfig($io));
        $io->loadConfiguration($config);

        return RepositoryFactory::defaultRepos($io, $config, $manager);
    }

    /**
     * @param array<int|string, mixed> $repoConfigs
     *
     * @return RepositoryInterface[]
     */
    private static function createRepos(RepositoryManager $rm, array $repoConfigs): array
    {
        $repos = [];

        foreach ($repoConfigs as $index => $repo) {
            if (is_string($repo)) {
                throw new \UnexpectedValueException('"repositories" should be an array of repository definitions, only a single repository was given');
            }
            if (!is_array($repo)) {
                throw new \UnexpectedValueException('Repository "'.$index.'" ('.json_encode($repo).') should be an array, '.gettype($repo).' given');
            }
            if (!isset($repo['type'])) {
                throw new \UnexpectedValueException('Repository "'.$index.'" ('.json_encode($repo).') must have a type defined');
            }

            $name = self::generateRepositoryName($index, $repo, $repos);
            if ($repo['type'] === 'filesystem') {
                $repos[$name] = new FilesystemRepository($repo['json']);
            } else {
                $repos[$name] = $rm->createRepository($repo['type'], $repo, (string) $index);
            }
        }

        return $repos;
    }

    /**
     * @param int|string $index
     * @param array{url?: string} $repo
     * @param array<string, mixed> $existingRepos
     */
    public static function generateRepositoryName($index, array $repo, array $existingRepos): string
    {
        $name = is_int($index) && isset($repo['url']) ? Preg::replace('{^https?://}i', '', $repo['url']) : (string) $index;
        while (isset($existingRepos[$name])) {
            $name .= '2';
        }

        return $name;
    }
}
