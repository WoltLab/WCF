<?php

namespace wcf\system\email;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Utils;
use wcf\data\language\Language;
use wcf\system\language\LanguageFactory;
use wcf\util\StringUtil;

/**
 * Represents a RFC 5322 mailbox.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class Mailbox
{
    /**
     * The email address of this mailbox.
     */
    protected string $address;

    /**
     * The human readable name of this mailbox.
     */
    protected ?string $name;

    /**
     * The preferred language of this mailbox.
     */
    protected int $languageID;

    /**
     * Creates a new Mailbox.
     *
     * @param $address email address of this mailbox
     * @param $name human readable name of this mailbox (or null)
     * @param $language Language to use for localization (or null for the default language)
     * @throws \DomainException
     */
    public function __construct(string $address, ?string $name = null, ?Language $language = null)
    {
        $this->address = self::filterAddress($address);
        $this->name = $name !== null ? StringUtil::trim($name) : null;
        if ($language === null) {
            $this->languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
        } else {
            $this->languageID = $language->languageID;
        }
    }

    /**
     * Splits the given address into localpart and domain, returning
     * a tuple.
     *
     * @return array{0: string, 1: string}
     * @throws \DomainException If the given address does not contain an '@'.
     * @since 6.0
     */
    private static function splitLocalpartAndDomain(string $address): array
    {
        // There could be multiple at-signs, but only in the localpart:
        //   Search for the last one.
        $atSign = \strrpos($address, '@');
        if ($atSign === false) {
            throw new \DomainException("The given email address '" . $address . "' does not contain an '@'.");
        }
        $localpart = \substr($address, 0, $atSign);
        $domain = \substr($address, $atSign + 1);

        return [$localpart, $domain];
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
        [$localpart, $domain] = self::splitLocalpartAndDomain($address);

        // We don't support SMTPUTF8
        for ($i = 0, $max = \strlen($localpart); $i < $max; $i++) {
            if (\ord($localpart[$i]) & 0b10000000) {
                throw new \DomainException(
                    "The localpart of the given email address '" . $address . "' contains 8-bit characters."
                );
            }
        }

        // punycode the domain ...
        try {
            $uri = (new Uri())->withHost($domain);
            $domain = Utils::idnUriConvert(
                $uri,
                \IDNA_DEFAULT | \IDNA_USE_STD3_RULES | \IDNA_CHECK_BIDI | \IDNA_CHECK_CONTEXTJ | \IDNA_NONTRANSITIONAL_TO_ASCII
            )->getHost();
        } catch (\InvalidArgumentException $e) {
            throw new \DomainException($e->getMessage(), 0, $e);
        }

        // ... and rebuild address.
        $address = $localpart . '@' . $domain;

        if (
            !\preg_match('(^' . EmailGrammar::getGrammar('addr-spec') . '$)', $address)
            // The addr-spec within a RFC 5322 message accepts CFWS, but SMTP does not. Including
            // CFWS in an email address does not make sense, thus we reject email addresses that
            // include CFWS, even if they technically match the addr-spec.
            || \preg_match('(' . EmailGrammar::getGrammar('CFWS') . ')', $address)
        ) {
            throw new \DomainException("The given email address '" . $address . "' is invalid.");
        }

        return $address;
    }

    /**
     * Returns the human readable name of this mailbox.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns the email address of this mailbox.
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Returns the properly encoded address for use within
     * the 'mailto' URI scheme.
     *
     * This method takes care to encode the localpart and domain
     * separately, leaving the '@' as-is.
     *
     * @since 6.0
     */
    public function getAddressForMailto(): string
    {
        [$localpart, $domain] = self::splitLocalpartAndDomain($this->getAddress());

        return \rawurlencode($localpart) . '@' . \rawurlencode($domain);
    }

    /**
     * Returns the language the recipient of this mailbox wants.
     * This is used for localization of the email template.
     */
    public function getLanguage(): Language
    {
        return
            LanguageFactory::getInstance()->getLanguage($this->languageID)
            ?: LanguageFactory::getInstance()->getDefaultLanguage();
    }

    /**
     * Returns a string representation for use in a RFC 5322 message.
     */
    public function __toString(): string
    {
        if (
            $this->name === null
            || $this->name === ''
            || $this->name === $this->address
        ) {
            return $this->address;
        }

        $name = EmailGrammar::encodeHeader($this->name);

        return $name . ' <' . $this->address . '>';
    }
}
