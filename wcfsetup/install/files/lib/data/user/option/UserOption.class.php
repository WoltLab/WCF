<?php

namespace wcf\data\user\option;

use wcf\data\ITitledObject;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\system\form\option\formatter\WysiwygFormatter;
use wcf\system\form\option\FormOptionHandler;
use wcf\system\form\option\IFormOption;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Represents a user option.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   ?string $defaultValue       default value of the user option
 * @property-read   0|1     $required           is `1` if the user option has to be filled out, otherwise `0`
 * @property-read   0|1     $askDuringRegistration  is `1` if the user option will be shown during registration to be filled out, otherwise `0`
 * @property-read   int     $editable           setting for who can edit the user option, see `UserOption::EDITABILITY_*` constants
 * @property-read   int     $visible            setting for who can see the user option, see `UserOption::VISIBILITY_*` constants
 * @property-read   0|1     $searchable         is `1` if the user option can be searched, otherwise `0`
 * @property-read   0|1     $isDisabled         is `1` if the user option is disabled and thus neither shown nor editable, otherwise `0`
 * @property-read   0|1     $originIsSystem     is `1` if the user option was created by the system and not manually by an administrator, otherwise `0`
 * @property-read   ?string $labeledUrl         the url, if the option type is `labeledUrl`
 * @property-read   0|1     $showOnUserCard     is `1` if the user option should be displayed on the user card, otherwise `0`
 * @property-read   ?string $l10nIdentifier     base name of the language variable the localized title/description are derived from, `null` for options created by an administrator
 *
 * @phpstan-import-type L10nValue from L10nStorage
 * @property-read   ?string $configuration      JSON-encoded configuration information depending on the option type
 */
class UserOption extends Option implements ITitledObject
{
    /**
     * visible for no one (no valid bit)
     * @var int
     */
    const VISIBILITY_NONE = 0;

    /**
     * visible for the owner
     * @var int
     */
    const VISIBILITY_OWNER = 1;

    /**
     * visible for admins
     * @var int
     */
    const VISIBILITY_ADMINISTRATOR = 2;

    /**
     * visible for users
     * @var int
     */
    const VISIBILITY_REGISTERED = 4;

    /**
     * visible for guests
     * @var int
     */
    const VISIBILITY_GUEST = 8;

    /**
     * visible for all (no valid bit)
     * @var int
     */
    const VISIBILITY_ALL = 15;

    /**
     * editable for no one (no valid bit)
     * @var int
     */
    const EDITABILITY_NONE = 0;

    /**
     * editable for the owner
     * @var int
     */
    const EDITABILITY_OWNER = 1;

    /**
     * editable for admins
     * @var int
     */
    const EDITABILITY_ADMINISTRATOR = 2;

    /**
     * editable for all (no valid bit)
     * @var int
     */
    const EDITABILITY_ALL = 3;

    /**
     * editable for owner during registration
     * @var int
     */
    const EDITABILITY_OWNER_DURING_REGISTRATION = 4;

    /**
     * editable for owner during registration and admins (no valid bit)
     * @var int
     */
    const EDITABILITY_OWNER_DURING_REGISTRATION_AND_ADMINISTRATOR = 6;

    /**
     * Maps the legacy option types without an equivalent form option to the ids of the form
     * options with a compatible value format. Other legacy option types fall back to the text
     * form option.
     *
     * @var array<string, string>
     */
    private const FORM_OPTION_TYPES = [
        'birthday' => 'date',
    ];

    /**
     * Maps the legacy option types that are converted to form options.
     *
     * @var array<string, string>
     * @since 6.3
     */
    public const LEGACY_OPTION_TYPES = [
        'aboutMe' => 'wysiwyg',
        'checkboxes' => 'checkboxes',
        'message' => 'wysiwyg',
        'multiSelect' => 'checkboxes',
        'URL' => 'url',
    ];

    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'user_option';

    /**
     * option value
     * @var string
     */
    public $optionValue = '';

    /**
     * user object
     * @var ?User
     */
    public $user;

    #[\Override]
    public function getCollectionClassName(): string
    {
        return UserOptionCollection::class;
    }

    #[\Override]
    public function getCollection(): UserOptionCollection
    {
        $collection = parent::getCollection();
        if (!($collection instanceof UserOptionCollection)) {
            throw new \RuntimeException('Expected a UserOptionCollection.');
        }

        return $collection;
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'title');
    }

    /**
     * Returns the localized description of this option in the active user's
     * language.
     *
     * @since 6.3
     */
    public function getDescription(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'description');
    }

    /**
     * Returns the localized values of the given column as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if ($columnName !== 'title' && $columnName !== 'description') {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_user_option',
            'wcf1_user_option_l10n',
            'optionID',
            ['title', 'description'],
            'l10nIdentifier',
            [
                'title' => '',
                'description' => '.description',
            ],
            ['title' => 255],
        );
    }

    /**
     * Returns the identifier for the form field.
     *
     * @since 6.3
     */
    public function getIdentifier(): string
    {
        return "userOption{$this->optionID}";
    }

    /**
     * @since 6.3
     */
    public function getFormOption(): IFormOption
    {
        $formOption = FormOptionHandler::getInstance()->getOption($this->optionType);
        if ($formOption === null && isset(self::FORM_OPTION_TYPES[$this->optionType])) {
            $formOption = FormOptionHandler::getInstance()->getOption(self::FORM_OPTION_TYPES[$this->optionType]);
        }

        if ($formOption === null) {
            $formOption = FormOptionHandler::getInstance()->getOption('text');
            \assert($formOption !== null);
        }

        return $formOption;
    }

    /**
     * @return array<string, mixed>
     * @since 6.3
     */
    public function getConfiguration(): array
    {
        return ($this->configuration ?? '') !== '' ? \json_decode(
            $this->configuration,
            true,
            flags: \JSON_THROW_ON_ERROR
        ) : [];
    }

    /**
     * Sets target user object.
     *
     * @param User $user
     * @return void
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    #[\Override]
    public function isVisible()
    {
        if ($this->isDisabled !== 0) {
            return false;
        }

        // proceed if option is visible for all
        if (($this->visible & self::VISIBILITY_GUEST) !== 0) {
            return true;
        }

        // proceed if option is visible for registered users and current user is logged in
        if (($this->visible & self::VISIBILITY_REGISTERED) !== 0 && !WCF::getUser()->isGuest()) {
            return true;
        }

        // check admin permissions
        if (($this->visible & self::VISIBILITY_ADMINISTRATOR) !== 0) {
            if (WCF::getSession()->hasPermission('admin.general.canViewPrivateUserOptions')) {
                return true;
            }
        }

        // check owner state
        if (($this->visible & self::VISIBILITY_OWNER) !== 0) {
            if ($this->user !== null && $this->user->userID === WCF::getUser()->userID) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true iff this option is editable.
     *
     * @param bool $inRegistration True iff the user currently is in registration.
     * @return  bool
     */
    public function isEditable(bool $inRegistration = false)
    {
        if ($this->isDisabled !== 0) {
            return false;
        }

        // check admin permissions
        if (($this->editable & self::EDITABILITY_ADMINISTRATOR) !== 0) {
            if (WCF::getSession()->hasPermission('admin.general.canViewPrivateUserOptions')) {
                return true;
            }
        }

        // check owner state
        if (($this->editable & self::EDITABILITY_OWNER) !== 0) {
            if ($this->user === null || $this->user->userID === WCF::getUser()->userID) {
                return true;
            }
        }

        if ($inRegistration && ($this->editable & self::EDITABILITY_OWNER_DURING_REGISTRATION) !== 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns true iff this user option can be deleted.
     *
     * @return  bool
     */
    public function canDelete()
    {
        if ($this->originIsSystem !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Allows modifications of editable option.
     *
     * @return void
     */
    public function modifyEditableOption(int $editableOption)
    {
        $this->data['editable'] = $editableOption;
    }

    /**
     * Allows modifications of visible option.
     *
     * @return void
     */
    public function modifyVisibleOption(int $visibleOption)
    {
        $this->data['visible'] = $visibleOption;
    }

    public function setRequired(bool $isRequired): void
    {
        $this->data['required'] = $isRequired ? 1 : 0;
    }

    /**
     * Returns the formatted value of this option for the given user.
     *
     * @since 6.3
     */
    public function getFormattedValue(string $value, User $user): string
    {
        return match ($this->optionType) {
            'birthday' => $this->formatBirthday($value, $user),
            'wysiwyg' => $this->formatMessage($value, $user),
            'labeledUrl' => $this->formatLabeledUrl($value),
            default => $this->getFormOption()->getFormatter()->format(
                $value,
                WCF::getLanguage()->languageID,
                $this->getConfiguration()
            ),
        };
    }

    /**
     * Formats the birthday, the year and the age are only shown if the user
     * allows it or the active user can view private user options.
     */
    private function formatBirthday(string $value, User $user): string
    {
        $parts = \explode('-', $value);
        $birthdayYear = (int)$parts[0];
        $month = (int)($parts[1] ?? 0);
        $day = (int)($parts[2] ?? 0);
        if ($month === 0 || $day === 0) {
            return '';
        }

        $showYear = $birthdayYear !== 0
            && ((bool)$user->birthdayShowYear || WCF::getSession()->hasPermission('admin.general.canViewPrivateUserOptions'));

        $dateFormat = WCF::getLanguage()->get(DateUtil::DATE_FORMAT);
        if (!$showYear) {
            $dateFormat = \str_replace('Y', '', $dateFormat);
        }

        $date = new \DateTimeImmutable($value, WCF::getUser()->getTimeZone());
        $birthday = DateUtil::localizeDate($date->format($dateFormat), $dateFormat, WCF::getLanguage());

        if ($showYear) {
            $age = DateUtil::getAge($value);
            if ($age > 0) {
                $birthday .= ' (' . $age . ')';
            }
        }

        return $birthday;
    }

    private function formatLabeledUrl(string $value): string
    {
        $url = \sprintf($this->labeledUrl, \rawurlencode($value));
        if (!\preg_match('~^https?://~i', $url)) {
            return StringUtil::encodeHTML($value);
        }

        return StringUtil::getAnchorTag($url, $value, true, true);
    }

    private function formatMessage(string $value, User $user): string
    {
        $objectType = $this->messageObjectType ?: 'com.woltlab.wcf.user.aboutMe';

        // Load embedded objects by parsing the original message again because for the user options, there
        // is no central way to save the embedded objects in the database.
        $htmlInputProcessor = new HtmlInputProcessor();
        $htmlInputProcessor->setContext($objectType, $user->userID);
        $htmlInputProcessor->processIntermediate($value);
        $htmlInputProcessor->processEmbeddedContent($value, $objectType, $user->userID);
        MessageEmbeddedObjectManager::getInstance()->registerTemporaryMessage($htmlInputProcessor);

        return (new WysiwygFormatter($objectType, $user->userID))->format(
            $value,
            WCF::getLanguage()->languageID,
            []
        );
    }
}
