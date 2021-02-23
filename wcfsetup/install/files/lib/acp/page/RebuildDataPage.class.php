<?php

namespace wcf\acp\page;

use wcf\data\object\type\ObjectTypeCache;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Show the list of available rebuild data options.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Page
 */
class RebuildDataPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.maintenance.rebuildData';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canRebuildData'];

    /**
     * object types
     * @var array
     */
    public $objectTypes = [];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // get object types
        $this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.rebuildData');

        // sort object types
        \uasort($this->objectTypes, static function ($a, $b) {
            return ($a->nicevalue ?: 0) <=> ($b->nicevalue ?: 0);
        });
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'objectTypes' => $this->objectTypes,
        ]);
    }
}
