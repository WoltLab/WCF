<?php
namespace wcf\system\email;
use wcf\data\user\User;
use wcf\system\email\mime\HtmlTextMimePart;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\PlainTextMimePart;

/**
 * Simplifies creating and sending a new Email.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email
 * @since	3.0
 */
class SimpleEmail {
	/**
	 * the underlying email object
	 * @var	Email
	 */
	private $email = null;
	
	/**
	 * the text/plain version of the message body
	 * @var	PlainTextMimePart
	 */
	private $textPlain = null;
	
	/**
	 * the text/html version of the message body
	 * @var	HtmlTextMimePart
	 */
	private $textHtml = null;
	
	/**
	 * Creates the underlying Email object.
	 */
	public function __construct() {
		$this->email = new Email();
	}
	
	/**
	 * Sets the email's 'Subject'.
	 *
	 * @param	string	$subject
	 * @see	Email::setSubject()
	 */
	public function setSubject($subject) {
		$this->email->setSubject($subject);
	}
	
	/**
	 * Sets the recipient of this email.
	 * This method clears any previous recipient of
	 * the email.
	 * 
	 * @param	User		$user
	 */
	public function setRecipient(User $user) {
		if (!$user->userID) throw new \InvalidArgumentException('The $user must not be a guest');
		
		$recipients = $this->email->getRecipients();
		foreach ($recipients as $recipient) {
			$this->email->removeRecipient($recipient['mailbox']);
		}
		
		$this->email->addRecipient(new UserMailbox($user));
	}
	
	/**
	 * Sets the text/plain version of this message.
	 * An empty string clears this version (not recommended!).
	 * 
	 * @param	string	$message
	 * @see		PlainTextMimePart
	 */
	public function setMessage($message) {
		$this->textPlain = $message ? new PlainTextMimePart($message) : null;
		
		$this->fixBody();
	}
	
	/**
	 * Sets the text/html version of this message.
	 * An empty string clears this version.
	 * 
	 * @param	string	$message
	 * @see		HtmlTextMimePart
	 */
	public function setHtmlMessage($message) {
		$this->textHtml = $message ? new HtmlTextMimePart($message) : null;
		
		$this->fixBody();
	}
	
	/**
	 * Sets the proper email body based on $textHtml and $textPlain.
	 */
	private function fixBody() {
		$parts = [];
		if ($this->textHtml) $parts[] = $this->textHtml;
		if ($this->textPlain) $parts[] = $this->textPlain;
		
		$this->email->setBody(new MimePartFacade($parts));
	}
	
	/**
	 * Queues this email for delivery.
	 * 
	 * @see	Email::send()
	 */
	public function send() {
		$this->email->send();
	}
	
	/**
	 * Returns the underlying email object
	 * 
	 * @return	Email
	 */
	public function getEmail() {
		return $this->email;
	}
}
