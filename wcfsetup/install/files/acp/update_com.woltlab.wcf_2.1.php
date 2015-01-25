<?php
use wcf\data\option\OptionEditor;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */

$options = array(
	'cache_source_type' => (CACHE_SOURCE_TYPE == 'no' ? 'disk' : CACHE_SOURCE_TYPE), 
	'last_update_time' => TIME_NOW,
	'url_legacy_mode' => 1,
	'url_to_lowercase' => 0,
	'user_cleanup_notification_lifetime' => (USER_CLEANUP_NOTIFICATION_LIFETIME == 60 ? 14 : USER_CLEANUP_NOTIFICATION_LIFETIME),
	// the line below equals \wcf\util\StringUtil::getUUID(), but since we have to do it in one step, the "old" class exists in memory
	'wcf_uuid' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535))
);

// clear recaptcha keys if public key and private key match WoltLab's OEM key
if (RECAPTCHA_PUBLICKEY === '6LfOlMYSAAAAADvo3s4puBAYDqI-6YK2ybe7BJE5' && RECAPTCHA_PRIVATEKEY === '6LfOlMYSAAAAAKR3m_EFxmDv1xS8PCfeaSZ2LdG9') {
	$options['recaptcha_publickey'] = '';
	$options['recaptcha_privatekey'] = '';
}

OptionEditor::import($options);

OptionEditor::resetCache();
