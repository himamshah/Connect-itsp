<?php
/**
 * @license MIT
 *
 * Modified by Philo Hermans on 21-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Anystack\WPGuard\V001\League\Flysystem\Plugin;

use Anystack\WPGuard\V001\League\Flysystem\FileExistsException;
use Anystack\WPGuard\V001\League\Flysystem\FileNotFoundException;

class ForcedRename extends AbstractPlugin
{
    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return 'forceRename';
    }

    /**
     * Renames a file, overwriting the destination if it exists.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws FileNotFoundException Thrown if $path does not exist.
     * @throws FileExistsException
     *
     * @return bool True on success, false on failure.
     */
    public function handle($path, $newpath)
    {
        try {
            $deleted = $this->filesystem->delete($newpath);
        } catch (FileNotFoundException $e) {
            // The destination path does not exist. That's ok.
            $deleted = true;
        }

        if ($deleted) {
            return $this->filesystem->rename($path, $newpath);
        }

        return false;
    }
}
