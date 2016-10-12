<?php
namespace wcf\system\email\mime;
use wcf\system\email\EmailGrammar;
use wcf\util\FileUtil;

/**
 * Represents an email attachment.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
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
		return [ 
			['Content-Disposition', 'attachment; filename='.EmailGrammar::encodeHeader($this->filename)]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContent() {
		return $this->content;
	}
}
