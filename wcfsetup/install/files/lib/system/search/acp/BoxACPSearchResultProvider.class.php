<?php

namespace wcf\system\search\acp;

use wcf\acp\form\BoxEditForm;
use wcf\data\box\BoxList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for cms boxes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BoxACPSearchResultProvider implements IACPSearchResultProvider
{
    #[\Override]
    public function search(string $query)
    {
        if (!WCF::getSession()->hasPermission('admin.content.cms.canManageBox')) {
            return [];
        }

        $results = [];

        $boxList = new BoxList();
        $boxList->getConditionBuilder()->add('box.boxType <> ?', ['menu']);
        $boxList->getConditionBuilder()->add('box.name LIKE ?', ['%' . WCF::getDB()->escapeLikeValue($query) . '%']);
        $boxList->sqlLimit = 10;
        $boxList->sqlOrderBy = 'box.name';
        $boxList->readObjects();
        foreach ($boxList as $box) {
            $results[] = new ACPSearchResult($box->name, LinkHandler::getInstance()->getControllerLink(BoxEditForm::class, [
                'id' => $box->boxID,
            ]));
        }

        return $results;
    }
}
