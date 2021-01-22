<?php

namespace wcf\system\user\multifactor\totp;

use ParagonIE\ConstantTime\Base32;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\util\CryptoUtil;

/**
 * Shows the TOTP secret as a QR code.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\System\User\Multifactor\Totp
 * @since   5.4
 */
class SecretFormField extends AbstractFormField
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = '__multifactorTotpSecretField';

    public function __construct()
    {
        $this->value(Totp::generateSecret());
    }

    /**
     * @inheritDoc
     */
    public function readValue(): self
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = CryptoUtil::getValueFromSignedString($this->getDocument()->getRequestData($this->getPrefixedId()));

            if ($value !== null) {
                $this->value = $value;
            }
        }

        return $this;
    }

    /**
     * Returns the encoded value for use within the QR code.
     */
    public function getEncodedValue(): string
    {
        return Base32::encodeUpperUnpadded($this->getValue());
    }

    /**
     * Returns the signed value for use within the hidden input.
     */
    public function getSignedValue(): string
    {
        return CryptoUtil::createSignedString($this->getValue());
    }

    /**
     * Returns a Totp handler for the field's secret.
     */
    public function getTotp(): Totp
    {
        return new Totp($this->getValue());
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId(): string
    {
        return 'secret';
    }
}
