<?php
namespace wcf\system\page\handler;
use wcf\data\trophy\category\TrophyCategory;

/**
 * Menu page handler for the trophy list page.
 *
 * @author	Joshua Rüsweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.1
 */
class TrophyListPageHandler extends AbstractLookupPageHandler {
	use TDecoratedCategoryOnlineLocationLookupPageHandler;
	
	/**
	 * @inheritDoc
	 */
	protected function getDecoratedCategoryClass() {
		return TrophyCategory::class;
	}
}
