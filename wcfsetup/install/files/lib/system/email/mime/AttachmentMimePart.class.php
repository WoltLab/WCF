<?php
namespace wcf\system\email\mime;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents an email attachment.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Mime
 * @since	3.0
 */
class AttachmentMimePart extends AbstractMimePart {
	/**
	 * the path the attachment is read from
	 * @var	string
	 */
	protected $path;
	
	/**
	 * the filename to provide in the email
	 * @var	string
	 */
	protected $filename;
	
	/**
	 * the mime type to provide in the email
	 * @var	string
	 */
	protected $mimeType;
	
	/**
	 * the file contents
	 * @var	string
	 */
	protected $content = '';
	
	/**
	 * Creates a new Attachment.
	 * 
	 * @param	string	$path		Path to read the file from.
	 * @param	string	$filename	Filename to provide in the email or null to use the $path's basename.
	 * @param	string	$mimeType	Mime type to provide in the email or null to guess the mime type.
	 * @throws	\InvalidArgumentException
	 */
	public function __construct($path, $filename = null, $mimeType = null) {
		if (!is_file($path) || !is_readable($path)) {
			throw new \InvalidArgumentException("Cannot attach file '".$path."'. It either does not exist or is not readable.");
		}
		
		$this->mimeType = $mimeType ?: (FileUtil::getMimeType($path) ?: 'application/octet-stream');
		$this->path = $path;
		$this->filename = $filename ?: basename($path);
		$this->content = file_get_contents($this->path);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContentType() {
		return $this->mimeType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContentTransferEncoding() {
		return 'base64';
	}
	
	/**
	 * Adds the Content-Disposition header.
	 * 
	 * @see	\wcf\system\email\mime\AbstractMimePart::getAdditionalHeaders()
	 */
	public function getAdditionalHeaders() {
		if (StringUtil::isASCII($this->filename)) {
			$encodedFilename = 'filename="'.$this->filename.'"';
		}
		else {
			// Encode according to RFC 2184
			$chunks = str_split($this->filename, 20);
			$encodedFilename = "filename".(count($chunks) > 1 ? "*0" : "")."*=utf-8''".rawurlencode($chunks[0]).";";
			for ($i = 1, $max = count($chunks); $i < $max; $i++) {
				$encodedFilename .= "\r\n    filename*".$i.'*='.rawurlencode($chunks[$i]);
				if ($i < ($max - 1)) {
					$encodedFilename .= ";";
				}
			}
		}
		
		return [ 
			['Content-Disposition', "attachment;\r\n    ".$encodedFilename]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContent() {
		return $this->content;
	}
}
