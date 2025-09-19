<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\AdEditForm;
use wcf\data\ad\Ad;
use wcf\data\ad\AdList;
use wcf\event\gridView\admin\AdGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\NumericFilter;
use wcf\system\gridView\filter\ObjectIdFilter;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\AdInteractions;
use wcf\system\interaction\bulk\admin\AdBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\WCF;

/**
 * Grid view for the list of ads.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractGridView<Ad, AdList>
 */
final class AdGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('adID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(new ObjectIdFilter())
                ->sortable(),
            GridViewColumn::for('adName')
                ->label('wcf.global.name')
                ->titleColumn()
                ->renderer(new DefaultColumnRenderer())
                ->filter(new TextFilter())
                ->sortable(),
            GridViewColumn::for('showOrder')
                ->label('wcf.global.showOrder')
                ->sortable()
                ->renderer(new NumberColumnRenderer())
                ->filter(new NumericFilter())
        ]);

        $provider = new AdInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(AdEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new AdBulkInteractions());

        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/ads/%s/enable',
                'core/ads/%s/disable',
            )
        );

        $this->setDefaultSortField("showOrder");
        $this->addRowLink(new GridViewRowLink(AdEditForm::class));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_WCF_AD
            && WCF::getSession()->getPermission('admin.ad.canManageAd');
    }

    #[\Override]
    protected function createObjectList(): AdList
    {
        return new AdList();
    }

    #[\Override]
    protected function getInitializedEvent(): AdGridViewInitialized
    {
        return new AdGridViewInitialized($this);
    }
}
