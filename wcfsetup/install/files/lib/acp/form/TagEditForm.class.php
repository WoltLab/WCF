<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\acp\page\TagListPage;
use wcf\data\tag\Tag;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\interaction\admin\TagInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the tag edit form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TagEditForm extends TagAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.tag.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.tag.canManageTag'];

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $this->formObject = new Tag($queryParameters['id']);

        if (!$this->formObject->getObjectID()) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new TagInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(TagListPage::class)
            ),
        ]);
    }
}
