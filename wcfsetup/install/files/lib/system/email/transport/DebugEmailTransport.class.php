<?php
namespace wcf\system\email\transport;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\io\File;
use wcf\util\DateUtil;

/**
 * DebugEmailTransport is a debug implementation of an email transport which writes emails into
 * a log file.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Transport
 * @since	3.0
 */
class DebugEmailTransport implements IEmailTransport {
	/**
	 * mbox file
	 * @var	File
	 */
	protected $mbox = null;
	
	/**
	 * Creates a new DebugTransport using the given mbox as target.
	 * 
	 * @param	string	$mbox	mbox location or null for default location
	 */
	public function __construct($mbox = null) {
		if ($mbox === null) $mbox = WCF_DIR.'log/debug.mbox';
		
		$this->mbox = new File($mbox, 'ab');
	}
	
	/**
	 * Writes the given $email into the mbox.
	 * 
	 * @param	Email		$email
	 * @param	Mailbox		$envelopeFrom
	 * @param	Mailbox		$envelopeTo
	 */
	public function deliver(Email $email, Mailbox $envelopeFrom, Mailbox $envelopeTo) {
		$this->mbox->write("From ".$envelopeFrom->getAddress()." ".DateUtil::getDateTimeByTimestamp(TIME_NOW)->format('D M d H:i:s Y')."\r\n");
		$this->mbox->write("Delivered-To: ".$envelopeTo->getAddress()."\r\n");
		$this->mbox->write($email->getEmail());
		$this->mbox->write("\r\n");
	}
}
