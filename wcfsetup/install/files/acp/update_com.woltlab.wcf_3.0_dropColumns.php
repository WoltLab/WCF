<?php
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
$data = <<<DATA
ALTER TABLE wcf1_session DROP COLUMN controller, DROP COLUMN parentObjectType, DROP COLUMN parentObjectID, DROP COLUMN objectType, DROP COLUMN objectID;
ALTER TABLE wcf1_user DROP COLUMN signatureEnableBBCodes, DROP COLUMN signatureEnableSmilies, DROP COLUMN socialNetworkPrivacySettings;
ALTER TABLE wcf1_user_avatar DROP COLUMN cropX, DROP COLUMN cropY;
DATA;

$lines = explode("\n", StringUtil::trim($data));

$rebuildData = WCF::getSession()->getVar('__wcfUpdateDropColumns');
if ($rebuildData === null) {
	$rebuildData = [
		'i' => 0,
		'max' => count($lines)
	];
}

// MySQL drops a column by creating a new table in the
// background, copying over all data except from the
// deleted column and uses this table afterwards.
// 
// Using a single `ALTER TABLE` to drop multiple columns
// results in the same runtime, because copying the table
// is what actually takes ages.
$statement = WCF::getDB()->prepareStatement(str_replace('wcf1_', 'wcf'.WCF_N.'_', $lines[$rebuildData['i']]));
$statement->execute();

$rebuildData['i']++;

if ($rebuildData['i'] === $rebuildData['max']) {
	WCF::getSession()->unregister('__wcfUpdateDropColumns');
}
else {
	WCF::getSession()->register('__wcfUpdateDropColumns', $rebuildData);
	
	// call this script again
	throw new SplitNodeException();
}
