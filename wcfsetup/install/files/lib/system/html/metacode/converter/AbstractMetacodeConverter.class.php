<?php
namespace wcf\system\html\metacode\converter;

/**
 * TOOD documentation
 * @since	3.0
 */
abstract class AbstractMetacodeConverter implements IMetacodeConverter {
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		return true;
	}
}
