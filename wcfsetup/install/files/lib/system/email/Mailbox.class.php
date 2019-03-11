<?php
namespace wcf\system\email;
use TrueBV\Punycode;
use wcf\data\language\Language;
use wcf\system\language\LanguageFactory;

/**
 * Represents a RFC 5322 mailbox.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
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
	 * The preferred language of this mailbox.
	 * @var	Language
	 */
	protected $language = null;
	
	/**
	 * Creates a new Mailbox.
	 * 
	 * @param	string		$address	email address of this mailbox
	 * @param	string		$name		human readable name of this mailbox (or null)
	 * @param	Language	$language	Language to use for localization (or null for the default language)
	 * @throws	\DomainException
	 */
	public function __construct($address, $name = null, Language $language = null) {
		// There could be multiple at-signs, but only in the localpart:
		//   Search for the last one.
		$atSign = strrpos($address, '@');
		if ($atSign === false) {
			throw new \DomainException("The given email address '".$address."' does not contain an '@'.");
		}
		$localpart = substr($address, 0, $atSign);
		$domain = substr($address, $atSign + 1);
		
		// We don't support SMTPUTF8
		for ($i = 0; $i < $atSign; $i++) {
			if (ord($localpart{$i}) & 0b10000000) {
				throw new \DomainException("The localpart of the given email address '".$address."' contains 8-bit characters.");
			}
		}
		
		// punycode the domain ...
		$domain = (new Punycode())->encode($domain);
		
		// ... and rebuild address.
		$address = $localpart.'@'.$domain;
		
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
	 * @return	Language
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
