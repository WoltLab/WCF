<?php

namespace wcf\util;

use ParagonIE\ConstantTime\Hex;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\io\GZipFile;

/**
 * Contains file-related functions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class FileUtil
{
    /**
     * finfo instance
     */
    protected static \finfo $finfo;

    /**
     * memory limit in bytes
     */
    protected static int $memoryLimit;

    /**
     * chmod mode
     * @var string
     */
    protected static $mode;

    /**
     * A regular expression that allows to detect links within text.
     */
    public const LINK_REGEX = "#(?i)\\b((?:https?://|www\\d{0,3}[.]|(?:[a-z0-9\\-]+[.])+[a-z]{2,4}/)(?:[^\\s()<>\\[\\]]+|\\([^\\s()<>\\[\\]]*\\))+(?:\\([^\\s()<>\\[\\]]*\\)|[^\\s`!()\\[\\]{};:'\".,<>?«»“”‘’]))#iS";

    /**
     * Prepares the temporary folder and returns its path.
     *
     * @throws \RuntimeException if the temporary folder is not usable.
     */
    public static function getTempFolder(): string
    {
        $path = self::unifyDirSeparator(WCF_DIR . 'tmp/');

        if (\is_file($path)) {
            // wat
            \unlink($path);
        }

        if (!\file_exists($path)) {
            \mkdir($path, 0777);
        }

        if (!\is_dir($path)) {
            throw new \RuntimeException(\sprintf(
                "Temporary folder '%s' does not exist and could not be created. Please check the permissions of the '%s' folder using your favorite ftp program.",
                $path,
                \dirname($path)
            ));
        }

        if (!\is_writable($path)) {
            self::makeWritable($path);
        }

        if (!\is_writable($path)) {
            throw new \RuntimeException(\sprintf(
                "Temporary folder '%s' is not writable. Please check the permissions using your favorite ftp program.",
                $path
            ));
        }

        if (@\md5_file($path . '/.htaccess') !== '62745e850958bfd8a1fd77f90e74184b') {
            \file_put_contents($path . '/.htaccess', "Require all denied\n");
        }

        return $path;
    }

    /**
     * Generates a new temporary filename in TMP_DIR.
     */
    public static function getTemporaryFilename(
        string $prefix = 'tmpFile_',
        string $extension = '',
        string $dir = TMP_DIR
    ): string {
        $dir = self::addTrailingSlash($dir);
        do {
            $tmpFile = $dir . $prefix . Hex::encode(\random_bytes(20)) . $extension;
        } while (\file_exists($tmpFile));

        return $tmpFile;
    }

    /**
     * Removes a leading slash from the given path.
     */
    public static function removeLeadingSlash(string $path): string
    {
        return \ltrim($path, '/');
    }

    /**
     * Removes a trailing slash from the given path.
     */
    public static function removeTrailingSlash(string $path): string
    {
        return \rtrim($path, '/');
    }

    /**
     * Adds a trailing slash to the given path.
     */
    public static function addTrailingSlash(string $path): string
    {
        return \rtrim($path, '/') . '/';
    }

    /**
     * Adds a leading slash to the given path.
     */
    public static function addLeadingSlash(string $path): string
    {
        return '/' . \ltrim($path, '/');
    }

    /**
     * Returns the relative path from the given absolute paths.
     *
     * @param string $currentDir
     * @param string $targetDir
     * @return  string
     */
    public static function getRelativePath($currentDir, $targetDir)
    {
        // remove trailing slashes
        $currentDir = self::removeTrailingSlash(self::unifyDirSeparator($currentDir));
        $targetDir = self::removeTrailingSlash(self::unifyDirSeparator($targetDir));

        if ($currentDir == $targetDir) {
            return './';
        }

        $current = \explode('/', $currentDir);
        $target = \explode('/', $targetDir);

        $relPath = '';
        for ($i = 0, $max = \max(\count($current), \count($target)); $i < $max; $i++) {
            if (isset($current[$i]) && isset($target[$i])) {
                if ($current[$i] != $target[$i]) {
                    for ($j = 0; $j < $i; $j++) {
                        unset($target[$j]);
                    }
                    $relPath .= \str_repeat('../', \count($current) - $i) . \implode('/', $target) . '/';

                    break;
                }
            } // go up one level
            elseif (isset($current[$i]) && !isset($target[$i])) {
                $relPath .= '../';
            } elseif (!isset($current[$i]) && isset($target[$i])) {
                $relPath .= $target[$i] . '/';
            }
        }

        return $relPath;
    }

    /**
     * Creates a path on the local filesystem and returns true on success.
     * Parent directories do not need to exists as they will be created if
     * necessary.
     */
    public static function makePath(string $path): bool
    {
        // directory already exists, abort
        if (\file_exists($path)) {
            return false;
        }

        // check if parent directory exists
        $parent = \dirname($path);
        if ($parent != $path) {
            // parent directory does not exist either
            // we have to create the parent directory first
            $parent = self::addTrailingSlash($parent);
            if (!@\file_exists($parent)) {
                // could not create parent directory either => abort
                if (!self::makePath($parent)) {
                    return false;
                }
            }

            // well, the parent directory exists or has been created
            // lets create this path
            if (!@\mkdir($path)) {
                return false;
            }

            self::makeWritable($path);

            return true;
        }

        return false;
    }

    /**
     * Unifies windows and unix directory separators.
     */
    public static function unifyDirSeparator(string $path): string
    {
        $path = \str_replace('\\\\', '/', $path);

        return \str_replace('\\', '/', $path);
    }

    /**
     * Scans a folder (and subfolder) for a specific file.
     * Returns the filename if found, otherwise false.
     *
     * @param string $folder
     * @param string $searchfile
     * @param bool $recursive
     * @return  mixed
     */
    public static function scanFolder($folder, $searchfile, $recursive = true)
    {
        if (!@\is_dir($folder)) {
            return false;
        }
        if (!$searchfile) {
            return false;
        }

        $folder = self::addTrailingSlash($folder);
        $dirh = @\opendir($folder);
        while ($filename = @\readdir($dirh)) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            if ($filename == $searchfile) {
                @\closedir($dirh);

                return $folder . $filename;
            }

            if ($recursive == true && @\is_dir($folder . $filename)) {
                if ($found = self::scanFolder($folder . $filename, $searchfile, $recursive)) {
                    @\closedir($dirh);

                    return $found;
                }
            }
        }
        @\closedir($dirh);
    }

    /**
     * Returns true if the given filename is an url (http or ftp).
     */
    public static function isURL(string $filename): bool
    {
        return !!\preg_match('!^(https?|ftp)://!', $filename);
    }

    /**
     * Returns canonicalized absolute pathname.
     */
    public static function getRealPath(string $path): string
    {
        $path = self::unifyDirSeparator($path);

        $result = [];
        $pathA = \explode('/', $path);
        if ($pathA[0] === '') {
            $result[] = '';
        }

        foreach ($pathA as $dir) {
            if ($dir == '..') {
                if (\end($result) == '..') {
                    $result[] = '..';
                } else {
                    $lastValue = \array_pop($result);
                    if ($lastValue === '' || $lastValue === null) {
                        $result[] = '..';
                    }
                }
            } elseif ($dir !== '' && $dir != '.') {
                $result[] = $dir;
            }
        }

        $lastValue = \end($pathA);
        if ($lastValue === '' || $lastValue === false) {
            $result[] = '';
        }

        return \implode('/', $result);
    }

    /**
     * Formats the given filesize.
     */
    public static function formatFilesize(int $byte, int $precision = 2): string
    {
        $symbol = 'Byte';
        if ($byte >= 1000) {
            $byte /= 1000;
            $symbol = 'kB';
        }
        if ($byte >= 1000) {
            $byte /= 1000;
            $symbol = 'MB';
        }
        if ($byte >= 1000) {
            $byte /= 1000;
            $symbol = 'GB';
        }
        if ($byte >= 1000) {
            $byte /= 1000;
            $symbol = 'TB';
        }

        return StringUtil::formatNumeric(\round($byte, $precision)) . ' ' . $symbol;
    }

    /**
     * Formats a filesize with binary prefix.
     *
     * For more information: <http://en.wikipedia.org/wiki/Binary_prefix>
     */
    public static function formatFilesizeBinary(int $byte, int $precision = 2): string
    {
        $symbol = 'Byte';
        if ($byte >= 1024) {
            $byte /= 1024;
            $symbol = 'KiB';
        }
        if ($byte >= 1024) {
            $byte /= 1024;
            $symbol = 'MiB';
        }
        if ($byte >= 1024) {
            $byte /= 1024;
            $symbol = 'GiB';
        }
        if ($byte >= 1024) {
            $byte /= 1024;
            $symbol = 'TiB';
        }

        return StringUtil::formatNumeric(\round($byte, $precision)) . ' ' . $symbol;
    }

    /**
     * Determines whether a file is text or binary by checking the first few bytes in the file.
     * The exact number of bytes is system dependent, but it is typically several thousand.
     * If every byte in that part of the file is non-null, considers the file to be text;
     * otherwise it considers the file to be binary.
     */
    public static function isBinary(string $file): bool
    {
        // open file
        $file = new File($file, 'rb');

        // get block size
        $stat = $file->stat();
        $blockSize = $stat['blksize'];
        if ($blockSize < 0) {
            $blockSize = 1024;
        }
        if ($blockSize > $file->filesize()) {
            $blockSize = $file->filesize();
        }
        if ($blockSize <= 0) {
            return false;
        }

        // get bytes
        $block = $file->read($blockSize);

        return \strlen($block) == 0 || \strpos($block, "\0") !== false;
    }

    /**
     * Uncompresses a gzipped file and returns true if successful.
     */
    public static function uncompressFile(string $gzipped, string $destination): bool
    {
        if (!@\is_file($gzipped)) {
            return false;
        }

        $sourceFile = new GZipFile($gzipped, 'rb');
        $targetFile = new File($destination);
        while (!$sourceFile->eof()) {
            $targetFile->write($sourceFile->read(512), 512);
        }
        $targetFile->close();
        $sourceFile->close();

        self::makeWritable($destination);

        return true;
    }

    /**
     * Returns true if php is running as apache module.
     */
    public static function isApacheModule(): bool
    {
        return \function_exists('apache_get_version');
    }

    /**
     * Returns the mime type of a file.
     */
    public static function getMimeType(string $filename): string
    {
        if (!isset(self::$finfo)) {
            if (!\class_exists(\finfo::class, false)) {
                return 'application/octet-stream';
            }

            self::$finfo = new \finfo(\FILEINFO_MIME_TYPE);
        }

        // \finfo->file() can fail for files that contain only 1 byte, because libmagic expects at least
        // a few bytes in order to determine the type. See https://bugs.php.net/bug.php?id=64684
        $mimeType = @self::$finfo->file($filename);

        return $mimeType ?: 'application/octet-stream';
    }

    /**
     * Tries to make a file or directory writable. It starts of with the least
     * permissions and goes up until 0666 for files and 0777 for directories.
     *
     * @throws  SystemException
     */
    public static function makeWritable(string $filename): void
    {
        if (!\file_exists($filename)) {
            return;
        }

        if (self::$mode === null) {
            // WCFSetup
            if (\defined('INSTALL_SCRIPT') && \file_exists(INSTALL_SCRIPT)) {
                // do not use PHP_OS here, as this represents the system it was built on != running on
                // php_uname() is forbidden on some strange hosts; PHP_EOL is reliable
                if (\PHP_EOL == "\r\n") {
                    // Windows
                    self::$mode = '0777';
                } else {
                    // anything but Windows
                    \clearstatcache();

                    self::$mode = '0666';

                    $tmpFilename = '__permissions_' . \sha1((string)\time()) . '.txt';
                    @\touch($tmpFilename);

                    // create a new file and check the file owner, if it is the same
                    // as this file (uploaded through FTP), we can safely grant write
                    // permissions exclusively to the owner rather than everyone
                    if (\file_exists($tmpFilename)) {
                        $scriptOwner = \fileowner(INSTALL_SCRIPT);
                        $fileOwner = \fileowner($tmpFilename);

                        if ($scriptOwner === $fileOwner) {
                            self::$mode = '0644';
                        }

                        @\unlink($tmpFilename);
                    }
                }
            } else {
                // mirror permissions of WCF.class.php
                if (!\file_exists(WCF_DIR . 'lib/system/WCF.class.php')) {
                    throw new SystemException("Unable to find 'wcf/lib/system/WCF.class.php'.");
                }

                self::$mode = '0' . \substr(\sprintf('%o', \fileperms(WCF_DIR . 'lib/system/WCF.class.php')), -3);
            }
        }

        if (\is_dir($filename)) {
            if (self::$mode == '0644') {
                @\chmod($filename, 0755);
            } else {
                @\chmod($filename, 0777);
            }
        } else {
            @\chmod($filename, \octdec(self::$mode));
        }

        if (!\is_writable($filename)) {
            // does not work with 0777
            throw new SystemException("Unable to make '" . $filename . "' writable. This is a misconfiguration of your server, please contact your system administrator or hosting provider.");
        }
    }

    /**
     * Returns memory limit in bytes.
     */
    public static function getMemoryLimit(): int
    {
        if (!isset(self::$memoryLimit)) {
            self::$memoryLimit = 0;

            $memoryLimit = \ini_get('memory_limit');

            // no limit
            if ($memoryLimit == "-1") {
                self::$memoryLimit = -1;
            } else if (\function_exists('ini_parse_quantity')) {
                self::$memoryLimit = \ini_parse_quantity($memoryLimit);
            } else {
                // completely numeric, PHP assumes byte
                if (\is_numeric($memoryLimit)) {
                    self::$memoryLimit = $memoryLimit;
                }

                // PHP supports 'K', 'M' and 'G' shorthand notation
                if (\preg_match('~^(\d+)\s*([KMG])$~i', $memoryLimit, $matches)) {
                    switch (\strtoupper($matches[2])) {
                        case 'K':
                            self::$memoryLimit = $matches[1] * 1024;
                            break;

                        case 'M':
                            self::$memoryLimit = $matches[1] * 1024 * 1024;
                            break;

                        case 'G':
                            self::$memoryLimit = $matches[1] * 1024 * 1024 * 1024;
                            break;
                    }
                }
            }
        }

        return self::$memoryLimit;
    }

    /**
     * Returns true if the given amount of memory is available.
     */
    public static function checkMemoryLimit(int|float $neededMemory): bool
    {
        return self::getMemoryLimit() == -1 || self::getMemoryLimit() > (\memory_get_usage() + $neededMemory);
    }

    /**
     * Returns icon name for given filename.
     */
    public static function getIconNameByFilename(string $filename): string
    {
        static $mapping = [
            // archive
            'zip' => 'zipper',
            'rar' => 'zipper',
            'tar' => 'zipper',
            'gz' => 'zipper',
            // audio
            'mp3' => 'audio',
            'ogg' => 'audio',
            'wav' => 'audio',
            // code
            'php' => 'code',
            'html' => 'code',
            'htm' => 'code',
            'tpl' => 'code',
            'js' => 'code',
            // excel
            'xls' => 'excel',
            'ods' => 'excel',
            'xlsx' => 'excel',
            // image
            'gif' => 'image',
            'jpg' => 'image',
            'jpeg' => 'image',
            'png' => 'image',
            'bmp' => 'image',
            'webp' => 'image',
            // video
            'avi' => 'video',
            'wmv' => 'video',
            'mov' => 'video',
            'mp4' => 'video',
            'mpg' => 'video',
            'mpeg' => 'video',
            'flv' => 'video',
            // pdf
            'pdf' => 'pdf',
            // powerpoint
            'ppt' => 'powerpoint',
            'pptx' => 'powerpoint',
            // text
            'txt' => 'lines',
            // word
            'doc' => 'word',
            'docx' => 'word',
            'odt' => 'word',
        ];

        $lastDotPosition = \strrpos($filename, '.');
        if ($lastDotPosition !== false) {
            $extension = \substr($filename, $lastDotPosition + 1);
            if (isset($mapping[$extension])) {
                return $mapping[$extension];
            }
        }

        return '';
    }

    /**
     * Returns whether the given $extension might allow for execution of
     * PHP code and thus must not be used for untrusted files uploaded by
     * a user.
     */
    public static function extensionAllowsPhpExecution(string $extension): bool
    {
        return !!\preg_match('/^\.?(php[0-9]*|phtml)$/i', $extension);
    }

    /**
     * Forbid creation of FileUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
