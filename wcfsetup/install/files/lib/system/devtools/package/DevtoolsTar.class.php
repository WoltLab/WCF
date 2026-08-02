<?php

namespace wcf\system\devtools\package;

use wcf\system\io\Tar;

/**
 * Specialized implementation to emulate a regular package installation.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DevtoolsTar extends Tar
{
    /**
     * list of virtual files
     * @var array<string, string>
     */
    protected $files = [];

    /**
     * @param array<string, string> $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * Resets the internal file list for re-use, because the devtools use
     * the same instance over and over to avoid some otherwise awkward
     * changes to the code.
     *
     * @return void
     */
    public function reset()
    {
        $this->contentList = $this->files = [];
        $this->read = false;
    }

    /**
     * Registers a new file in the virtual file list.
     *
     * @return void
     */
    public function registerFile(string $filename, string $fullPath)
    {
        $this->files[$filename] = $fullPath;
    }

    /**
     * @return string|false
     * @phpstan-ignore method.childReturnType, method.childReturnType
     */
    #[\Override]
    public function getIndexByFilename(string $filename)
    {
        return isset($this->files[$filename]) ? $filename : false;
    }

    #[\Override]
    public function extractToString(int|string $index)
    {
        if (!isset($this->files[$index])) {
            throw new \RuntimeException(
                "DevtoolsTar does not permit reading any files except for the explicitly registered ones."
            );
        }

        return \file_get_contents($this->files[$index]);
    }

    #[\Override]
    public function extract(int|string $index, string $destination)
    {
        if (\is_int($index)) {
            $header = $this->getFileInfo($index);
            $index = $header['filename'];
        }

        // The source file is empty, if the file is a symlink, which yield to an error.
        if (empty($this->files[$index])) {
            return false;
        }

        if (\file_exists($destination)) {
            $sourceHash = \hash_file('xxh3', $this->files[$index], true);
            $targetHash = \hash_file('xxh3', $destination, true);
            if ($sourceHash === $targetHash) {
                return false;
            }
        }

        \copy($this->files[$index], $destination);

        return true;
    }

    #[\Override]
    public function getContentList()
    {
        if (!$this->read) {
            $contentList = [];
            foreach ($this->files as $filename => $fullPath) {
                if (\strpos($filename, '/') !== false) {
                    $directory = \dirname($filename) . '/';
                    $contentList[$directory] ??= [
                        'filename' => $directory,
                        'type' => 'folder',
                    ];
                }

                $contentList[$filename] = [
                    'filename' => $filename,
                    'type' => 'file',
                ];
            }

            // @phpstan-ignore assign.propertyType
            $this->contentList = \array_values($contentList);
            $this->read = true;
        }

        return $this->contentList;
    }

    /**
     * Returns all files in the virtual file list.
     *
     * @return string[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Sets all files in the virtual file list.
     *
     * @param string[] $files
     * @return void
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
    }
}
