<?php
namespace wcf\system\email\mime;
use wcf\system\email\EmailGrammar;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * Represents an email attachment.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 * @since	2.2
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
	 * @throws	SystemException
	 */
	public function __construct($path, $filename = null, $mimeType = null) {
		if (!is_file($path) || !is_readable($path)) {
			throw new SystemException("Cannot attach file '".$path."'. It either does not exist or is not readable.");
		}
		
		$this->mimeType = $mimeType ?: (FileUtil::getMimeType($path) ?: 'application/octet-stream');
		$this->path = $path;
		$this->filename = $filename ?: basename($path);
		$this->content = file_get_contents($this->path);
	}
	
	/**
	 * @see	\wcf\system\email\mime\AbstractMimePart::getContentType()
	 */
	public function getContentType() {
		return $this->mimeType;
	}
	
	/**
	 * @see	\wcf\system\email\mime\AbstractMimePart::getContentTransferEncoding()
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
	 * @see	\wcf\system\email\mime\AbstractMimePart::getContent()
	 */
	public function getContent() {
		return $this->content;
	}
}
