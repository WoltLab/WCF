<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserRankEditForm;
use wcf\data\DatabaseObject;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\L10nUserRankList;
use wcf\data\user\rank\UserRank;
use wcf\event\gridView\admin\UserRankGridViewInitialized;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\UserRankInteractions;
use wcf\system\interaction\bulk\admin\UserRankBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\L10nTextFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of user ranks.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<UserRank, L10nUserRankList>
 */
final class UserRankGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('rankID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('rankTitle')
                ->label('wcf.acp.user.rank.title')
                ->sortable(sortByDatabaseColumn: 'rankTitle')
                ->titleColumn()
                ->filter(new L10nTextFilter(
                    UserRank::getL10nDefinition(),
                    'rankTitle',
                    'rankTitle',
                    'wcf.acp.user.rank.title',
                ))
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserRank);

                            return '<span class="badge label' . ($row->cssClassName !== '' ? ' ' . $row->cssClassName : '') . '">'
                                . StringUtil::encodeHTML($value ?? '')
                                . '</span>';
                        }
                    }
                ]),
            GridViewColumn::for('rankImage')
                ->label('wcf.acp.user.rank.image')
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserRank);

                            return $row->getImage();
                        }

                        #[\Override]
                        public function prepare(mixed $value, DatabaseObject $row): void
                        {
                            \assert($row instanceof UserRank);

                            if ($row->rankImageFileID !== null) {
                                FileRuntimeCache::getInstance()->cacheObjectID($row->rankImageFileID);
                            }
                        }
                    },
                ]),
            GridViewColumn::for('groupID')
                ->label('wcf.user.group')
                ->sortable()
                ->filter(new SelectFilter(
                    $this->getAvailableUserGroups(),
                    'groupID',
                    'wcf.user.group'
                ))
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            return StringUtil::encodeHTML(UserGroup::getGroupByID($value)->getName());
                        }
                    },
                ]),
            GridViewColumn::for('requiredGender')
                ->label('wcf.user.option.gender')
                ->sortable()
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserRank);

                            if ($row->requiredGender === 0) {
                                return '';
                            }

                            return WCF::getLanguage()->get(match ($row->requiredGender) {
                                1 => 'wcf.user.gender.male',
                                2 => 'wcf.user.gender.female',
                                default => 'wcf.user.gender.other'
                            });
                        }
                    },
                ]),
            GridViewColumn::for('requiredPoints')
                ->label('wcf.acp.user.rank.requiredPoints')
                ->sortable()
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class),
        ]);

        $provider = new UserRankInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(UserRankEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new UserRankBulkInteractions());
        $this->addRowLink(new GridViewRowLink(UserRankEditForm::class));
        $this->setDefaultSortField('rankTitle');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_USER_RANK !== 0 && WCF::getSession()->hasPermission('admin.user.rank.canManageRank');
    }

    #[\Override]
    protected function createObjectList(): L10nUserRankList
    {
        return new L10nUserRankList();
    }

    #[\Override]
    protected function getInitializedEvent(): UserRankGridViewInitialized
    {
        return new UserRankGridViewInitialized($this);
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableUserGroups(): array
    {
        $groups = [];
        foreach (UserGroup::getSortedGroupsByType([], [UserGroup::GUESTS, UserGroup::EVERYONE]) as $group) {
            $groups[$group->groupID] = $group->getName();
        }

        return $groups;
    }
}
