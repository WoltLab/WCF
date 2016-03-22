<?php
namespace wcf\system\background\job;
use wcf\system\email\transport\exception\PermanentFailure;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;

/**
 * Delivers the given email to the given mailbox.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.background.job
 * @category	Community Framework
 * @since	2.2
 */
class EmailDeliveryBackgroundJob extends AbstractBackgroundJob {
	/**
	 * email to send
	 * @var	\wcf\system\email\Email
	 */
	protected $email;
	
	/**
	 * recipient mailbox
	 * @var	\wcf\system\email\Mailbox
	 */
	protected $mailbox;
	
	/**
	 * instance of the default transport
	 * @var	\wcf\system\email\transport\EmailTransport
	 */
	protected static $transport = null;
	
	/**
	 * Creates the job using the given the email and the destination mailbox.
	 * 
	 * @param	\wcf\system\email\Email		$email
	 * @param	\wcf\system\email\Mailbox	$mailbox
	 * @see		\wcf\system\email\transport\EmailTransport
	 */
	public function __construct(Email $email, Mailbox $mailbox) {
		$this->email = $email;
		$this->mailbox = $mailbox;
	}
	
	/**
	 * Emails will be sent with an increasing timeout between the tries.
	 * 
	 * @return	int	5 minutes, 30 minutes, 2 hours.
	 */
	public function retryAfter() {
		switch ($this->getFailures()) {
			case 1:
				return 5 * 60;
			case 2:
				return 30 * 60;
			case 3:
				return 2 * 60 * 60;
		}
	}
	
	/**
	 * @see	\wcf\system\background\job\AbstractJob::perform();
	 */
	public function perform() {
		if (self::$transport === null) {
			$name = '\wcf\system\email\transport\\'.ucfirst(MAIL_SEND_METHOD).'EmailTransport';
			self::$transport = new $name();
		}
		
		try {
			self::$transport->deliver($this->email, $this->mailbox);
		}
		catch (PermanentFailure $e) {
			// no need for retrying. Eat Exception and log the error.
			\wcf\functions\exception\logThrowable($e);
		}
	}
}
