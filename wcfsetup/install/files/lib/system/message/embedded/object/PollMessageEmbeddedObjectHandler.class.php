<?php

namespace wcf\system\message\embedded\object;

use wcf\data\poll\PollList;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Parses standalone polls that are embedded via the `[poll]` bbcode.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class PollMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler
{
    #[\Override]
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData)
    {
        $pollIDs = [];
        foreach ($embeddedData['poll'] ?? [] as $attributes) {
            $pollID = (int)($attributes[0] ?? 0);
            if ($pollID > 0) {
                $pollIDs[] = $pollID;
            }
        }

        return \array_values(\array_unique($pollIDs));
    }

    #[\Override]
    public function loadObjects(array $objectIDs)
    {
        // The list shares a collection, which loads the options of all polls at once.
        $pollList = new PollList();
        $pollList->setObjectIDs($objectIDs);
        $pollList->readObjects();

        return $pollList->getObjects();
    }
}
