<?php

namespace wcf\system\view\grid;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\I18nCronjobList;
use wcf\data\cronjob\log\CronjobLog;
use wcf\data\cronjob\log\CronjobLogList;
use wcf\data\DatabaseObjectList;
use wcf\system\view\grid\filter\SelectFilter;
use wcf\system\view\grid\renderer\DefaultColumnRenderer;
use wcf\system\view\grid\renderer\NumberColumnRenderer;
use wcf\system\view\grid\renderer\TimeColumnRenderer;
use wcf\system\view\grid\renderer\TitleColumnRenderer;
use wcf\system\WCF;
use wcf\util\StringUtil;

final class CronjobLogGridView extends DatabaseObjectListGridView
{
    #[\Override]
    protected function init(): void
    {
        $availableCronjobs = $this->getAvailableCronjobs();

        $this->addColumns([
            GridViewColumn::for('cronjobLogID')
                ->label('wcf.global.objectID')
                ->renderer(new NumberColumnRenderer())
                ->sortable(),
            GridViewColumn::for('cronjobID')
                ->label('wcf.acp.cronjob')
                ->sortable()
                ->filter(new SelectFilter($availableCronjobs))
                ->renderer([
                    new class($availableCronjobs) extends TitleColumnRenderer {
                        public function __construct(private readonly array $availableCronjobs) {}

                        public function render(mixed $value, mixed $context = null): string
                        {
                            return $this->availableCronjobs[$value];
                        }
                    },
                ]),
            GridViewColumn::for('execTime')
                ->label('wcf.acp.cronjob.log.execTime')
                ->sortable()
                // TODO: Add some time frame filter.
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
                        public function render(mixed $value, mixed $context = null): string
                        {
                            \assert($context instanceof CronjobLog);

                            if ($context->success) {
                                return '<span class="badge green">' . WCF::getLanguage()->get('wcf.acp.cronjob.log.success') . '</span>';
                            }
                            if ($context->error) {
                                $label = WCF::getLanguage()->get('wcf.acp.cronjob.log.error');
                                $buttonId = 'cronjobLogErrorButton' . $context->cronjobLogID;
                                $id = 'cronjobLogError' . $context->cronjobLogID;
                                $error = StringUtil::encodeHTML($context->error);
                                $dialogTitle = StringUtil::encodeJS(WCF::getLanguage()->get('wcf.acp.cronjob.log.error.details'));

                                return <<<HTML
                                    <button type="button" id="{$buttonId}" class="badge red">
                                        {$label}
                                    </button>
                                    <template id="{$id}">{$error}</template>
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

    private function getAvailableCronjobs(): array
    {
        $list = new I18nCronjobList();
        $list->sqlOrderBy = 'descriptionI18n';
        $list->readObjects();

        return \array_map(fn(Cronjob $cronjob) => $cronjob->getDescription(), $list->getObjects());
    }
}
