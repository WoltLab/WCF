<?php

namespace wcf\system\gridView\admin;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\I18nCronjobList;
use wcf\data\cronjob\log\CronjobLog;
use wcf\data\cronjob\log\CronjobLogList;
use wcf\data\DatabaseObject;
use wcf\event\gridView\admin\CronjobLogGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\IInteraction;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the cronjob log.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<CronjobLog, CronjobLogList>
 */
final class CronjobLogGridView extends AbstractGridView
{
    public function __construct()
    {
        $availableCronjobs = $this->getAvailableCronjobs();

        $this->addColumns([
            GridViewColumn::for('cronjobLogID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('cronjobID')
                ->label('wcf.acp.cronjob')
                ->sortable()
                ->titleColumn()
                ->filter(new SelectFilter(
                    $availableCronjobs,
                    'cronjobID',
                    'wcf.acp.cronjob'
                ))
                ->renderer([
                    new class($availableCronjobs) extends DefaultColumnRenderer {
                        /**
                         * @param array<int, string> $availableCronjobs
                         */
                        public function __construct(private readonly array $availableCronjobs) {}

                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            return $this->availableCronjobs[$value];
                        }
                    },
                ]),
            GridViewColumn::for('execTime')
                ->label('wcf.acp.cronjob.log.execTime')
                ->sortable()
                ->filter(TimeFilter::class)
                ->renderer(new TimeColumnRenderer()),
            GridViewColumn::for('success')
                ->label('wcf.acp.cronjob.log.status')
                ->sortable()
                ->filter(new SelectFilter(
                    [
                        1 => 'wcf.acp.cronjob.log.success',
                        0 => 'wcf.acp.cronjob.log.error',
                    ],
                    'success',
                    'wcf.acp.cronjob.log.status'
                ))
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof CronjobLog);

                            if ($row->success) {
                                return '<span class="badge green">' . WCF::getLanguage()->get('wcf.acp.cronjob.log.success') . '</span>';
                            }
                            if ($row->error) {
                                return '<span class="badge red">' . WCF::getLanguage()->get('wcf.acp.cronjob.log.error') . '</span>';
                            }

                            return '';
                        }
                    },
                ]),
        ]);

        $this->addQuickInteraction($this->getShowDetailsInteraction());
        $this->setDefaultSortField('execTime');
        $this->setDefaultSortOrder('DESC');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.management.canManageCronjob');
    }

    #[\Override]
    protected function createObjectList(): CronjobLogList
    {
        return new CronjobLogList();
    }

    #[\Override]
    protected function getInitializedEvent(): CronjobLogGridViewInitialized
    {
        return new CronjobLogGridViewInitialized($this);
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableCronjobs(): array
    {
        $list = new I18nCronjobList();
        $list->sqlOrderBy = 'descriptionI18n';
        $list->readObjects();

        return \array_map(fn(Cronjob $cronjob) => $cronjob->getDescription(), $list->getObjects());
    }

    private function getShowDetailsInteraction(): IInteraction
    {
        return new class(
            'showDetails',
            static fn(CronjobLog $object) => !!$object->error
        ) extends AbstractInteraction {
            #[\Override]
            public function render(DatabaseObject $object): string
            {
                \assert($object instanceof CronjobLog);

                $buttonLabel = WCF::getLanguage()->get('wcf.acp.cronjob.log.error.showDetails');
                $buttonId = 'cronjobLogErrorButton' . $object->cronjobLogID;
                $id = 'cronjobLogError' . $object->cronjobLogID;
                $error = StringUtil::encodeHTML($object->error);
                $dialogTitle = StringUtil::encodeJS(WCF::getLanguage()->get('wcf.acp.cronjob.log.error.details'));

                return <<<HTML
                    <button type="button" id="{$buttonId}" class="jsTooltip" title="{$buttonLabel}">
                        <fa-icon name="magnifying-glass"></fa-icon>
                    </button>
                    <template id="{$id}"><pre>{$error}</pre></template>
                    <script data-relocate="true">
                        require(['WoltLabSuite/Core/Component/Dialog'], ({ dialogFactory }) => {
                            document.getElementById('{$buttonId}').addEventListener('click', () => {
                                const dialog = dialogFactory().fromId('{$id}').withoutControls();
                                dialog.show('{$dialogTitle}');
                            });
                        });
                    </script>
                    HTML;
            }
        };
    }
}
