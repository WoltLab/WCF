<?php

namespace wcf\system\email;

use TrueBV\Exception\OutOfBoundsException;
use TrueBV\Punycode;
use wcf\data\language\Language;
use wcf\system\language\LanguageFactory;

/**
 * Represents a RFC 5322 mailbox.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Email
 * @since   3.0
 */
class Mailbox
{
    /**
     * The email address of this mailbox.
     * @var string
     */
    protected $address;

    /**
     * The human-readable name of this mailbox.
     * @var string
     */
    protected $name;

    /**
     * The preferred language of this mailbox.
     * @var int
     */
    protected $languageID;

    /**
     * Creates a new Mailbox.
     *
     * @param string $address email address of this mailbox
     * @param string $name human-readable name of this mailbox (or null)
     * @param Language $language Language to use for localization (or null for the default language)
     * @throws  \DomainException
     */
    public function __construct($address, $name = null, ?Language $language = null)
    {
        $this->address = self::filterAddress($address);
        $this->name = $name;
        if ($language === null) {
            $this->languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
        } else {
            $this->languageID = $language->languageID;
        }
    }

    /**
     * Preprocesses the given email address to improve compatibility for
     * IDN domains. The rewritten email address will be returned and an
     * exception will be thrown if the email address is invalid and cannot
     * be fixed.
     *
     * @since 5.5
     * @throws \DomainException If the given address is not valid.
     */
    public static function filterAddress(string $address): string
    {
        // There could be multiple at-signs, but only in the localpart:
        //   Search for the last one.
        $atSign = \strrpos($address, '@');
        if ($atSign === false) {
            throw new \DomainException("The given email address '" . $address . "' does not contain an '@'.");
        }
        $localpart = \substr($address, 0, $atSign);
        $domain = \substr($address, $atSign + 1);

        // We don't support SMTPUTF8
        for ($i = 0; $i < $atSign; $i++) {
            if (\ord($localpart[$i]) & 0b10000000) {
                throw new \DomainException(
                    "The localpart of the given email address '" . $address . "' contains 8-bit characters."
                );
            }
        }

        try {
            // punycode the domain ...
            $domain = @(new Punycode())->encode($domain);
        } catch (OutOfBoundsException $e) {
            throw new \DomainException($e->getMessage(), 0, $e);
        }

        // ... and rebuild address.
        $address = $localpart . '@' . $domain;

        if (!\preg_match('(^' . EmailGrammar::getGrammar('addr-spec') . '$)', $address)) {
            throw new \DomainException("The given email address '" . $address . "' is invalid.");
        }

        return $address;
    }

    /**
     * Returns the human-readable name of this mailbox.
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the email address of this mailbox.
     *
     * @return  string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Returns the language the recipient of this mailbox wants.
     * This is used for localization of the email template.
     *
     * @return  Language
     */
    public function getLanguage()
    {
        return LanguageFactory::getInstance()->getLanguage($this->languageID);
    }

    /**
     * Returns a string representation for use in an RFC 5322 message.
     *
     * @return  string
     */
    public function __toString()
    {
        if ($this->name === null || $this->name === $this->address) {
            return $this->address;
        }

        $name = EmailGrammar::encodeHeader($this->name);

        return $name . ' <' . $this->address . '>';
    }
}
