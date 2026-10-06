<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserEditForm;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\group\UserGroup;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\event\gridView\admin\UserGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\EmailColumnRenderer;
use wcf\system\gridView\renderer\IpAddressColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\interaction\admin\UserInteractions;
use wcf\system\interaction\bulk\admin\UserBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\option\user\TUserOptionsByCategory;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\view\filter\BooleanFilter;
use wcf\system\view\filter\exception\InvalidFilterValue;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\IpAddressFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\view\filter\UserAgeFilter;
use wcf\system\view\filter\UserOptionFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractGridView<User, UserList>
 */
final class UserGridView extends AbstractGridView
{
    use TUserOptionsByCategory;

    private bool $userStorageLoaded = false;

    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('userID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('avatar')
                ->label('wcf.user.avatar')
                ->renderer($this->getAvatarRenderer()),
            GridViewColumn::for('username')
                ->label('wcf.user.username')
                ->titleColumn()
                ->renderer($this->getUsernameRenderer())
                ->filter(TextFilter::class)
                ->sortable(),
        ]);

        if (WCF::getSession()->hasPermission('admin.user.canEditMailAddress')) {
            $this->addColumn(
                GridViewColumn::for('email')
                    ->label('wcf.user.email')
                    ->renderer(new EmailColumnRenderer())
                    ->filter(TextFilter::class)
                    ->sortable()
            );
        }

        $this->addColumn(
            GridViewColumn::for('registrationDate')
                ->label('wcf.user.registrationDate')
                ->renderer(new TimeColumnRenderer())
                ->filter(TimeFilter::class)
                ->sortable(defaultSortOrder: 'DESC')
        );

        if (WCF::getSession()->hasPermission('admin.user.canViewIpAddress')) {
            $this->addColumn(
                GridViewColumn::for('registrationIpAddress')
                    ->label('wcf.user.registrationIpAddress')
                    ->renderer(new IpAddressColumnRenderer())
                    ->filter(IpAddressFilter::class)
                    ->hidden()
            );
        }

        $this->addColumns([
            GridViewColumn::for('lastActivityTime')
                ->label('wcf.user.lastActivityTime')
                ->renderer($this->getLastActivityTimeRenderer())
                ->filter(TimeFilter::class)
                ->sortable(defaultSortOrder: 'DESC'),
            GridViewColumn::for('activityPoints')
                ->label('wcf.user.activityPoint')
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
                ->sortable(defaultSortOrder: 'DESC')
                ->hidden(),
            GridViewColumn::for('likesReceived')
                ->label('wcf.like.likesReceived')
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
                ->sortable(defaultSortOrder: 'DESC')
                ->hidden(),
            ...$this->getUserOptionColumns(),
        ]);

        $this->addAvailableFilters([
            $this->getGroupFilter(),
            new BooleanFilter('banned', 'wcf.acp.user.filter.banned'),
            $this->getIsDisabledFilter(),
            $this->getPendingActivationFilter(),
            $this->getEmailConfirmedFilter(),
            new BooleanFilter('disableAvatar', 'wcf.acp.user.filter.disableAvatar'),
            new BooleanFilter('disableSignature', 'wcf.acp.user.filter.disableSignature'),
            ...$this->getUserOptionFilters(),
        ]);

        $provider = new UserInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(UserEditForm::class, static fn(User $user) => $user->canEdit()),
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new UserBulkInteractions());

        $this->addRowLink(
            new GridViewRowLink(
                UserEditForm::class,
                isAvailableCallback: static fn(User $user) => $user->canEdit()
            )
        );

        $this->setColumnConfigurable();
        $this->setDefaultSortField('registrationDate');
        $this->setDefaultSortOrder('DESC');
        $this->setRowsPerPage(50);
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canSearchUser');
    }

    #[\Override]
    public function getRows(): array
    {
        $rows = parent::getRows();

        // The interactions check the groups of each user.
        if (!$this->userStorageLoaded) {
            $this->userStorageLoaded = true;
            if ($rows !== []) {
                UserStorageHandler::getInstance()->loadStorage(\array_keys($rows));
            }
        }

        return $rows;
    }

    #[\Override]
    protected function createObjectList(): UserList
    {
        $list = new UserList();

        // `UserList` only joins the user option values when reading the objects,
        // sorting by an option requires them when reading the ids as well.
        $sortColumn = $this->getColumn($this->getSortField());
        if ($sortColumn !== null && \str_starts_with($sortColumn->getSortByDatabaseColumn(), 'user_option_value.')) {
            $list->sqlConditionJoins .= "
                LEFT JOIN   wcf1_user_option_value user_option_value
                ON          user_option_value.userID = user_table.userID";
        }

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): UserGridViewInitialized
    {
        return new UserGridViewInitialized($this);
    }

    private function getAvatarRenderer(): AbstractColumnRenderer
    {
        return new class extends AbstractColumnRenderer {
            #[\Override]
            public function render(mixed $value, DatabaseObject $row): string
            {
                \assert($row instanceof User);

                return $row->getAvatar()->getImageTag(24);
            }

            #[\Override]
            public function getClasses(): string
            {
                return 'gridView__column--icon';
            }
        };
    }

    private function getUsernameRenderer(): DefaultColumnRenderer
    {
        return new class extends DefaultColumnRenderer {
            #[\Override]
            public function render(mixed $value, DatabaseObject $row): string
            {
                \assert($row instanceof User);

                $html = '<span class="username">' . StringUtil::encodeHTML($row->username) . '</span>';

                $statusIcons = '';
                if ($row->banned !== 0) {
                    $statusIcons .= $this->renderStatusIcon('lock', 'wcf.user.banned', $row);
                }
                if ($row->pendingActivation()) {
                    $statusIcons .= $this->renderStatusIcon('power-off', 'wcf.user.status.isDisabled', $row);

                    if ($row->getBlacklistMatches() !== []) {
                        $statusIcons .= $this->renderStatusIcon('warning', 'wcf.user.status.blacklistMatches', $row);
                    }
                }
                if ($statusIcons !== '') {
                    $html .= ' <span class="gridView__statusIcons">' . $statusIcons . '</span>';
                }

                if (\MODULE_USER_RANK !== 0) {
                    $rank = $row->getRank();
                    if ($row->getUserTitle() !== '') {
                        $html .= \sprintf(
                            ' <span class="badge userTitleBadge%s">%s</span>',
                            $rank !== null && $rank->cssClassName !== ''
                                ? ' ' . StringUtil::encodeHTML($rank->cssClassName)
                                : '',
                            StringUtil::encodeHTML($row->getUserTitle())
                        );
                    }
                    if ($rank !== null && $rank->rankImage !== '') {
                        $html .= ' <span class="userRankImage">' . $rank->getImage() . '</span>';
                    }
                }

                return $html;
            }

            private function renderStatusIcon(string $icon, string $languageItem, User $user): string
            {
                return \sprintf(
                    '<span class="jsTooltip" title="%s">%s</span>',
                    WCF::getLanguage()->getDynamicVariable($languageItem, ['user' => $user]),
                    FontAwesomeIcon::fromValues($icon)->toHtml()
                );
            }
        };
    }

    private function getLastActivityTimeRenderer(): TimeColumnRenderer
    {
        return new class extends TimeColumnRenderer {
            #[\Override]
            public function render(mixed $value, DatabaseObject $row): string
            {
                \assert($row instanceof User);

                if ($row->isOnline()) {
                    return WCF::getLanguage()->get('wcf.user.online')
                        . ' <span class="userOnlineIndicator" aria-hidden="true"></span>';
                }

                return parent::render($value, $row);
            }
        };
    }

    /**
     * Returns the hidden columns for the profile options, except for messages.
     * Unlike the members list, administrators can see every option regardless
     * of its visibility.
     *
     * @return list<GridViewColumn>
     */
    private function getUserOptionColumns(): array
    {
        $options = $this->getUserOptionsByCategory('profile', static function (UserOption $option) {
            return $option->optionType !== 'aboutMe'
                && $option->optionType !== 'message'
                && $option->validateOptions()
                && $option->validatePermissions();
        });

        $columns = [];
        foreach (\array_merge(...\array_values($options)) as $option) {
            $columns[] = GridViewColumn::for($option->getIdentifier())
                ->label('wcf.user.option.' . $option->optionName)
                ->renderer($this->getUserOptionRenderer($option))
                ->unsafeDisableEncoding()
                ->sortable(sortByDatabaseColumn: 'user_option_value.' . $option->getIdentifier())
                ->hidden();
        }

        return $columns;
    }

    private function getUserOptionRenderer(UserOption $option): DefaultColumnRenderer
    {
        return new class($option) extends DefaultColumnRenderer {
            public function __construct(private readonly UserOption $option) {}

            #[\Override]
            public function render(mixed $value, DatabaseObject $row): string
            {
                // The value is not encoded, because the formatters take care of it.
                $value = (string)$value;
                if ($value === '' || $value === '0000-00-00') {
                    return '';
                }

                return $this->option->getFormattedValue($value);
            }
        };
    }

    private function getGroupFilter(): SelectFilter
    {
        $options = [];
        foreach (UserGroup::getSortedAccessibleGroups([], [UserGroup::EVERYONE, UserGroup::GUESTS]) as $group) {
            $options[$group->groupID] = $group->getName();
        }

        return new class($options, 'groupID', 'wcf.user.group', '', false) extends SelectFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                if (!isset($this->options[$value])) {
                    throw new InvalidFilterValue(
                        "Invalid value '{$value}' for filter '{$this->id}' given."
                    );
                }

                $list->getConditionBuilder()->add(
                    "user_table.userID IN (
                        SELECT  userID
                        FROM    wcf1_user_to_group
                        WHERE   groupID = ?
                    )",
                    [(int)$value]
                );
            }

            #[\Override]
            public function renderValue(string $value): string
            {
                return $this->options[$value] ?? '';
            }
        };
    }

    private function getIsDisabledFilter(): BooleanFilter
    {
        return new class('isDisabled', 'wcf.acp.user.filter.isDisabled') extends BooleanFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                $list->getConditionBuilder()->add("user_table.activationCode <> ?", [0]);
            }
        };
    }

    /**
     * Matches the users that are awaiting the approval by an administrator,
     * see `UsersAwaitingApprovalAcpDashboardBox`.
     */
    private function getPendingActivationFilter(): BooleanFilter
    {
        return new class('pendingActivation', 'wcf.acp.user.filter.pendingActivation') extends BooleanFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                $list->getConditionBuilder()->add("user_table.banned = ?", [0]);
                $list->getConditionBuilder()->add("user_table.activationCode <> ?", [0]);
                if (((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER) !== 0) {
                    $list->getConditionBuilder()->add("user_table.emailConfirmed IS NULL");
                }
            }
        };
    }

    private function getEmailConfirmedFilter(): SelectFilter
    {
        return new class(
            [
                1 => 'wcf.acp.user.filter.emailConfirmed.yes',
                0 => 'wcf.acp.user.filter.emailConfirmed.no',
            ],
            'emailConfirmed',
            'wcf.acp.user.filter.emailConfirmed'
        ) extends SelectFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                if (!isset($this->options[$value])) {
                    throw new InvalidFilterValue(
                        "Invalid value '{$value}' for filter '{$this->id}' given."
                    );
                }

                if ($value === '1') {
                    $list->getConditionBuilder()->add("user_table.emailConfirmed IS NULL");
                } else {
                    $list->getConditionBuilder()->add("user_table.emailConfirmed IS NOT NULL");
                }
            }
        };
    }

    /**
     * Returns the filters for the profile options. Unlike the members list,
     * administrators can filter by every option regardless of its visibility.
     *
     * @return list<UserOptionFilter|UserAgeFilter>
     */
    private function getUserOptionFilters(): array
    {
        $options = $this->getUserOptionsByCategory('profile', static function (UserOption $option) {
            return $option->validateOptions()
                && $option->validatePermissions()
                && $option->getFormOption()->isFilterable();
        });

        return UserOptionFilter::createFilters(
            \array_merge(...\array_values($options)),
            respectBirthdayYearPrivacy: false
        );
    }
}
