<?php

namespace wcf\data\ad;

use wcf\command\ad\DisableAd;
use wcf\command\ad\EnableAd;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\condition\ConditionHandler;

/**
 * Executes ad-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Ad, AdEditor>
 */
class AdAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.ad.canManageAd'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.ad.canManageAd'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'toggle', 'update', 'updatePosition'];

    /**
     * @inheritDoc
     * @return  Ad
     */
    public function create()
    {
        $showOrder = 0;
        if (isset($this->parameters['data']['showOrder'])) {
            $showOrder = $this->parameters['data']['showOrder'];
            unset($this->parameters['data']['showOrder']);
        }

        /** @var Ad $ad */
        $ad = parent::create();
        $adEditor = new AdEditor($ad);
        $adEditor->setShowOrder($showOrder);

        return new Ad($ad->adID);
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        ConditionHandler::getInstance()->deleteConditions('com.woltlab.wcf.condition.ad', $this->objectIDs);

        return parent::delete();
    }

    /**
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnableAd` or `DisableAd` commands instead.
     */
    public function toggle()
    {
        foreach ($this->objects as $adEditor) {
            if ($adEditor->isDisabled) {
                (new EnableAd($adEditor->getDecoratedObject()))();
            } else {
                (new DisableAd($adEditor->getDecoratedObject()))();
            }
        }
    }
}
