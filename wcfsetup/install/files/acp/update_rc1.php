<?php
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */

// remove outdated language items
$conditions = new PreparedStatementConditionBuilder();
$conditions->add("languageItem IN (?)", array(array(
	'wcf.date.month.jan',
	'wcf.date.month.feb',
	'wcf.date.month.mar',
	'wcf.date.month.apr',
	// may is intentionally left out because it conflicts with the full month name
	'wcf.date.month.jun',
	'wcf.date.month.jul',
	'wcf.date.month.aug',
	'wcf.date.month.sep',
	'wcf.date.month.oct',
	'wcf.date.month.nov',
	'wcf.date.month.dec'
)));
$sql = "DELETE FROM	wcf".WCF_N."_language_item
	".$conditions;
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute($conditions->getParameters());
