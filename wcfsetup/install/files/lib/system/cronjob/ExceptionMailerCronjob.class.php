<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\email\mime\PlainTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\language\LanguageFactory;
use wcf\system\registry\RegistryHandler;
use wcf\util\ExceptionLogUtil;
use wcf\util\StringUtil;

/**
 * Mails an Exception summary.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 * @since	5.2
 */
class ExceptionMailerCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$timestamp = RegistryHandler::getInstance()->get('com.woltlab.wcf', 'exceptionMailerTimestamp');
		$timestamp = max($timestamp, TIME_NOW - 86400 * 3);
		for ($it = $timestamp; $it < TIME_NOW; $it += 86400) {
			$files[gmdate('Y-m-d', $it)] = [];
		}
		$files[gmdate('Y-m-d', TIME_NOW)] = [];
		
		$seen = [];
		foreach ($files as $file => $value) {
			$path = WCF_DIR.'log/'.$file.'.txt';
			if (!file_exists($path)) {
				unset($files[$file]);
				continue;
			}
			// check log size (2MiB) to prevent resource exhaustion
			if (filesize($path) > 2 * (1 << 20)) {
				$files[$file] = [
					'verdict' => 'huge'
				];
				continue;
			}
			try {
				$exceptions = ExceptionLogUtil::splitLog(file_get_contents($path));
			}
			catch (\Exception $e) {
				$files[$file] = [
					'verdict' => 'corrupt'
				];
				continue;
			}
			
			$count = 0;
			$files[$file]['messages'] = [];
			foreach ($exceptions as $exception) {
				$exception = ExceptionLogUtil::parseException($exception);
				$message = $exception['message'];
				if ($exception['date'] < $timestamp) continue;
				
				$count++;
				if (!isset($seen[$message]) && count($files[$file]['messages']) < 3) {
					$files[$file]['messages'][] = StringUtil::truncate(preg_replace('/\s+/', ' ', $message), 140);
					$seen[$message] = true;
				}
			}
			if ($count == 0) {
				unset($files[$file]);
				continue;
			}
			
			$files[$file]['count'] = $count;
		}
		
		if (empty($files)) return;
		
		$language = LanguageFactory::getInstance()->getDefaultLanguage();
		
		$email = new Email();
		$email->addRecipient(new Mailbox(MAIL_ADMIN_ADDRESS, null, $language));
		$email->setSubject($language->getDynamicVariable('wcf.acp.exceptionLog.email.subject', [
			'date' => $timestamp
		]));
		$email->setBody(new PlainTextMimePart($language->getDynamicVariable('wcf.acp.exceptionLog.email.body', [
			'date' => $timestamp,
			'files' => $files
		])));
		$email->send();
		RegistryHandler::getInstance()->set('com.woltlab.wcf', 'exceptionMailerTimestamp', TIME_NOW);
	}
}
