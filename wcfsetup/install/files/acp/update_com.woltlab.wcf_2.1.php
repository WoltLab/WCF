<?php
use wcf\data\option\OptionEditor;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */

OptionEditor::updateAll(array(
	'cache_source_type' => (CACHE_SOURCE_TYPE == 'no' ? 'disk' : CACHE_SOURCE_TYPE), 
	'last_update_time' => TIME_NOW,
	'url_legacy_mode' => 1,
	// the line below equals \wcf\util\StringUtil::getUUID(), but since we have to do it in one step, the "old" class exists in memory
	'wcf_uuid' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535))
));

OptionEditor::resetCache();
