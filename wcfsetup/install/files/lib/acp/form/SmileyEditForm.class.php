<?php

namespace wcf\acp\form;

use wcf\acp\page\SmileyListPage;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyAction;
use wcf\form\AbstractForm;
use wcf\http\Helper;
use wcf\system\interaction\admin\SmileyInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\language\I18nHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the smiley edit form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SmileyEditForm extends SmileyAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.smiley.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.smiley.canManageSmiley'];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->smiley = Helper::fetchObjectFromQueryParameter(Smiley::class);
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        $this->smileyTitle = 'wcf.smiley.title' . $this->smiley->smileyID;
        if (I18nHandler::getInstance()->isPlainValue('smileyTitle')) {
            I18nHandler::getInstance()->remove($this->smileyTitle);
            $this->smileyTitle = I18nHandler::getInstance()->getValue('smileyTitle');
        } else {
            I18nHandler::getInstance()->save('smileyTitle', $this->smileyTitle, 'wcf.smiley', 1);
        }

        // update bbcode
        $this->objectAction = new SmileyAction([$this->smiley], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'smileyTitle' => $this->smileyTitle,
                'smileyCode' => $this->smileyCode,
                'aliases' => $this->aliases,
                'emoji' => $this->emoji,
                'smileyPath' => $this->smileyPath,
                'smileyPath2x' => $this->smileyPath2x,
                'showOrder' => $this->showOrder,
                'categoryID' => $this->categoryID ?: null,
            ]),
            'fileLocation' => $this->uploadedFilename !== '' ? \WCF_DIR . 'images/smilies/' . $this->uploadedFilename : '',
            'fileLocation2x' => $this->uploadedFilename2x !== '' ? \WCF_DIR . 'images/smilies/' . $this->uploadedFilename2x : '',
        ]);
        $this->objectAction->executeAction();

        $this->uploadedFilename = '';

        $this->saved();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            I18nHandler::getInstance()->setOptions('smileyTitle', 1, $this->smiley->smileyTitle, 'wcf.smiley.title\d+');
            $this->smileyTitle = $this->smiley->smileyTitle;

            $this->smileyCode = $this->smiley->smileyCode;
            $this->aliases = $this->smiley->aliases;
            $this->emoji = $this->smiley->emoji;
            $this->smileyPath = $this->smiley->smileyPath;
            $this->smileyPath2x = $this->smiley->smileyPath2x;
            $this->showOrder = $this->smiley->showOrder;
            $this->categoryID = $this->smiley->categoryID;
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        WCF::getTPL()->assign([
            'smiley' => $this->smiley,
            'action' => 'edit',
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new SmileyInteractions(),
                $this->smiley,
                LinkHandler::getInstance()->getControllerLink(SmileyListPage::class)
            ),
        ]);
    }
}
