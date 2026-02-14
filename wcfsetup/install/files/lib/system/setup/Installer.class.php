<?php

namespace wcf\system\setup;

use wcf\system\exception\SystemException;
use wcf\system\io\Tar;
use wcf\util\FileUtil;

/**
 * Extracts files and directories from a tar archive.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class Installer
{
    /**
     * directory the files are installed into
     * @var string
     */
    protected $targetDir;

    /**
     * name of the source tar archive
     * @var string
     */
    protected $source;

    /**
     * folder within source that limits the installed files to those within
     * this folder
     * @var string
     */
    protected $folder;

    /**
     * file handler of the installed files
     * @var ?\wcf\system\setup\IFileHandler
     */
    protected $fileHandler;

    /**
     * Creates a new Installer object.
     *
     * @param string $targetDir
     * @param string $source
     * @param IFileHandler $fileHandler
     * @param string $folder
     */
    public function __construct($targetDir, $source, $fileHandler = null, $folder = '')
    {
        $this->targetDir = FileUtil::addTrailingSlash($targetDir);
        $this->source = $source;
        $this->folder = $folder;
        $this->fileHandler = $fileHandler;
        $this->install();
    }

    /**
     * Creates the target directory if necessary.
     *
     * @return void
     */
    protected function createTargetDir()
    {
        if (!@\is_dir($this->targetDir)) {
            if (!FileUtil::makePath($this->targetDir)) {
                throw new SystemException("Could not create dir '" . $this->targetDir . "'");
            }
        }
        if (FileUtil::isApacheModule() || !\is_writable($this->targetDir)) {
            $this->makeWriteable($this->targetDir);
        }
    }

    /**
     * Creates a directory in the target directory.
     *
     * @param string $dir
     * @return void
     * @throws  SystemException
     */
    protected function createDir($dir)
    {
        if (!@\is_dir($this->targetDir . $dir)) {
            $oldumask = \umask(0);
            if (!@\mkdir($this->targetDir . $dir, 0755, true)) {
                throw new SystemException("Could not create dir '" . $this->targetDir . $dir . "'");
            }
            \umask($oldumask);
        }
        if (FileUtil::isApacheModule() || !\is_writable($this->targetDir . $dir)) {
            $this->makeWriteable($this->targetDir . $dir);
        }
    }

    /**
     * Touches a file in the target directory.
     *
     * @param string $file
     * @return void
     */
    public function touchFile($file)
    {
        @\touch($this->targetDir . $file);
        $this->makeWriteable($this->targetDir . $file);
    }

    /**
     * Creates a file in the target directory.
     *
     * @param string $file
     * @param int $index
     * @param Tar $tar
     * @return void
     */
    protected function createFile($file, $index, Tar $tar)
    {
        $tar->extract($index, $this->targetDir . $file);
        if (FileUtil::isApacheModule() || !\is_writable($this->targetDir . $file)) {
            $this->makeWriteable($this->targetDir . $file);
        }
    }

    /**
     * Starts the extracting of the files.
     *
     * @return void
     */
    protected function install()
    {
        $this->createTargetDir();

        // open source archive
        $tar = $this->getTar($this->source);

        // distinct directories and files
        $directories = [];
        $files = [];
        foreach ($tar->getContentList() as $index => $file) {
            if (empty($this->folder) || \str_starts_with($file['filename'], $this->folder)) {
                if (!empty($this->folder)) {
                    $file['filename'] = \str_replace($this->folder, '', $file['filename']);
                }

                // remove leading slash
                $file['filename'] = FileUtil::getRealPath(FileUtil::removeLeadingSlash($file['filename']));
                if ($file['type'] === 'folder') {
                    // remove trailing slash
                    $directories[] = FileUtil::removeTrailingSlash($file['filename']);
                } else {
                    if (\str_ends_with($file['filename'], '.DS_Store')) {
                        continue;
                    }

                    $files[$index] = $file['filename'];
                }
            }
        }

        $this->checkFiles($files);

        // now create the directories
        $errors = [];
        foreach ($directories as $dir) {
            try {
                $this->createDir($dir);
            } catch (SystemException $e) {
                $errors[] = $e->getMessage();
            }
        }

        // now untar all files
        foreach ($files as $index => $file) {
            try {
                $this->createFile($file, $index, $tar);
            } catch (SystemException $e) {
                $errors[] = $e->getMessage();
            }
        }
        if (!empty($errors)) {
            throw new SystemException('error(s) during the installation of the files.', 0, \implode("<br>", $errors));
        }

        $this->logFiles($files);

        // close tar
        $tar->close();
    }

    /**
     * Opens a new tar archive.
     *
     * @param string $source
     * @return      Tar
     */
    protected function getTar($source)
    {
        return new Tar($source);
    }

    /**
     * Checks whether the given files overwriting locked existing files.
     *
     * @param list<string> $files
     * @return void
     */
    protected function checkFiles(&$files)
    {
        $this->fileHandler?->checkFiles($files);
    }

    /**
     * Logs the given files.
     *
     * @param list<string> $files
     * @return void
     */
    protected function logFiles(&$files)
    {
        $this->fileHandler?->logFiles($files);
    }

    /**
     * Makes a file or directory writeable.
     *
     * @param string $target
     * @return void
     */
    protected function makeWriteable($target)
    {
        FileUtil::makeWritable($target);
    }
}
