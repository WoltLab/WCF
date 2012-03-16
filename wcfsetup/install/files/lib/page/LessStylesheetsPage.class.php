<?php
namespace wcf\page;
use wcf\data\application\Application;

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
					$application = new Application(1);
					$absolutePath = $application->domainName . $application->domainPath;
					$content = preg_replace('~url\(\'..\/~', 'url(\''.$absolutePath, $content);
					$content = preg_replace('~@import "([a-zA-Z]+)\.less";~', '@import "' . $absolutePath . 'style/$1.less";', $content);
					
					echo $content;
				}
			}
		}
	}
}
