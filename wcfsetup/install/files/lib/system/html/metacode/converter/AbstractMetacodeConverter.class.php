<?php
namespace wcf\system\html\metacode\converter;

abstract class AbstractMetacodeConverter implements IMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		return true;
	}
}
