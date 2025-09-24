<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\TemplateGroupEditForm;
use wcf\data\template\group\I18nTemplateGroupList;
use wcf\data\template\group\TemplateGroup;
use wcf\event\gridView\admin\TemplateGroupGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\interaction\admin\TemplateGroupInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\view\filter\I18nTextFilter;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of template groups.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<TemplateGroup, I18nTemplateGroupList>
 */
final class TemplateGroupGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('templateGroupID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer()),
            GridViewColumn::for('templateGroupName')
                ->label('wcf.global.name')
                ->titleColumn()
                ->filter(I18nTextFilter::class)
                ->renderer(new PhraseColumnRenderer())
                ->sortable(sortByDatabaseColumn: 'templateGroupNameI18n'),
            GridViewColumn::for('templateGroupFolderName')
                ->label('wcf.acp.template.group.folderName')
                ->filter(TextFilter::class)
                ->sortable(),
            GridViewColumn::for('templates')
                ->label('wcf.acp.template.group.templates')
                ->filter(new IntegerFilter(
                    'templates',
                    'wcf.acp.template.group.templates',
                    $this->subQueryTemplates(),
                ))
                ->sortable(sortByDatabaseColumn: $this->subQueryTemplates()),
            GridViewColumn::for('styles')
                ->label('wcf.acp.template.group.styles')
                ->filter(new IntegerFilter(
                    'styles',
                    'wcf.acp.template.group.styles',
                    $this->subQueryStyles(),
                ))
                ->sortable(sortByDatabaseColumn: $this->subQueryStyles()),
        ]);

        $provider = new TemplateGroupInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(
                TemplateGroupEditForm::class,
                static fn(TemplateGroup $group) => !$group->isImmutable()
            ),
        ]);
        $this->setInteractionProvider($provider);
        $this->addRowLink(new GridViewRowLink(TemplateGroupEditForm::class));

        $this->setDefaultSortField("templateGroupName");
    }

    private function subQueryTemplates(): string
    {
        return "(
            SELECT  COUNT(*)
            FROM    wcf1_template
            WHERE   templateGroupID = template_group.templateGroupID
        )";
    }

    private function subQueryStyles(): string
    {
        return "(
            SELECT  COUNT(*)
            FROM    wcf1_style
            WHERE   templateGroupID = template_group.templateGroupID
        )";
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission("admin.template.canManageTemplate");
    }

    #[\Override]
    protected function createObjectList(): I18nTemplateGroupList
    {
        $list = new I18nTemplateGroupList();
        if (!empty($list->sqlSelects)) {
            $list->sqlSelects .= ', ';
        }

        $list->sqlSelects .= $this->subQueryStyles() . ' AS styles, ' . $this->subQueryTemplates() . ' AS templates';

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): TemplateGroupGridViewInitialized
    {
        return new TemplateGroupGridViewInitialized($this);
    }
}
