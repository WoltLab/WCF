<?php

namespace wcf\system\io;

use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * Reads zip files.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Io
 */
class Zip extends File implements IArchive
{
    const LOCAL_FILE_SIGNATURE = "\x50\x4b\x03\x04";

    const CENTRAL_DIRECTORY_SIGNATURE = "\x50\x4b\x01\x02";

    const EOF_SIGNATURE = "\x50\x4b\x05\x06";

    const DATA_DESCRIPTOR_SIGNATURE = "\x50\x4b\x07\x08";

    protected $centralDirectory;

    /**
     * @inheritDoc
     */
    public function __construct($filename)
    {
        parent::__construct($filename, 'rb');

        $this->centralDirectory = $this->readCentralDirectory();
    }

    /**
     * @inheritDoc
     */
    public function getIndexByFilename($filename)
    {
        if (isset($this->centralDirectory['files'][$filename])) {
            return $this->centralDirectory['files'][$filename]['offset'];
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getContentList()
    {
        return $this->centralDirectory['files'];
    }

    /**
     * @inheritDoc
     */
    public function getFileInfo($offset)
    {
        if (!\is_int($offset)) {
            $offset = $this->getIndexByFilename($offset);
        }

        $info = $this->readFile($offset);

        return $info['header'];
    }

    /**
     * Extracts all files to the given destination.
     * The directory-structure inside the .zip is preserved.
     *
     * @param string $destination where to extract
     */
    public function extractAll($destination)
    {
        $destination = FileUtil::addTrailingSlash($destination);
        $this->seek(0);

        while ($this->isFile()) {
            $offset = $this->tell();
            $file = $this->readFile();
            $filename = $file['header']['filename'];
            $this->extract($offset, $destination . $filename);
        }
    }

    /**
     * @inheritDoc
     */
    public function extractToString($offset)
    {
        if (!\is_int($offset)) {
            $offset = $this->getIndexByFilename($offset);
        }

        try {
            $file = $this->readFile($offset);
        } catch (SystemException $e) {
            return false;
        }
        if ($file['header']['type'] === 'folder') {
            return false;
        }

        return $file['content'];
    }

    /**
     * @inheritDoc
     */
    public function extract($offset, $destination)
    {
        if (!\is_int($offset)) {
            $offset = $this->getIndexByFilename($offset);
        }

        try {
            $file = $this->readFile($offset);
        } catch (SystemException $e) {
            return false;
        }

        FileUtil::makePath(\dirname($destination));
        if ($file['header']['type'] === 'folder') {
            FileUtil::makePath($destination);

            return;
        }

        $targetFile = new File($destination);
        $targetFile->write($file['content'], \strlen($file['content']));
        $targetFile->close();

        FileUtil::makeWritable($destination);

        if ($file['header']['mtime']) {
            @$targetFile->touch($file['header']['mtime']);
        }

        // check filesize
        if (\filesize($destination) != $file['header']['size']) {
            throw new SystemException("Could not unzip file '" . $file['header']['filename'] . "' to '" . $destination . "'. Maybe disk quota exceeded in folder '" . \dirname($destination) . "'.");
        }

        return true;
    }

    /**
     * Moves the file-pointer to the beginning of the Central Directory.
     */
    protected function jumpToCentralDirectory()
    {
        $this->seek(0, \SEEK_END);
        $lastOffset = $this->tell();
        $this->seek(-4, \SEEK_CUR);

        do {
            if ($this->read(4) === self::EOF_SIGNATURE) {
                $eof = \unpack(
                    'vdiskNo/vdiskWithCentralDirectory/vdiskEntries/vtotalEntries/VcentralDirectorySize/VcentralDirectoryOffset/vcommentLength',
                    $this->read(18)
                );
                if ($eof['commentLength'] + $this->tell() === $lastOffset) {
                    $this->seek($eof['centralDirectoryOffset']);
                    break;
                } else {
                    // some part of the comment looked like the EOF_SIGNATURE
                    $this->seek(-18, \SEEK_CUR);
                }
            }

            $this->seek(-5, \SEEK_CUR);
        } while (true);

        if ($this->read(4) !== self::CENTRAL_DIRECTORY_SIGNATURE) {
            throw new SystemException('Unable to locate central directory');
        }
        $this->seek(-4, \SEEK_CUR);
    }

    /**
     * Reads the central directory and returns it.
     *
     * @return  array
     * @throws  SystemException
     */
    protected function readCentralDirectory()
    {
        $this->jumpToCentralDirectory();

        $offset = $this->tell();

        // check signature
        if ($this->read(4) !== self::CENTRAL_DIRECTORY_SIGNATURE) {
            throw new SystemException('Not in central directory');
        }
        $this->seek(-4, \SEEK_CUR);

        $files = [];
        while ($this->read(4) === self::CENTRAL_DIRECTORY_SIGNATURE) {
            $data = \unpack('vversion/vminVersion/vgeneralPurposeBit/vcompression/vmtime/vmdate', $this->read(12));
            // calculate timestamp
            $second = ($data['mtime'] & ((1 << 5) - 1)) * 2;
            $minute = ($data['mtime'] >> 5) & ((1 << 6) - 1);
            $hour = ($data['mtime'] >> 11) & ((1 << 5) - 1);
            $day = $data['mdate'] & ((1 << 5) - 1);
            $month = ($data['mdate'] >> 5) & ((1 << 4) - 1);
            $year = (($data['mdate'] >> 9) & ((1 << 7) - 1)) + 1980;
            $data['mtime'] = \gmmktime($hour, $minute, $second, $month, $day, $year);

            $data += \unpack(
                'Vcrc32/VcompressedSize/Vsize/vfilenameLength/vextraFieldLength/vfileCommentLength/vdiskNo/vinternalAttr/vexternalAttr',
                $this->read(26)
            );
            $data['offset'] = $this->readAndUnpack(4, 'V');
            $data['filename'] = $this->read($data['filenameLength']);
            if (\substr($data['filename'], -1) == '/') {
                $data['type'] = 'folder';
            } else {
                $data['type'] = 'file';
            }

            // read extraField
            if ($data['extraFieldLength'] > 0) {
                $data['extraField'] = $this->read($data['extraFieldLength']);
            } else {
                $data['extraField'] = '';
            }
            // read filecomment
            if ($data['fileCommentLength'] > 0) {
                $data['fileComment'] = $this->read($data['fileCommentLength']);
            } else {
                $data['fileComment'] = '';
            }

            $files[$data['filename']] = $data;
        }
        $this->seek(-4, \SEEK_CUR);
        $size = $this->tell() - $offset;

        if ($this->read(4) !== self::EOF_SIGNATURE) {
            throw new SystemException('Could not find the end of Central Directory');
        }

        $eof = \unpack(
            'vdiskNo/vdiskWithCentralDirectory/vdiskEntries/vtotalEntries/VcentralDirectorySize',
            $this->read(12)
        );
        // check size of Central Directory
        if ($size !== $eof['centralDirectorySize']) {
            throw new SystemException('Central Directory size does not match');
        }
        $eof += \unpack('VcentralDirectoryOffset/vcommentLength', $this->read(6));

        // read comment
        if ($eof['commentLength'] > 0) {
            $eof['comment'] = $this->read($eof['commentLength']);
        } else {
            $eof['comment'] = '';
        }

        return ['files' => $files, 'eof' => $eof];
    }

    /**
     * Checks whether the next record is a file.
     * This does not change the position of the file-pointer.
     *
     * @param int $offset where to start reading
     * @return  bool
     * @throws  SystemException
     */
    public function isFile($offset = null)
    {
        if ($offset === null) {
            $offset = $this->tell();
        }
        if ($offset === false) {
            throw new SystemException('Invalid offset passed to isFile');
        }

        $oldOffset = $this->tell();
        $this->seek($offset);
        // check signature
        $result = $this->read(4) === self::LOCAL_FILE_SIGNATURE;

        $this->seek($oldOffset);

        return $result;
    }

    /**
     * Reads a file and returns it.
     *
     * @param int $offset where to start reading
     * @return  array
     * @throws  SystemException
     */
    public function readFile($offset = null)
    {
        if ($offset === null) {
            $offset = $this->tell();
        }
        if (!\is_int($offset)) {
            $offset = $this->getIndexByFilename($offset);
        }
        if ($offset === false) {
            throw new SystemException('Invalid offset passed to readFile');
        }

        $this->seek($offset);
        // check signature
        if ($this->read(4) !== self::LOCAL_FILE_SIGNATURE) {
            throw new SystemException('Invalid offset passed to readFile');
        }

        // read headers
        $header = \unpack('vminVersion/vgeneralPurposeBit/vcompression/vmtime/vmdate', $this->read(10));
        $second = ($header['mtime'] & ((1 << 5) - 1)) * 2;
        $minute = ($header['mtime'] >> 5) & ((1 << 6) - 1);
        $hour = ($header['mtime'] >> 11) & ((1 << 5) - 1);
        $day = $header['mdate'] & ((1 << 5) - 1);
        $month = ($header['mdate'] >> 5) & ((1 << 4) - 1);
        $year = (($header['mdate'] >> 9) & ((1 << 7) - 1)) + 1980;
        $header['x-timestamp'] = \gmmktime($hour, $minute, $second, $month, $day, $year);
        $header += \unpack('Vcrc32/VcompressedSize/Vsize/vfilenameLength/vextraFieldLength', $this->read(16));

        // read filename
        $header['filename'] = $this->read($header['filenameLength']);
        // read extra field
        if ($header['extraFieldLength'] > 0) {
            $header['extraField'] = $this->read($header['extraFieldLength']);
        } else {
            $header['extraField'] = '';
        }

        // fetch sizes and crc from central directory
        if ($header['generalPurposeBit'] & (1 << 3)) {
            $header['compressedSize'] = $this->centralDirectory['files'][$header['filename']]['compressedSize'];
            $header['size'] = $this->centralDirectory['files'][$header['filename']]['size'];
            $header['crc32'] = $this->centralDirectory['files'][$header['filename']]['crc32'];
        }

        // read contents
        $header['type'] = 'file';
        if (\substr($header['filename'], -1) != '/') {
            $content = $this->read($header['compressedSize']);
        } else {
            $header['type'] = 'folder';
            $content = false;
        }

        // uncompress file
        switch ($header['compression']) {
            case 8:
                $content = \gzinflate($content);
                break;

            case 12:
                if (\function_exists('bzdecompress')) {
                    $content = \bzdecompress($content);
                } else {
                    throw new SystemException('The bzip2 extension is not available');
                }
                break;

            case 0:
                break;

            default:
                throw new SystemException('Compression ' . $header['compression'] . ' is not supported');
        }

        // check crc32
        if (\crc32($content) != $header['crc32']) {
            throw new SystemException('Checksum does not match');
        }

        // gobble data descriptor
        if ($header['generalPurposeBit'] & (1 << 3)) {
            if ($this->read(4) === self::DATA_DESCRIPTOR_SIGNATURE) {
                $this->read(12);
            } else {
                $this->read(8);
            }
        }

        return ['header' => $header, 'content' => $content];
    }

    /**
     * Reads in the specified number of bytes and unpacks them.
     *
     * @param int $length Number of bytes to read
     * @param string $type Which type are the bytes of
     * @return  mixed
     */
    protected function readAndUnpack($length, $type)
    {
        $data = \unpack($type, $this->read($length));

        return $data[1];
    }
}
