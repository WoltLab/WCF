<?php

namespace wcf\data\user\option;

use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\data\user\User;
use wcf\system\form\option\formatter\WysiwygFormatter;
use wcf\system\form\option\FormOptionHandler;
use wcf\system\form\option\IFormOption;
use wcf\system\form\option\user\SocialMediaFormOption;
use wcf\system\form\option\user\TimezoneFormOption;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Represents a user option.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $optionID           unique id of the user option
 * @property-read   int     $packageID          id of the package which delivers the user option
 * @property-read   string  $optionName         name and textual identifier of the user option
 * @property-read   string  $categoryName       name of the user option category the user option belongs to
 * @property-read   string  $optionType         identifier of the form option type, see `UserOption::getFormOption()`
 * @property-read   ?string $defaultValue       default value of the user option
 * @property-read   ?string $validationPattern  regular expression used to validate the user option's value or empty if no such regular expression exists
 * @property-read   ?string $selectOptions      newline-separated list of selectable options in the legacy format (line pattern: `{value}:{label}`)
 * @property-read   0|1     $required           is `1` if the user option has to be filled out, otherwise `0`
 * @property-read   0|1     $askDuringRegistration  is `1` if the user option will be shown during registration to be filled out, otherwise `0`
 * @property-read   int     $editable           setting for who can edit the user option, see `UserOption::EDITABILITY_*` constants
 * @property-read   int     $visible            setting for who can see the user option, see `UserOption::VISIBILITY_*` constants
 * @property-read   0|1     $searchable         is `1` if the user option can be searched, otherwise `0`
 * @property-read   int     $showOrder          position of the user option in relation to the other user options in the category
 * @property-read   0|1     $isDisabled         is `1` if the user option is disabled and thus neither shown nor editable, otherwise `0`
 * @property-read   ?string $permissions        comma separated list of user group permissions of which the active user needs to have at least one to access the user option
 * @property-read   ?string $options            comma separated list of options of which at least one needs to be enabled to access the user option
 * @property-read   0|1     $originIsSystem     is `1` if the user option was created by the system and not manually by an administrator, otherwise `0`
 * @property-read   0|1     $showOnUserCard     is `1` if the user option should be displayed on the user card, otherwise `0`
 * @property-read   ?string $l10nIdentifier     base name of the language variable the localized title/description are derived from, `null` for options created by an administrator
 * @property-read   ?string $configuration      JSON-encoded configuration information depending on the option type
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class UserOption extends DatabaseObject implements ITitledObject
{
    use TDatabaseObjectOptions;
    use TDatabaseObjectPermissions;

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
        'labeledUrl' => 'socialMedia',
    ];

    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'user_option';

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
        $formOptions = self::getAvailableFormOptions();

        return $formOptions[$this->optionType]
            ?? $formOptions[self::FORM_OPTION_TYPES[$this->optionType] ?? '']
            ?? $formOptions['text'];
    }

    /**
     * Returns the form options available for user options, which includes form
     * options that are exclusive to user options.
     *
     * @return array<string, IFormOption>
     * @since 6.3
     */
    public static function getAvailableFormOptions(): array
    {
        static $formOptions = null;

        if ($formOptions === null) {
            $formOptions = FormOptionHandler::getInstance()->getOptions();

            foreach ([new SocialMediaFormOption(), new TimezoneFormOption()] as $formOption) {
                $formOptions[$formOption->getId()] = $formOption;
            }
        }

        return $formOptions;
    }

    /**
     * Returns the message object type used to process the values of WYSIWYG options.
     *
     * @since 6.3
     */
    public function getMessageObjectType(): string
    {
        if ($this->optionName === 'aboutMe') {
            return 'com.woltlab.wcf.user.aboutMe';
        }

        return 'com.woltlab.wcf.user.option.generic';
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
     * Returns true if the active user can see the value of this option of the
     * given user. Values of options that are only visible to their owner are
     * not visible if no user is given.
     */
    public function isVisible(?User $user = null): bool
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
            if ($user !== null && $user->userID === WCF::getUser()->userID) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the active user can edit this option of the given user.
     * Options that are only editable by their owner are editable if no user is
     * given, e.g. when creating a user.
     */
    public function isEditable(bool $inRegistration = false, ?User $user = null): bool
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
            if ($user === null || $user->userID === WCF::getUser()->userID) {
                return true;
            }
        }

        if ($inRegistration && ($this->editable & self::EDITABILITY_OWNER_DURING_REGISTRATION) !== 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if this user option can be deleted.
     */
    public function canDelete(): bool
    {
        if ($this->originIsSystem !== 0) {
            return false;
        }

        return true;
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

    private function formatMessage(string $value, User $user): string
    {
        $objectType = $this->getMessageObjectType();

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
