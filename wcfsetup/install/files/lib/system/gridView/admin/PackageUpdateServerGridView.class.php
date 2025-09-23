<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\PackageUpdateServerEditForm;
use wcf\data\DatabaseObject;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\event\gridView\admin\PackageUpdateServerGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\admin\PackageUpdateServerInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\IInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\ObjectIdFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of package update servers.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<PackageUpdateServer, PackageUpdateServerList>
 */
final class PackageUpdateServerGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('packageUpdateServerID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(ObjectIdFilter::class)
                ->sortable(),
            GridViewColumn::for('serverURL')
                ->label('wcf.acp.updateServer.serverURL')
                ->titleColumn()
                ->sortable()
                ->filter(TextFilter::class),
            GridViewColumn::for('loginUsername')
                ->label('wcf.acp.updateServer.loginUsername')
                ->sortable()
                ->filter(TextFilter::class),
            GridViewColumn::for('packages')
                ->label('wcf.acp.updateServer.packages')
                ->renderer(new NumberColumnRenderer())
                ->filter(new IntegerFilter('packages', 'wcf.acp.updateServer.packages', $this->subSelectPackages()))
                ->sortable(sortByDatabaseColumn: $this->subSelectPackages()),
            GridViewColumn::for('status')
                ->label('wcf.acp.updateServer.status')
                ->filter(
                    new SelectFilter(
                        [
                            'online' => 'online',
                            'offline' => 'offline',
                        ],
                        'status',
                        'wcf.acp.updateServer.status'
                    )
                )
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof PackageUpdateServer);

                            return \sprintf(
                                '<span class="badge %s">%s</span>',
                                $row->status === 'online' ? "green" : "red",
                                $row->status
                            );
                        }
                    }
                )
                ->sortable(),
            GridViewColumn::for('lastUpdateTime')
                ->label('wcf.acp.updateServer.lastUpdateTime')
                ->renderer(new TimeColumnRenderer())
                ->filter(TimeFilter::class)
                ->sortable(),
        ]);

        $provider = new PackageUpdateServerInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(PackageUpdateServerEditForm::class),
        ]);
        $this->setInteractionProvider($provider);

        $this->addQuickInteraction($this->getShowErrorMessageInteraction());
        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/packages/updates/servers/%s/enable',
                'core/packages/updates/servers/%s/disable',
                isAvailableCallback: static fn(PackageUpdateServer $server) => $server->canDisable()
            )
        );

        $this->setDefaultSortField('serverURL');
        $this->addRowLink(new GridViewRowLink(PackageUpdateServerEditForm::class));
    }

    private function subSelectPackages(): string
    {
        return "(
            SELECT  COUNT(*)
            FROM    wcf1_package_update
            WHERE   packageUpdateServerID = package_update_server.packageUpdateServerID
        )";
    }

    private function getShowErrorMessageInteraction(): IInteraction
    {
        return new class('errorMessage') extends AbstractInteraction {
            #[\Override]
            public function isAvailable(DatabaseObject $object): bool
            {
                \assert($object instanceof PackageUpdateServer);

                return !empty($object->errorMessage);
            }

            #[\Override]
            public function render(DatabaseObject $object): string
            {
                \assert($object instanceof PackageUpdateServer);

                $buttonLabel = WCF::getLanguage()->get('wcf.acp.updateServer.showErrorMessage');
                $dialogTitle = StringUtil::encodeJS(WCF::getLanguage()->get('wcf.global.error.title'));
                $buttonId = 'packageUpdateServerErrorMessageButton' . $object->packageUpdateServerID;
                $id = 'packageUpdateServerErrorMessage' . $object->packageUpdateServerID;
                $errorMessage = StringUtil::encodeHTML($object->errorMessage);

                return <<<HTML
                    <button type="button" id="{$buttonId}" class="jsTooltip" title="{$buttonLabel}">
                        <fa-icon name="circle-exclamation"></fa-icon>
                    </button>
                    <template id="{$id}">
                        <p>{$errorMessage}</p>
                    </template>
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

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.configuration.package.canEditServer');
    }

    #[\Override]
    protected function createObjectList(): PackageUpdateServerList
    {
        return new PackageUpdateServerList();
    }

    #[\Override]
    protected function getInitializedEvent(): PackageUpdateServerGridViewInitialized
    {
        return new PackageUpdateServerGridViewInitialized($this);
    }
}
