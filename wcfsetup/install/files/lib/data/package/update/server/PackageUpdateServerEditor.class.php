<?php
namespace wcf\data\package\update\server;
use wcf\data\DatabaseObjectEditor;

/**
 * Contains business logic related to handling of package update servers.
 *
 * @author	Siegfried Schweizer
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update.server
 * @category	Community Framework
 */
class PackageUpdateServerEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\package\update\server\PackageUpdateServer';
}
