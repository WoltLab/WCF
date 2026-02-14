<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\BBCodeEditForm;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeList;
use wcf\data\DatabaseObject;
use wcf\event\gridView\admin\BBCodeGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\BBCodeInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\view\filter\TextFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of bb codes.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<BBCode, BBCodeList>
 */
final class BBCodeGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('bbcodeID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('bbcodeTag')
                ->label('wcf.acp.bbcode.bbcodeTag')
                ->filter(TextFilter::class)
                ->titleColumn()
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            return \sprintf('[%s]', $value);
                        }
                    }
                )
                ->sortable(),
            GridViewColumn::for('className')
                ->label('wcf.acp.bbcode.className')
                ->filter(TextFilter::class)
                ->sortable(),
        ]);

        $provider = new BBCodeInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(BBCodeEditForm::class)
        ]);
        $this->setInteractionProvider($provider);

        $this->setDefaultSortField('bbcodeTag');
        $this->addRowLink(new GridViewRowLink(BBCodeEditForm::class));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.content.bbcode.canManageBBCode');
    }

    #[\Override]
    protected function createObjectList(): BBCodeList
    {
        return new BBCodeList();
    }

    #[\Override]
    protected function getInitializedEvent(): BBCodeGridViewInitialized
    {
        return new BBCodeGridViewInitialized($this);
    }
}
