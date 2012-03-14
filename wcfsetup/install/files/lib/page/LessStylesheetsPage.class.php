<?php
namespace wcf\page;
use wcf\system\application\ApplicationHandler;

class LessStylesheetsPage extends AbstractPage {
	public function show() {
		if (defined('LESS_FILES') && LESS_FILES) {
			@header('Content-Type: text/css');
			
			foreach (explode("\n", LESS_FILES) as $stylesheet) {
				$path = WCF_DIR . 'style/'.trim($stylesheet).'.less';
				if (!file_exists($path)) {
					echo "\n\nFILE NOT FOUND: ".$path."\n\n\n";
				}
				else {
					$content = file_get_contents($path);
					
					// use absolute path for url()
					$application = ApplicationHandler::getInstance()->getApplication('wcf');
					$absolutePath = $application->domainName . $application->domainPath;
					$content = preg_replace('~url\(\'..\/~', 'url(\''.$absolutePath, $content);
					
					echo $content;
				}
			}
		}
	}
}
