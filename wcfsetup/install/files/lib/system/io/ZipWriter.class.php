<?php
namespace wcf\system\io;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Creates a Zip file archive.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Io
 */
class ZipWriter {
	protected $headers = [];
	protected $data = [];
	protected $endOfData = "\x50\x4b\x05\x06\x00\x00\x00\x00";
	protected $lastOffset = 0;
	protected $zipComment = '';
	
	/**
	 * Adds a folder to the Zip archive.
	 * 
	 * @param	string		$name		dirname
	 * @param	integer		$date
	 */
	public function addDir($name, $date = TIME_NOW) {
		// replace backward slashes with forward slashes in the dirname
		$name = str_replace("\\", "/", $name);
		$name = FileUtil::addTrailingSlash($name);
		
		// construct the general header information for the directory
		$header = "\x50\x4b\x03\x04";
		$header .= "\x0a\x00\x00\x00";
		$header .= "\x00\x00\x00\x00";
		$header .= "\x00\x00";
		
		// construct the directory header specific information
		$header .= pack("V", 0);
		$header .= pack("V", 0);
		$header .= pack("V", 0);
		$header .= pack("v", strlen($name));
		$header .= pack("v", 0);
		$header .= $name;
		$header .= pack("V", 0);
		$header .= pack("V", 0);
		$header .= pack("V", 0);
		
		// store the complete header information into the $headers array
		$this->headers[] = $header;
		
		// calculate the new offset that will be used the next time a segment is added
		$newOffset = strlen(implode('', $this->headers));
		
		// construct the general header for the central index record
		$record = "\x50\x4b\x01\x02";
		$record .= "\x00\x00\x0a\x00";
		$record .= "\x00\x00\x00\x00";
		//$record .= "\x00\x00\x00\x00";
		$record .= $this->getDosDatetime($date);
		$record .= pack("V", 0);
		$record .= pack("V", 0);
		$record .= pack("V", 0);
		$record .= pack("v", strlen($name));
		$record .= pack("v", 0);
		$record .= pack("v", 0);
		$record .= pack("v", 0);
		$record .= pack("v", 0);
		//$ext = "\x00\x00\x10\x00";
		//$ext = "\xff\xff\xff\xff";
		$record .= pack("V", 16 );
		$record .= pack("V", $this->lastOffset);
		$record .= $name;
		
		// save the central index record in the array $data
		$this->data[] = $record;
		$this->lastOffset = $newOffset;
	}
	
	/**
	 * Adds a file to the Zip archive.
	 * 
	 * @param	string		$data		content of the file
	 * @param	string		$name		filename
	 * @param	integer		$date		file creation time as unix timestamp
	 */
	public function addFile($data, $name, $date = TIME_NOW) {		
		// replace backward slashes with forward slashes in the filename
		$name = str_replace("\\", "/", $name);
		
		// calculate the size of the file being uncompressed
		$sizeUncompressed = strlen($data);
		
		// get data checksum
		$crc = crc32($data);
		
		// compress the file data
		$compressedData = gzcompress($data);
		
		// calculate the size of the file being compressed
		$compressedData = substr($compressedData, 2, - 4);
		$sizeCompressed = strlen($compressedData);
		
		// construct the general header for the file record complete with checksum information, etc.
		$header = "\x50\x4b\x03\x04";
		$header .= "\x14\x00\x00\x00";
		$header .= "\x08\x00\x00\x00";
		$header .= "\x00\x00";
		$header .= pack("V", $crc);
		$header .= pack("V", $sizeCompressed);
		$header .= pack("V", $sizeUncompressed);
		$header .= pack("v", strlen($name));
		$header .= pack("v", 0);
		$header .= $name;
		
		// store the compressed data immediately following the file header
		$header .= $compressedData;
		
		// complete the file record by adding an additional footer directly following the file data
		//$header .= pack("V", $crc);
		//$header .= pack("V", $sizeCompressed);
		//$header .= pack("V", $sizeUncompressed);
		
		// store the completed file record in the $headers array
		$this->headers[] = $header;
		
		// calculate the new offset for the central index record
		$newOffset = strlen(implode('', $this->headers));
		
		// construct the record
		$record = "\x50\x4b\x01\x02";
		$record .= "\x00\x00\x14\x00";
		$record .= "\x00\x00\x08\x00";
		$record .= $this->getDosDatetime($date);
		$record .= pack("V", $crc);
		$record .= pack("V", $sizeCompressed);
		$record .= pack("V", $sizeUncompressed);
		$record .= pack("v", strlen($name));
		$record .= pack("v", 0);
		$record .= pack("v", 0);
		$record .= pack("v", 0);
		$record .= pack("v", 0);
		$record .= pack("V", 32);
		$record .= pack("V", $this->lastOffset);
		
		// update the offset for the next record to be stored
		$this->lastOffset = $newOffset;
		
		$record .= $name;
		
		// store the record in the $data array
		$this->data[] = $record;
	}
	
	/**
	 * Set Zip archive comment
	 * 
	 * @param	string		$comment		zip archive comment
	 */
	public function setArchiveComment($comment) {
		$this->zipComment = StringUtil::trim($comment);
	}
	
	/**
	 * Constructs the final Zip file structure and return it.
	 * 
	 * @return	string
	 */
	public function getFile() {
		// implode the $headers array into a single string
		$headers = implode('', $this->headers);
		
		// implode the $data array into a single string
		$data = implode('', $this->data);
		
		// construct the final Zip file structure and return it
		return
			$headers.
			$data.
			$this->endOfData.
			pack("v", count($this->data)).
			pack("v", count($this->data)).
			pack("V", strlen($data)).
			pack("V", strlen($headers)).
			(!empty($this->zipComment) ? pack("v", strlen($this->zipComment)) . $this->zipComment : "\x00\x00");
	}
	
	/**
	 * Converts an unix timestamp to Zip file time.
	 * 
	 * @param	integer		$date		unix timestamp
	 * @return	string
	 */
	protected static function getDosDatetime($date) {
		// Ensure we have a numeric value
		$date = intval($date);
		
		if ($date < 315532800) {
			return "\x00\x00\x00\x00";
		}
		
		$day = gmdate('d', $date);
		$month = gmdate('m', $date);
		$year = gmdate('Y', $date);
		$year -= 1980;
		$hour = gmdate('H', $date);
		$minute = gmdate('i', $date);
		$second = gmdate('s', $date);
		
		// calculate time
		$time = $hour;
		$time = ($time << 6) + $minute;
		$time = ($time << 5) + number_format($second / 2, 0);
		$timeRight = $time >> 8;
		$timeLeft = $time - ($timeRight << 8);
		
		// calculate date
		$date = $year;
		$date = ($date << 4) + $month;
		$date = ($date << 5) + $day;
		$dateRight = $date >> 8;
		$dateLeft = $date - ($dateRight << 8);
		
		$timeLeft = sprintf("%02x", $timeLeft);
		$timeRight = sprintf("%02x", $timeRight);
		$dateLeft = sprintf("%02x", $dateLeft);
		$dateRight = sprintf("%02x", $dateRight);
		
		return pack("H*H*H*H*", $timeLeft, $timeRight, $dateLeft, $dateRight);
	}
}
