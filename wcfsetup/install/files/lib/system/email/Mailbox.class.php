<?php
namespace wcf\system\email;
use wcf\data\language\Language;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;

/**
 * Represents a RFC 5322 mailbox.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email
 * @since	3.0
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
	 * @param	string		$address	email address of this mailbox
	 * @param	string		$name		human readable name of this mailbox (or null)
	 * @param	Language	$language	Language to use for localization (or null for the default language)
	 * @throws	\DomainException
	 */
	public function __construct($address, $name = null, Language $language = null) {
		if (!preg_match('(^'.EmailGrammar::getGrammar('addr-spec').'$)', $address)) {
			throw new \DomainException("The given email address '".$address."' is invalid.");
		}
		
		$this->address = $address;
		$this->name = $name;
		if ($language === null) {
			$language = LanguageFactory::getInstance()->getLanguage(LanguageFactory::getInstance()->getDefaultLanguageID());
		}
		$this->language = $language;
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
	 * Returns the language the recipient of this mailbox wants.
	 * This is used for localization of the email template.
	 * 
	 * @return	\wcf\data\language\Language
	 */
	public function getLanguage() {
		return $this->language;
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
