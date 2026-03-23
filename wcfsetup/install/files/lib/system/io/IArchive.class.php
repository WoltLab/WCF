<?php

namespace wcf\system\io;

/**
 * Represents an archive of files.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @phpstan-type FileInfo array{
 *  compressedSize?: int,
 *  crc32: int,
 *  filename: string,
 *  mtime: int,
 *  offset: int,
 *  size: int,
 *  type: 'file'|'folder'|'symlink',
 *  index: int,
 * }&mixed[]
 */
interface IArchive
{
    /**
     * Returns the table of contents (TOC) list for this archive.
     *
     * @return array<int, FileInfo> list of contents
     */
    public function getContentList();

    /**
     * Returns an associative array with information about a specific file
     * in the archive.
     *
     * @param mixed $index index or name of the requested file
     * @return FileInfo
     */
    public function getFileInfo(mixed $index);

    /**
     * Extracts a specific file and returns the content as string. Returns
     * false if extraction failed.
     *
     * @param mixed $index index or name of the requested file
     * @return string|false content of the requested file
     */
    public function extractToString(mixed $index);

    /**
     * Extracts a specific file and writes its content to the file specified
     * with $destination.
     *
     * @param mixed $index index or name of the requested file
     * @return bool
     */
    public function extract(mixed $index, string $destination);

    /**
     * Searchs a file in the archive and returns the numeric file index.
     * Returns false if not found.
     *
     * @return int|false index of the requested file
     */
    public function getIndexByFilename(string $filename);
}
