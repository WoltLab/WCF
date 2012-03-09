<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;

class LessStylesheetsPage extends AbstractPage {
	public function show() {
		if (defined('LESS_FILES') && LESS_FILES) {
			@header('Content-Type: text/css');
			
			foreach (explode("\n", LESS_FILES) as $stylesheet) {
				$path = WCF_DIR . 'style/'.$stylesheet.'.less';
				if (!file_exists($path)) {
					echo "\n\nFILE NOT FOUND: ".$path."\n\n\n";
				}
				else {
					readfile($path);
				}
			}
		}
	}
}