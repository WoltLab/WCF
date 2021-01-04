<?php
namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IAutoCompleteFormField` methods for text fields.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @since       5.4
 */
trait TTextAutoCompleteFormField {
	use TAutoCompleteFormField;
	
	/**
	 * @inheritDoc
	 */
	protected function getValidAutoCompleteTokens(): array {
		return [
			'name',
				'honorific-prefix',
				'given-name',
				'additional-name',
				'family-name',
				'honorific-suffix',
			'nickname',
			'organization-title',
			'organization',
			'address-line1',
			'address-line2',
			'address-line3',
			'address-level4',
			'address-level3',
			'address-level3',
			'address-level2',
			'address-level1',
			'country',
			'country-name',
			'postal-code',
			'cc-name',
				'cc-given-name',
				'cc-additional-name',
				'cc-family-name',
				'cc-number',
			'cc-csc',
			'cc-type',
			'transaction-currency',
			'language',
			'sex',
			'tel-country-code',
			'tel-national',
				'tel-area-code',
				'tel-local',
					'tel-local-prefix',
					'tel-local-suffix',
			'tel-extension',
		];
	}
}
