<?php

namespace wcf\system\view\grid;

use wcf\acp\form\UserRankEditForm;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankList;
use wcf\system\view\grid\renderer\DefaultColumnRenderer;
use wcf\system\view\grid\renderer\LinkColumnRenderer;
use wcf\system\view\grid\renderer\NumberColumnRenderer;
use wcf\system\view\grid\renderer\TitleColumnRenderer;
use wcf\system\WCF;
use wcf\util\StringUtil;

final class UserRankGridView extends DatabaseObjectListGridView
{
    protected string $objectListClassName = UserRankList::class;

    #[\Override]
    protected function init(): void
    {
        $this->addColumns([
            GridViewColumn::for('rankID')
                ->label('wcf.global.objectID')
                ->renderer(new NumberColumnRenderer())
                ->sortable(),
            GridViewColumn::for('rankTitle')
                ->label('wcf.acp.user.rank.title')
                ->sortable()
                ->renderer([
                    new class extends TitleColumnRenderer {
                        public function render(mixed $value, mixed $context = null): string
                        {
                            \assert($context instanceof UserRank);

                            return '<span class="badge label' . ($context->cssClassName ? ' ' . $context->cssClassName : '') . '">'
                                . StringUtil::encodeHTML($context->getTitle())
                                . '<span>';
                        }
                    },
                    new LinkColumnRenderer(UserRankEditForm::class, [], 'wcf.acp.user.rank.edit'),
                ]),
            GridViewColumn::for('rankImage')
                ->label('wcf.acp.user.rank.image')
                ->sortable()
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, mixed $context = null): string
                        {
                            \assert($context instanceof UserRank);

                            return $context->rankImage ? $context->getImage() : '';
                        }
                    },
                ]),
            GridViewColumn::for('groupID')
                ->label('wcf.user.group')
                ->sortable()
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, mixed $context = null): string
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
                        public function render(mixed $value, mixed $context = null): string
                        {
                            if (!$value) {
                                return '';
                            }

                            return WCF::getLanguage()->get(match ($value) {
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
                ->renderer(new NumberColumnRenderer()),
        ]);

        $this->setSortField('rankTitle');
    }

    public function isAccessible(): bool
    {
        return \MODULE_USER_RANK && WCF::getSession()->getPermission('admin.user.rank.canManageRank');
    }
}
