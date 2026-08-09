<?php

namespace wcf\system\interaction\admin;

use wcf\acp\page\TemplateListPage;
use wcf\data\DatabaseObject;
use wcf\data\template\group\TemplateGroup;
use wcf\event\interaction\admin\TemplateGroupInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Interaction provider for template groups.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class TemplateGroupInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.template.canManageTemplate')) {
            return;
        }

        $this->addInteractions([
            new class("showTemplates") extends AbstractInteraction {
                #[\Override]
                public function render(DatabaseObject $object): string
                {
                    \assert($object instanceof TemplateGroup);

                    return \sprintf(
                        '<a href="%s">%s</a>',
                        StringUtil::encodeHTML(
                            LinkHandler::getInstance()->getControllerLink(TemplateListPage::class, [
                                "filters" => ['templateGroupID' => $object->templateGroupID,]
                            ])
                        ),
                        WCF::getLanguage()->get('wcf.acp.template.group.button.showTemplates')
                    );
                }
            },
            new DeleteInteraction("core/templates/groups/%s", static fn(TemplateGroup $group) => !$group->isImmutable())
        ]);

        EventHandler::getInstance()->fire(
            new TemplateGroupInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return TemplateGroup::class;
    }
}
