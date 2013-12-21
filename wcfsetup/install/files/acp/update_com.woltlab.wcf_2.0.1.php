<?php
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */

// disable APC if applicable
$sql = "UPDATE	wcf".WCF_N."_option
	SET	optionValue = ?
	WHERE	optionName = ?
		AND optionValue = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(
	'disk',
	'cache_source_type',
	'apc'
));
