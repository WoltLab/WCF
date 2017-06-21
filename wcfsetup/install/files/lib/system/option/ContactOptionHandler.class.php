<?php
namespace wcf\system\option;
use wcf\system\cache\builder\ContactOptionCacheBuilder;

class ContactOptionHandler extends CustomOptionHandler {
	/**
	 * @inheritDoc
	 */
	protected function readCache() {
		$this->cachedOptions = ContactOptionCacheBuilder::getInstance()->getData();
	}
}
