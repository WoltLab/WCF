<?php
namespace wcf\system\email\mime;
use wcf\system\email\Mailbox;
use wcf\util\StringUtil;

/**
 * Represents a multipart/* mime container.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Mime
 * @since	3.0
 */
abstract class AbstractMultipartMimePart extends AbstractMimePart implements IRecipientAwareMimePart {
	/**
	 * The boundary between the distinct parts.
	 * @var	string
	 */
	protected $boundary;
	
	/**
	 * The parts.
	 * @var	\SplObjectStorage
	 */
	protected $parts;
	
	/**
	 * Sets the multipart boundary.
	 */
	public function __construct() {
		$this->boundary = "WoltLab_Community_Framework=_".StringUtil::getRandomID();
		$this->parts = new \SplObjectStorage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContentTransferEncoding() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function setRecipient(Mailbox $mailbox = null) {
		foreach ($this->parts as $part) {
			if ($part instanceof IRecipientAwareMimePart) {
				$part->setRecipient($mailbox);
			}
		}
	}
	
	/**
	 * Concatenates the given mime parts.
	 * 
	 * @param	\Traversable	$parts
	 * @return	string
	 */
	protected function getConcatenatedParts($parts) {
		$content = "";
		foreach ($parts as $part) {
			$content .= "--".$this->boundary."\r\n";
			$content .= "content-type: ".$part->getContentType()."\r\n";
			if ($part->getContentTransferEncoding()) {
				$content .= "content-transfer-encoding: ".$part->getContentTransferEncoding()."\r\n";
			}
			
			if ($part->getAdditionalHeaders()) {
				$content .= implode("\r\n", array_map(function ($item) {
					return implode(': ', $item);
				}, $part->getAdditionalHeaders()))."\r\n";
			}
			$content .= "\r\n";
			switch ($part->getContentTransferEncoding()) {
				case 'quoted-printable':
					$content .= quoted_printable_encode($part->getContent());
				break;
				case 'base64':
					$content .= chunk_split(base64_encode($part->getContent()));
				break;
				case '':
					$content .= $part->getContent();
				break;
				default:
					throw new \LogicException('Unreachable');
			}
			$content .= "\r\n";
		}
		$content .= "--".$this->boundary."--\r\n";
		
		return $content;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContent() {
		$content = "";
		$content .= StringUtil::wordwrap("This is a MIME encoded email. As you are seeing this your user agent does not support these.");
		$content .= "\r\n\r\n";
		
		$content .= $this->getConcatenatedParts($this->parts);
		
		return $content;
	}
	
	/**
	 * Adds a mime part to this email. Should be either \wcf\system\email\mime\TextMimePart
	 * or \wcf\system\email\mime\AttachmentMimePart.
	 * 
	 * @param	AbstractMimePart	$part
	 * @param	mixed			$data	Additional data, to be defined by child classes
	 * @throws	\InvalidArgumentException
	 * @throws	\DomainException
	 */
	public function addMimePart(AbstractMimePart $part, $data = null) {
		foreach ($part->getAdditionalHeaders() as $header) {
			$header[0] = mb_strtolower($header[0]);
			if ($header[0] == 'content-type' || $header[0] == 'content-transfer-encoding') {
				throw new \InvalidArgumentException("The header '".$header[0]."' may not be set. Use the proper methods.");
			}
			
			if (!StringUtil::startsWith($header[0], 'x-') && !StringUtil::startsWith($header[0], 'content-')) {
				throw new \DomainException("The header '".$header[0]."' may not be set. You may only set headers starting with 'X-' or 'Content-'.");
			}
		}
		
		switch ($part->getContentTransferEncoding()) {
			case 'base64':
			case 'quoted-printable':
			case '':
			break;
			default:
				throw new \DomainException("The Content-Transfer-Encoding '".$part->getContentTransferEncoding()."' may not be set. You may only use 'quoted-printable' or 'base64' or ''.");
		}
		
		$this->parts[$part] = $data;
	}
	
	/**
	 * Removes a mime part from this multipart part.
	 * 
	 * @param	AbstractMimePart	$part
	 */
	public function removeMimePart(AbstractMimePart $part) {
		$this->parts->detach($part);
	}
	
	/**
	 * Returns the stored mime parts of this multipart part.
	 * Note: The returned \SplObjectStorage is a clone of the internal one.
	 * Modifications will not reflect on this object.
	 *
	 * @return	\SplObjectStorage
	 */
	public function getMimeParts() {
		return clone $this->parts;
	}
}
