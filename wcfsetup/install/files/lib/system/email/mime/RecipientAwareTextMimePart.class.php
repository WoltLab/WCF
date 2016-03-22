<?php
namespace wcf\system\email\mime;
use wcf\system\email\Mailbox;
use wcf\system\email\UserMailbox;
use wcf\system\WCF;

/**
 * Default implementation of a recipient aware TextMimePart.
 * 
 * This implementation generates the final content by passing the content
 * to a specified template. The language will be changed to the Mailbox' language,
 * before evaluating the template.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 * @since	2.2
 */
class RecipientAwareTextMimePart extends TextMimePart implements IRecipientAwareMimePart {
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
	 * Creates a new AbstractRecipientAwareTextMimePart.
	 * 
	 * @param	string	$content	Content of this text part (this is passed to the template).
	 * @param	string	$mimeType	Mime type to provide in the email. You *must* not provide a charset. UTF-8 will be used automatically.
	 * @param	string	$template	Template to evaluate
	 * @param	string	$application	Application of the template to evaluate (default: wcf)
	 */
	public function __construct($content, $mimeType, $template, $application = 'wcf') {
		parent::__construct($content, $mimeType);
		
		$this->template = $template;
		$this->application = $application;
	}
	
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
			WCF::setLanguage($this->mailbox->getLanguage()->languageID);
			
			return WCF::getTPL()->fetch($this->template, $this->application, [
				'content' => $this->content,
				'mimeType' => $this->mimeType,
				'mailbox' => $this->mailbox
			], true);
		}
		finally {
			WCF::setLanguage($language->languageID);
		}
	}
}
