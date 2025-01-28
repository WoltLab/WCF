<?php

namespace wcf\system\gridView\admin;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\I18nCronjobList;
use wcf\data\cronjob\log\CronjobLog;
use wcf\data\cronjob\log\CronjobLogList;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\event\gridView\admin\CronjobLogGridViewInitialized;
use wcf\event\IPsr14Event;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\filter\TimeFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the cronjob log.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
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
                ->filter(new SelectFilter($availableCronjobs))
                ->renderer([
                    new class($availableCronjobs) extends DefaultColumnRenderer {
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
                ->filter(new TimeFilter())
                ->renderer(new TimeColumnRenderer()),
            GridViewColumn::for('success')
                ->label('wcf.acp.cronjob.log.status')
                ->sortable()
                ->filter(new SelectFilter([
                    1 => 'wcf.acp.cronjob.log.success',
                    0 => 'wcf.acp.cronjob.log.error',
                ]))
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof CronjobLog);

                            if ($row->success) {
                                return '<span class="badge green">' . WCF::getLanguage()->get('wcf.acp.cronjob.log.success') . '</span>';
                            }
                            if ($row->error) {
                                $label = WCF::getLanguage()->get('wcf.acp.cronjob.log.error');
                                $buttonId = 'cronjobLogErrorButton' . $row->cronjobLogID;
                                $id = 'cronjobLogError' . $row->cronjobLogID;
                                $error = StringUtil::encodeHTML($row->error);
                                $dialogTitle = StringUtil::encodeJS(WCF::getLanguage()->get('wcf.acp.cronjob.log.error.details'));

                                return <<<HTML
                                    <button type="button" id="{$buttonId}" class="badge red">
                                        {$label}
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

                            return '';
                        }
                    },
                ]),
        ]);

        $this->setSortField('execTime');
        $this->setSortOrder('DESC');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.management.canManageCronjob');
    }

    #[\Override]
    protected function createObjectList(): DatabaseObjectList
    {
        return new CronjobLogList();
    }

    #[\Override]
    protected function getInitializedEvent(): ?IPsr14Event
    {
        return new CronjobLogGridViewInitialized($this);
    }

    private function getAvailableCronjobs(): array
    {
        $list = new I18nCronjobList();
        $list->sqlOrderBy = 'descriptionI18n';
        $list->readObjects();

        return \array_map(fn(Cronjob $cronjob) => $cronjob->getDescription(), $list->getObjects());
    }
}
