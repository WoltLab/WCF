<?php

/**
 * Create conditions for all boxes that use the legacy page filter.
 *
 * @author Joshua Ruesweg
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\data\condition\Condition;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\condition\page\MultiPageCondition;

$boxList = new BoxList();
$boxList->readObjects();

foreach ($boxList as $box) {
    $conditions = ConditionHandler::getInstance()->getConditions(
        Box::VISIBILITY_CONDITIONS_OBJECT_TYPE_NAME,
        $box->boxID
    );

    if (!empty($conditions)) {
        // The box already has conditions (maybe from a previous upgrade attempt).
        // Skip this box to ensure, that the condition will not attached twice.
        continue;
    }

    $pageCondition = ObjectTypeCache::getInstance()->getObjectTypeByName(
        Box::VISIBILITY_CONDITIONS_OBJECT_TYPE_NAME,
        'com.woltlab.wcf.page'
    );

    \assert($pageCondition->getProcessor() instanceof MultiPageCondition);

    $pageCondition->getProcessor()->setData(new Condition(null, [
        'conditionData' => \serialize([
            'pageIDs' => $box->getPageIDs(),
            'pageIDs_reverseLogic' => $box->visibleEverywhere,
        ]),
    ]));

    ConditionHandler::getInstance()->createConditions(
        $box->boxID,
        [$pageCondition]
    );
}
