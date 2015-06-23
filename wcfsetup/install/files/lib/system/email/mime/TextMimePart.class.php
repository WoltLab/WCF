<?php
namespace wcf\system\email\mime;

/**
 * Represents the visible text of an email.
 * The content type usually is either text/plain or text/html.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 */
class TextMimePart extends AbstractMimePart {
	/**
	 * the content of this text part
	 * @var	string
	 */
	protected $content;
	
	/**
	 * the mime type to provide in the email
	 * @var	string
	 */
	protected $mimeType;
	
	/**
	 * Creates a new Text.
	 * 
	 * @param	string	$content	Content of this text part.
	 * @param	string	$mimeType	Mime type to provide in the email. You *must* not provide a charset. UTF-8 will be used automatically.
	 */
	public function __construct($content, $mimeType) {
		$this->mimeType = $mimeType;
		$this->content = $content;
	}
	
	/**
	 * @see	\wcf\system\email\mime\AbstractMimePart::getContentType()
	 */
	public function getContentType() {
		return $this->mimeType."; charset=UTF-8";
	}
	
	/**
	 * @see	\wcf\system\email\mime\AbstractMimePart::getContentTransferEncoding()
	 */
	public function getContentTransferEncoding() {
		return 'quoted-printable';
	}
	
	/**
	 * @see	\wcf\system\email\mime\AbstractMimePart::getContent()
	 */
	public function getContent() {
		return $this->content;
	}
}
