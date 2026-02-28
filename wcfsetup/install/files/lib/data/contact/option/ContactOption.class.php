<?php

namespace wcf\data\contact\option;

use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\form\option\FormOptionHandler;
use wcf\system\form\option\IFormOption;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Represents a contact option.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.1
 *
 * @property-read   int     $optionID           unique id of the option
 * @property-read   string  $optionTitle        title of the option or name of language item which contains the title
 * @property-read   ?string $optionDescription  description of the option or name of language item which contains the description
 * @property-read   string  $optionType         type of the option which determines its input and output
 * @property-read   ?string $configuration      JSON-encoded configuration information depending on the option type
 * @property-read   int     $showOrder          position of the option in relation to the other options
 * @property-read   0|1     $isDisabled         is `1` if the option is disabled, otherwise `0`
 * @property-read   0|1     $originIsSystem     is `1` if the option has been delivered by a package, otherwise `0` (i.e. the option has been created in the ACP)
 */
class ContactOption extends DatabaseObject implements ITitledObject
{
    #[\Override]
    public static function getDatabaseTableAlias()
    {
        return 'contact_option';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->optionTitle);
    }

    /**
     * Returns the option description in the active user's language.
     *
     * @since   5.2
     */
    public function getDescription(): string
    {
        return WCF::getLanguage()->get($this->optionDescription);
    }

    public function canDelete(): bool
    {
        return !$this->originIsSystem;
    }

    /**
     * @since 6.2
     */
    public function getFormOption(): IFormOption
    {
        $formOption = FormOptionHandler::getInstance()->getOption($this->optionType);
        if ($formOption === null) {
            throw new \BadMethodCallException("unknown form option type '{$this->optionType}'");
        }

        return $formOption;
    }

    /**
     * @return array<string, mixed>
     * @since 6.2
     */
    public function getConfiguration(): array
    {
        return $this->configuration ? JSON::decode($this->configuration) : [];
    }
}
