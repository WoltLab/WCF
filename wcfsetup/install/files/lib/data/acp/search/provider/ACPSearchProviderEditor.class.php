<?php
namespace wcf\data\acp\search\provider;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit acp search providers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.search.provider
 * @category	Community Framework
 * 
 * @method	ACPSearchProvider	getDecoratedObject()
 * @mixin	ACPSearchProvider
 */
class ACPSearchProviderEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ACPSearchProvider::class;
}
