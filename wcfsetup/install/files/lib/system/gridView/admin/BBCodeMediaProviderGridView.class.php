<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\BBCodeMediaProviderEditForm;
use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderList;
use wcf\event\gridView\admin\BBCodeMediaProviderGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\BBCodeMediaProviderInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\view\filter\ObjectIdFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of bb code media providers.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<BBCodeMediaProvider, BBCodeMediaProviderList>
 */
final class BBCodeMediaProviderGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('providerID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(ObjectIdFilter::class)
                ->sortable(),
            GridViewColumn::for('title')
                ->label('wcf.acp.bbcode.mediaProvider.title')
                ->renderer(new DefaultColumnRenderer())
                ->titleColumn()
                ->filter(TextFilter::class)
                ->sortable(),
        ]);

        $provider = new BBCodeMediaProviderInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(BBCodeMediaProviderEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/bbcodes/media/providers/%s/enable',
                'core/bbcodes/media/providers/%s/disable'
            )
        );

        $this->addRowLink(new GridViewRowLink(BBCodeMediaProviderEditForm::class));
        $this->setDefaultSortField('title');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.content.bbcode.canManageBBCode');
    }

    #[\Override]
    protected function createObjectList(): BBCodeMediaProviderList
    {
        return new BBCodeMediaProviderList();
    }

    #[\Override]
    protected function getInitializedEvent(): BBCodeMediaProviderGridViewInitialized
    {
        return new BBCodeMediaProviderGridViewInitialized($this);
    }
}
