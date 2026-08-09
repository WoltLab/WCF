<?php

namespace wcf\system\interaction\admin;

use wcf\data\DatabaseObject;
use wcf\data\package\Package;
use wcf\event\interaction\admin\PackageInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Interaction provider for packages.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class PackageInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (
            !WCF::getSession()->getPermission('admin.configuration.package.canUpdatePackage')
            && !WCF::getSession()->getPermission('admin.configuration.package.canInstallPackage')
        ) {
            return;
        }

        $this->addInteractions([
            new class(
                'uninstallation',
                static fn(Package $package) => $package->canUninstall()
            ) extends AbstractInteraction {
                #[\Override]
                public function render(DatabaseObject $object): string
                {
                    \assert($object instanceof Package);

                    $label = WCF::getLanguage()->get('wcf.acp.package.button.uninstall');
                    $confirmMessage = StringUtil::encodeHTML(WCF::getLanguage()->getDynamicVariable(
                        'wcf.acp.package.uninstallation.confirm',
                        ['package' => $object]
                    ));

                    return <<<HTML
                        <button type="button" class="jsUninstallButton" data-object-id="{$object->packageID}" data-confirm-message="{$confirmMessage}">
                            {$label}
                        </button>
                        HTML;
                }
            }
        ]);

        EventHandler::getInstance()->fire(
            new PackageInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Package::class;
    }
}
