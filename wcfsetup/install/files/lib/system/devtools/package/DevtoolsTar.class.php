<?php

namespace wcf\system\devtools\package;

use wcf\system\io\Tar;

/**
 * Specialized implementation to emulate a regular package installation.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.1
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
     * @param string $filename
     * @param string $fullPath
     * @return void
     */
    public function registerFile($filename, $fullPath)
    {
        $this->files[$filename] = $fullPath;
    }

    #[\Override]
    public function getIndexByFilename(string $filename)
    {
        return isset($this->files[$filename]) ? $filename : false;
    }

    #[\Override]
    public function extractToString($index)
    {
        if (!isset($this->files[$index])) {
            throw new \RuntimeException(
                "DevtoolsTar does not permit reading any files except for the explicitly registered ones."
            );
        }

        return \file_get_contents($this->files[$index]);
    }

    #[\Override]
    public function extract($index, string $destination)
    {
        // The source file is empty, if the file is a symlink, which yield to an error.
        if (empty($this->files[$index])) {
            return false;
        }

        \copy($this->files[$index], $destination);

        return true;
    }

    #[\Override]
    public function getContentList()
    {
        if (!$this->read) {
            foreach ($this->files as $filename => $fullPath) {
                if (\strpos($filename, '/') !== false) {
                    $directory = \dirname($filename) . '/';
                    if (!isset($this->contentList[$directory])) {
                        $this->contentList[$directory] = [
                            'filename' => $directory,
                            'type' => 'folder',
                        ];
                    }
                }

                $this->contentList[$filename] = [
                    'filename' => $filename,
                    'type' => 'file',
                ];
            }

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
