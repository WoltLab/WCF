<?php
namespace wcf\system\request;
use wcf\system\SingletonFactory;

class CmsLinkHandler extends SingletonFactory {
	public function getLink($pageID, $languageID = -1) {
		return LinkHandler::getInstance()->getLink('Cms', [
			'application' => ''
		]);
	}
}
