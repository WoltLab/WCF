<?php
namespace wcf\system\email;
use wcf\system\exception\SystemException;

/**
 * Represents a RFC 5322 mailbox.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email
 * @category	Community Framework
 */
class Mailbox {
	/**
	 * The email address of this mailbox.
	 * @var	string
	 */
	protected $address;
	
	/**
	 * The human readable name of this mailbox.
	 * @var	string
	 */
	protected $name = null;
	
	/**
	 * Creates a new Mailbox.
	 * 
	 * @param	string	$address	email address of this mailbox
	 * @param	string	$name		human readable name of this mailbox (or null)
	 */
	public function __construct($address, $name = null) {
		if (!preg_match('(^'.EmailGrammar::getGrammar('addr-spec').'$)', $address)) {
			throw new SystemException("The given email address '".$address."' is invalid.");
		}
		
		$this->address = $address;
		$this->name = $name;
	}
	
	/**
	 * Returns the human readable name of this mailbox.
	 * 
	 * @return	string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Returns the email address of this mailbox.
	 * 
	 * @return	string
	 */
	public function getAddress() {
		return $this->address;
	}
	
	/**
	 * Returns a string representation for use in a RFC 5233 message.
	 * 
	 * @return	string
	 */
	public function __toString() {
		if ($this->name === null) {
			return $this->address;
		}
		
		$name = EmailGrammar::encodeHeader($this->name);
		return $name.' <'.$this->address.'>';
	}
}
