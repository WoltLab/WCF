<?php
namespace wcf\data\bbcode\media\provider;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of BBCode media providers.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011-2016 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode.media.provider
 * @category	Community Framework
 *
 * @method	BBCodeMediaProvider		current()
 * @method	BBCodeMediaProvider[]		getObjects()
 * @method	BBCodeMediaProvider|null	search($objectID)
 * @property	BBCodeMediaProvider[]		$objects
 */
class BBCodeMediaProviderList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = BBCodeMediaProvider::class;
}
