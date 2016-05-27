<?php
namespace wcf\data\notice;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of notices.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.notice
 * @category	Community Framework
 *
 * @method	Notice		current()
 * @method	Notice[]	getObjects()
 * @method	Notice|null	search($objectID)
 * @property	Notice[]	$objects
 */
class NoticeList extends DatabaseObjectList { }
