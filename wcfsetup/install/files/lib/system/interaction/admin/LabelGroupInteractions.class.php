<?php

namespace wcf\system\interaction\admin;

use wcf\acp\page\LabelListPage;
use wcf\data\DatabaseObject;
use wcf\data\label\group\LabelGroup;
use wcf\event\interaction\admin\LabelGroupInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Interaction provider for label groups.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class LabelGroupInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction('core/labels/groups/%s'),
            new class(
                "show-labels",
            ) extends AbstractInteraction {
                #[\Override]
                public function render(DatabaseObject $object): string
                {
                    \assert($object instanceof LabelGroup);
                    $href = LinkHandler::getInstance()->getControllerLink(
                        LabelListPage::class,
                        [
                            'filters' => [
                                'groupID' => $object->getObjectID(),
                            ],
                        ]
                    );
                    $title = WCF::getLanguage()->get("wcf.acp.label.group.showLabels");

                    return \sprintf('<a href="%s">%s</a>', StringUtil::encodeHTML($href), $title);
                }
            }
        ]);

        EventHandler::getInstance()->fire(
            new LabelGroupInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return LabelGroup::class;
    }
}
