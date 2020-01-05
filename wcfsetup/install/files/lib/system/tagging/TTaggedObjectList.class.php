<?php
namespace wcf\system\tagging;
use wcf\util\StringUtil;

/**
 * Helper functions to query tagged objects.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Tagging
 */
trait TTaggedObjectList {
	/**
	 * Processes the `$orderBy` parameters to inject the parameter into the `GROUP BY` parameter.
	 * 
	 * @param string $groupBy
	 * @param string $orderBy
	 * @return string
	 */
	protected function getGroupByFromOrderBy($groupBy, $orderBy) {
		if (!empty($orderBy)) {
			$orderBy = explode(',', $orderBy);
			$orderBy = array_map(function ($order) {
				return StringUtil::trim(preg_replace('~\s+(?:ASC|DESC)\s*$~i', '', $order));
			}, $orderBy);
			
			$orderBy = ', ' . implode(', ', $orderBy);
		}
		
		return "GROUP BY {$groupBy}{$orderBy}";
	}
}
