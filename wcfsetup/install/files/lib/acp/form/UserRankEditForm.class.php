<?php

namespace wcf\acp\form;

use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\file\upload\UploadHandler;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the user rank edit form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserRankEditForm extends UserRankAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.rank.list';

    /**
     * rank id
     * @var int
     */
    public $rankID = 0;

    /**
     * rank object
     * @var UserRank
     */
    public $rank;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->rankID = \intval($_REQUEST['id']);
        }
        $this->rank = new UserRank($this->rankID);
        if (!$this->rank->rankID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $this->rankTitle = 'wcf.user.rank.userRank' . $this->rank->rankID;
        if (I18nHandler::getInstance()->isPlainValue('rankTitle')) {
            I18nHandler::getInstance()->remove($this->rankTitle);
            $this->rankTitle = I18nHandler::getInstance()->getValue('rankTitle');
        } else {
            I18nHandler::getInstance()->save('rankTitle', $this->rankTitle, 'wcf.user', 1);
        }

        // update label
        $this->objectAction = new UserRankAction([$this->rank], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'rankTitle' => $this->rankTitle,
                'cssClassName' => $this->cssClassName == 'custom' ? $this->customCssClassName : $this->cssClassName,
                'groupID' => $this->groupID,
                'requiredPoints' => $this->requiredPoints,
                'repeatImage' => $this->repeatImage,
                'requiredGender' => $this->requiredGender,
                'hideTitle' => $this->hideTitle,
            ]),
            'rankImageFile' => $this->rankImageFile,
            'rankImageFile__removedFiles' => $this->removedRankImages,
        ]);
        $this->objectAction->executeAction();
        $this->saved();

        // reset values if non-custom value was chosen
        if ($this->cssClassName != 'custom') {
            $this->customCssClassName = '';
        }

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            I18nHandler::getInstance()->setOptions('rankTitle', 1, $this->rank->rankTitle, 'wcf.user.rank.userRank\d+');
            $this->rankTitle = $this->rank->rankTitle;
            $this->cssClassName = $this->rank->cssClassName;
            if (!\in_array($this->cssClassName, $this->availableCssClassNames)) {
                $this->customCssClassName = $this->cssClassName;
                $this->cssClassName = 'custom';
            }
            $this->groupID = $this->rank->groupID;
            $this->requiredPoints = $this->rank->requiredPoints;
            $this->requiredGender = $this->rank->requiredGender;
            $this->repeatImage = $this->rank->repeatImage;
            $this->rankImage = $this->rank->rankImage;
            $this->hideTitle = $this->rank->hideTitle;

            if ($this->rank->getImageFile()) {
                UploadHandler::getInstance()->registerFilesByField('rankImage', [
                    $this->rank->getImageFile(),
                ]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        WCF::getTPL()->assign([
            'rankID' => $this->rankID,
            'rank' => $this->rank,
            'action' => 'edit',
        ]);
    }
}
