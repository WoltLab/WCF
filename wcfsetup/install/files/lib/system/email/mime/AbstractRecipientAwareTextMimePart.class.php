<?php
namespace wcf\system\email\mime;
use wcf\system\email\Mailbox;
use wcf\system\email\UserMailbox;
use wcf\system\WCF;

/**
 * Abstract implementation of a recipient aware TextMimePart.
 * 
 * This implementation generates the final content by passing the content
 * to a specified template. If the recipient is a UserMailbox the language
 * will be changed to the user's interface language, before evaluating the
 * template.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 */
abstract class AbstractRecipientAwareTextMimePart extends TextMimePart implements IRecipientAwareMimePart {
	/**
	 * template to use for this email
	 * @var	string
	 */
	protected $template = '';
	
	/**
	 * application of this template
	 * @var	string
	 */
	protected $application = 'wcf';
	
	/**
	 * the recipient of the email containing this mime part
	 * @var	\wcf\system\email\Mailbox
	 */
	protected $mailbox = null;
	
	/**
	 * @see	\wcf\system\email\mime\IRecipientAwareMimePart::setRecipient()
	 */
	public function setRecipient(Mailbox $mailbox = null) {
		$this->mailbox = $mailbox;
	}
	
	/**
	 * @see	\wcf\system\email\mime\AbstractMimePart::getContent()
	 */
	public function getContent() {
		$language = WCF::getLanguage();
		
		try {
			if ($this->mailbox instanceof UserMailbox) {
				WCF::setLanguage($this->mailbox->getUser()->getLanguage()->languageID);
			}
			
			return WCF::getTPL()->fetch($this->template, $this->application, [
				'content' => $this->content,
				'mailbox' => $this->mailbox
			], true);
		}
		finally {
			WCF::setLanguage($language->languageID);
		}
	}
}
