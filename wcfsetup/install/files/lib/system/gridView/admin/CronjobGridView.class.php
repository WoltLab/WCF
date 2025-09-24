<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\CronjobEditForm;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\I18nCronjobList;
use wcf\data\DatabaseObject;
use wcf\data\package\PackageCache;
use wcf\event\gridView\admin\CronjobGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\interaction\admin\CronjobInteractions;
use wcf\system\interaction\bulk\admin\CronjobBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\view\filter\I18nTextFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of cronjobs.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<Cronjob, I18nCronjobList>
 */
final class CronjobGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('cronjobID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('expression')
                ->label('wcf.acp.cronjob.expression')
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Cronjob);

                            return \sprintf('<kbd>%s</kbd>', $row->getExpression());
                        }
                    }
                ),
            GridViewColumn::for('description')
                ->label('wcf.acp.cronjob.description')
                ->sortable(sortByDatabaseColumn: 'descriptionI18n')
                ->filter(I18nTextFilter::class)
                ->renderer(new PhraseColumnRenderer())
                ->titleColumn(),
            GridViewColumn::for('packageID')
                ->label('wcf.acp.package.name')
                ->filter(new SelectFilter(
                    PackageCache::getInstance()->getPackages(),
                    'packageID',
                    'wcf.acp.package.name'
                ))
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Cronjob);

                            return StringUtil::encodeHTML($row->getPackage()->getTitle());
                        }
                    }
                )
                ->sortable(),
            GridViewColumn::for('nextExec')
                ->label('wcf.acp.cronjob.nextExec')
                ->renderer(
                    new class extends TimeColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Cronjob);

                            if ($row->isDisabled || $row->nextExec === 1) {
                                return '';
                            }

                            return parent::render($value, $row);
                        }
                    }
                )
                ->filter(TimeFilter::class)
                ->sortable(),
        ]);

        $interaction = new CronjobInteractions();
        $interaction->addInteractions([
            new Divider(),
            new EditInteraction(CronjobEditForm::class, static fn(Cronjob $cronjob) => $cronjob->isEditable()),
        ]);

        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/cronjobs/%s/enable',
                'core/cronjobs/%s/disable',
                isAvailableCallback: static fn(Cronjob $cronjob) => $cronjob->canBeDisabled()
            )
        );
        $this->setInteractionProvider($interaction);
        $this->setBulkInteractionProvider(new CronjobBulkInteractions());

        $this->addRowLink(new GridViewRowLink(CronjobEditForm::class));
        $this->setDefaultSortField('description');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.management.canManageCronjob');
    }

    #[\Override]
    protected function createObjectList(): I18nCronjobList
    {
        return new I18nCronjobList();
    }

    #[\Override]
    protected function getInitializedEvent(): CronjobGridViewInitialized
    {
        return new CronjobGridViewInitialized($this);
    }
}
