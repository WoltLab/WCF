<?php
namespace wcf\data\search\keyword;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit keywords.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	data.search.keyword
 * @category	Community Framework
 */
class SearchKeywordEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\search\keyword\SearchKeyword';
}
