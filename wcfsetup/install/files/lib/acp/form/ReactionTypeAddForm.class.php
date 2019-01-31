<?php
namespace wcf\acp\form;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeAction;
use wcf\data\reaction\type\ReactionTypeEditor;
use wcf\data\reaction\type\ReactionTypeList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\file\upload\UploadFile;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\IsDisabledFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\UploadFormField;

/**
 * Represents the reaction type add form.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	5.2
 */
class ReactionTypeAddForm extends AbstractFormBuilderForm {
	/**
	 * @inheritDoc
	 */
	public $formAction = 'create';
	
	/**
	 * @inheritDoc
	 */
	public $objectActionClass = ReactionTypeAction::class;
	
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.reactionType.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.reaction.canManageReactionType'];
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_LIKE'];
	
	/**
	 * @var UploadFormField
	 */
	protected $uploadFormField;
	
	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();
		
		$dataContainer = FormContainer::create('generalSection')
			->appendChildren([
				TitleFormField::create()
					->required()
					->autoFocus()
					->maximumLength(255)
					->i18n()
					->languageItemPattern('wcf.reactionType.title\d+'),
				RadioButtonFormField::create('type')
					->label('wcf.acp.reactionType.type')
					->required()
					->options([
						ReactionType::REACTION_TYPE_POSITIVE => 'wcf.acp.reactionType.type.positive',
						ReactionType::REACTION_TYPE_NEUTRAL => 'wcf.acp.reactionType.type.neutral',
						ReactionType::REACTION_TYPE_NEGATIVE => 'wcf.acp.reactionType.type.negative'
					])
					->value(ReactionType::REACTION_TYPE_POSITIVE),
				ShowOrderFormField::create()
					->required()
					->options(new ReactionTypeList()),
				IsDisabledFormField::create()
					->label('wcf.acp.reactionType.isDisabled')
			]);
		
		$this->uploadFormField = UploadFormField::create('iconFile')
			->label('wcf.acp.reactionType.image')
			->description('wcf.acp.reactionType.image.description')
			->required()
			->maximum(1)
			->imageOnly(true)
			->allowSvgImage(true);
		
		$iconContainer = FormContainer::create('imageSection')
			->label('wcf.acp.reactionType.image')
			->appendChildren([
				$this->uploadFormField
			]);
		
		$this->form->appendChildren([
			$dataContainer, 
			$iconContainer
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function saved() {
		$this->saveImage($this->objectAction->getReturnValues()['returnValues']);
		
		parent::saved();
	}
	
	/**
	 * Save the image for a reaction type.
	 * 
	 * @param       ReactionType    $reactionType
	 */
	protected function saveImage(ReactionType $reactionType) {
		$files = $this->uploadFormField->getValue();
		
		/** @var UploadFile $file */
		$file = array_pop($files);
		if (!$file->isProcessed()) {
			$fileName = $reactionType->reactionTypeID . '-' . $file->getFilename();
			
			rename($file->getLocation(), WCF_DIR . '/images/reaction/' . $fileName);
			$file->setProcessed(WCF_DIR . '/images/reaction/' . $fileName);
			
			$reactionTypeEditor = new ReactionTypeEditor($reactionType);
			$reactionTypeEditor->update([
				'iconFile' => $fileName
			]);
		}
	}
}
