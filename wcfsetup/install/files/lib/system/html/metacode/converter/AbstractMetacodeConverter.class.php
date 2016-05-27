<?php
namespace wcf\system\html\metacode\converter;

/**
 * TOOD documentation
 * @since	2.2
 */
abstract class AbstractMetacodeConverter implements IMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		return true;
	}
}
