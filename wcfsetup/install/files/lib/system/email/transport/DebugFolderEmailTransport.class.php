<?php
namespace wcf\system\email\transport;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\util\FileUtil;

/**
 * DebugFolderEmailTransport is a debug implementation of an email transport which writes emails into
 * a folder.
 * On unix-like operating systems the folder will be a valid Maildir.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Transport
 * @since	5.2
 */
class DebugFolderEmailTransport implements IEmailTransport {
	/**
	 * folder
	 * @var	string
	 */
	protected $folder = null;
	
	/**
	 * Creates a new DebugFolderTransport using the given folder as target.
	 * 
	 * @param	string	$folder	folder or null for default folder
	 */
	public function __construct($folder = null) {
		if ($folder === null) $folder = WCF_DIR.'log/Maildir';
		
		$this->folder = FileUtil::addTrailingSlash($folder);
		FileUtil::makePath($this->folder);
		if (PHP_EOL != "\r\n") {
			FileUtil::makePath($this->folder.'new');
			FileUtil::makePath($this->folder.'cur');
			FileUtil::makePath($this->folder.'tmp');
		}
	}
	
	/**
	 * Writes the given $email into the folder.
	 * 
	 * @param	Email		$email
	 * @param	Mailbox		$envelopeFrom
	 * @param	Mailbox		$envelopeTo
	 */
	public function deliver(Email $email, Mailbox $envelopeFrom, Mailbox $envelopeTo) {
		$eml = "Return-Path: <".$envelopeFrom->getAddress().">\r\n";
		$eml .= "Delivered-To: <".$envelopeTo->getAddress().">\r\n";
		$eml .= $email->getEmail();
		$eml .= "\r\n";
		$timestamp = explode(' ', microtime());
		$filename = $timestamp[1].'.M'.substr($timestamp[0], 2).'.eml';
		file_put_contents($this->folder.$filename, $eml);
		
		if (PHP_EOL != "\r\n") {
			@symlink('../'.$filename, $this->folder.'new/'.$filename);
		}
	}
}
