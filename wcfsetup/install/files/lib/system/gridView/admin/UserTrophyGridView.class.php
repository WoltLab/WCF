<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserEditForm;
use wcf\acp\form\UserTrophyEditForm;
use wcf\data\DatabaseObject;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyList;
use wcf\event\gridView\admin\UserTrophyGridViewInitialized;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\filter\TimeFilter;
use wcf\system\gridView\filter\UserFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\interaction\admin\UserTrophyInteractions;
use wcf\system\interaction\bulk\admin\UserTrophyBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of user trophies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractGridView<UserTrophy, UserTrophyList>
 */
final class UserTrophyGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('userTrophyID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for("userID")
                ->label("wcf.user.username")
                ->titleColumn()
                ->filter(new UserFilter())
                ->renderer(new UserLinkColumnRenderer(UserEditForm::class))
                ->sortable(),
            GridViewColumn::for("image")
                ->label("wcf.acp.trophy")
                ->filter($this->getTrophySelectFilter())
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserTrophy);

                            return $row->getTrophy()->renderTrophy();
                        }

                        #[\Override]
                        public function getClasses(): string
                        {
                            return "gridView__column--icon";
                        }
                    }
                ),
            GridViewColumn::for("trophyID")
                ->label("wcf.global.title")
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserTrophy);

                            return StringUtil::encodeHTML($row->getTrophy()->getTitle());
                        }
                    }
                )
                ->sortable(),
            GridViewColumn::for('time')
                ->label('wcf.global.date')
                ->sortable()
                ->renderer(new TimeColumnRenderer())
                ->filter(new TimeFilter()),
        ]);

        $provider = new UserTrophyInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(
                UserTrophyEditForm::class,
                static fn(UserTrophy $userTrophy) => !$userTrophy->getTrophy()->awardAutomatically
            )
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new UserTrophyBulkInteractions());

        $this->addRowLink(
            new GridViewRowLink(
                UserTrophyEditForm::class,
                isAvailableCallback: static fn(UserTrophy $userTrophy) => !$userTrophy->getTrophy()->awardAutomatically
            )
        );

        $this->setDefaultSortField("time");
        $this->setDefaultSortOrder("DESC");
    }

    private function getTrophySelectFilter(): SelectFilter
    {
        return new class() extends SelectFilter {
            public function __construct()
            {
                parent::__construct([], "trophyID");
            }

            #[\Override]
            public function getFormField(string $id, string $label): SelectFormField
            {
                $options = [];
                foreach (TrophyCategoryCache::getInstance()->getCategories() as $category) {
                    $options[] = [
                        "value" => $category->categoryID,
                        "label" => $category->getTitle(),
                        "depth" => 0,
                        "isSelectable" => false,
                    ];

                    foreach ($category->getTrophies() as $trophy) {
                        $options[] = [
                            "value" => $trophy->trophyID,
                            "label" => $trophy->getTitle(),
                            "depth" => 1,
                            "isSelectable" => true,
                        ];
                    }
                }

                return SelectFormField::create($id)
                    ->label($label)
                    ->options($options, true, false);
            }

            #[\Override]
            public function renderValue(string $value): string
            {
                return TrophyCache::getInstance()->getTrophyByID(\intval($value))->getTitle();
            }
        };
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_TROPHY
            && WCF::getSession()->getPermission('admin.trophy.canAwardTrophy');
    }

    #[\Override]
    protected function createObjectList(): UserTrophyList
    {
        return new UserTrophyList();
    }

    #[\Override]
    protected function getInitializedEvent(): UserTrophyGridViewInitialized
    {
        return new UserTrophyGridViewInitialized($this);
    }
}
