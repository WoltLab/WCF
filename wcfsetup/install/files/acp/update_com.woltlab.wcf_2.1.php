<?php
use wcf\util\StringUtil;
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
	'wcf_uuid' => StringUtil::getUUID()
));

OptionEditor::resetCache();
