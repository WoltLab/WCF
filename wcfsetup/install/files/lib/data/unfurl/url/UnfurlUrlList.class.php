<?php

namespace wcf\data\unfurl\url;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of unfurled urls.
 *
 * @author 		Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license 	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package 	WoltLabSuite\Core\Data\Unfurl\Url
 * @since   	5.4
 *
 * @method	UnfurlUrl		current()
 * @method	UnfurlUrl[]		getObjects()
 * @method	UnfurlUrl|null	        search($objectID)
 * @property	UnfurlUrl[]	        $objects
 */
class UnfurlUrlList extends DatabaseObjectList
{
}
