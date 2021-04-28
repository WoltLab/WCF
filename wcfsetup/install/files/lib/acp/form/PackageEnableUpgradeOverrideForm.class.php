<?php
namespace wcf\acp\form;

use wcf\data\package\Package;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\registry\RegistryHandler;

/**
 * Allows enabling the package upgrade override.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2021 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	5.3
 */
final class PackageEnableUpgradeOverrideForm extends AbstractFormBuilderForm {
	/**
	 * @inheritDoc
	 */
	public $formAction = 'enable';

	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.list';

	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canUpdatePackage'];

	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();

		$issues = $this->getIssuesPreventingUpgrade();

		if (empty($issues)) {
			$this->form->appendChildren([
				BooleanFormField::create('enable')
					->label('wcf.acp.package.enableUpgradeOverride.enable')
					->value(PackageUpdateServer::isUpgradeOverrideEnabled())
			]);
		}
		else {
			$this->form->addDefaultButton(false);
			$this->form->appendChildren([
				TemplateFormNode::create('issues')
					->templateName('packageEnableUpgradeOverrideIssues')
					->variables([
						'issues' => $issues
					]),
				new class extends AbstractFormField {
					// TODO: Replace this with RejectEverythingFormField in 5.4+.
					public function __construct() {
						$this->id('rejectEverything');
					}

					public function getFieldHtml() {
						return '';
					}

					public function getHtml() {
						return '';
					}

					public function readValue() {
						return $this;
					}

					public function validate() {
						$this->addValidationError(new FormFieldValidationError('rejectEverything'));
					}
				}
			]);
		}
	}

	private function getIssuesPreventingUpgrade() {
		return ['foo'];
	}

	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();

		$formData = $this->form->getData();
		if ($formData['data']['enable']) {
			RegistryHandler::getInstance()->set('com.woltlab.wcf', PackageUpdateServer::class . "\0upgradeOverride", \TIME_NOW);
		}
		else {
			RegistryHandler::getInstance()->delete('com.woltlab.wcf', PackageUpdateServer::class . "\0upgradeOverride");
		}

		$this->saved();
	}
}
