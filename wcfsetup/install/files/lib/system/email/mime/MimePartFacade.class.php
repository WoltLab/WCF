<?php
namespace wcf\system\email\mime;
use wcf\system\email\Mailbox;

/**
 * This facade eases creating a "standard" email
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Mime
 * @since	3.0
 */
class MimePartFacade extends AbstractMimePart implements IRecipientAwareMimePart {
	/**
	 * the mime part to provide in the email
	 * @var	AbstractMimePart
	 */
	protected $mimePart;
	
	/**
	 * Creates a new MimePartFacade.
	 * 
	 * @see		MultipartAlternativeMimePart
	 * @see		MultipartMixedMimePart
	 * @param	AbstractMimePart[]	$texts		Versions of the text part in descending priority (i.e. inside multipart/alternative)
	 * @param	AbstractMimePart[]	$attachments	Attachments (i.e. inside multipart/mixed)
	 */
	public function __construct(array $texts, array $attachments = []) {
		if (count($texts) > 1) {
			$this->mimePart = new MultipartAlternativeMimePart();
			$priority = PHP_INT_MAX;
			foreach ($texts as $text) {
				$this->mimePart->addMimePart($text, $priority);
				$priority -= 1000;
			}
		}
		else {
			$this->mimePart = $texts[0];
		}
		
		if (!empty($attachments)) {
			$mixed = new MultipartMixedMimePart();
			$mixed->addMimePart($this->mimePart);
			foreach ($attachments as $attachment) {
				$mixed->addMimePart($attachment);
			}
			$this->mimePart = $mixed;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function setRecipient(Mailbox $mailbox = null) {
		if ($this->mimePart instanceof IRecipientAwareMimePart) {
			$this->mimePart->setRecipient($mailbox);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContentType() {
		return $this->mimePart->getContentType();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContentTransferEncoding() {
		return $this->mimePart->getContentTransferEncoding();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContent() {
		return $this->mimePart->getContent();
	}
	
	/**
	 * Returns the inner mime part.
	 * 
	 * @return	AbstractMimePart
	 */
	public function getMimePart() {
		return $this->mimePart;
	}
}
