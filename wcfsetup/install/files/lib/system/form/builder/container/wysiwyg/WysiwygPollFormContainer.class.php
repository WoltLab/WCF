<?php
namespace wcf\system\form\builder\container\wysiwyg;
use wcf\data\IPollContainer;
use wcf\data\IStorableObject;
use wcf\data\poll\Poll;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\poll\PollOptionsFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IObjectTypeFormNode;
use wcf\system\form\builder\TObjectTypeFormNode;
use wcf\system\form\builder\TWysiwygFormNode;
use wcf\system\poll\IPollHandler;

/**
 * Represents the form container for the poll-related fields below a WYSIWYG editor.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Container\Wysiwyg
 * @since	5.2
 */
class WysiwygPollFormContainer extends FormContainer implements IObjectTypeFormNode {
	use TObjectTypeFormNode;
	use TWysiwygFormNode;
	
	/**
	 * form field to set the end date of the poll
	 * @var	DateFormField
	 */
	protected $endTimeField;
	
	/**
	 * form field to set if votes can be changed
	 * @var	BooleanFormField
	 */
	protected $isChangeableField;
	
	/**
	 * form field to set if the poll results are public
	 * @var	BooleanFormField
	 */
	protected $isPublicField;
	
	/**
	 * form field to set the maximum number of votes per user
	 * @var	IntegerFormField
	 */
	protected $maxVotesField;
	
	/**
	 * form field to set the available poll answers
	 * @var	PollOptionsFormField
	 */
	protected $optionsField;
	
	/**
	 * poll belonging to the edited object
	 * @var	null|Poll
	 */
	protected $poll;
	
	/**
	 * form field to set the question of the poll
	 * @var	TextFormField
	 */
	protected $questionField;
	
	/**
	 * form field to set whether viewing the poll results requires voting
	 * @var	BooleanFormField
	 */
	protected $resultsRequireVoteField;
	
	/**
	 * form field to set whether the poll answers are sorted by votes when viewing the results
	 * @var	BooleanFormField
	 */
	protected $sortByVotesField;
	
	const FIELD_NAMES = [
		'endTime',
		'isChangeable',
		'isPublic',
		'maxVotes',
		'options',
		'question',
		'resultsRequireVote',
		'sortByVotes'
	];
	
	/**
	 * Returns form field to set the end date of the poll.
	 * 
	 * @return	DateFormField
	 * @throws	\BadMethodCallException		if the form field has not been populated yet/form has not been built yet
	 */
	public function getEndTimeField() {
		if ($this->endTimeField === null) {
			throw new \BadMethodCallException("Poll form field can only be requested after the form has been built.");
		}
		
		return $this->endTimeField;
	}
	
	/**
	 * Returns the form field to set if votes can be changed.
	 * 
	 * @return	BooleanFormField
	 * @throws	\BadMethodCallException		if the form field has not been populated yet/form has not been built yet
	 */
	public function getIsChangeableField() {
		if ($this->isChangeableField === null) {
			throw new \BadMethodCallException("Poll form field can only be requested after the form has been built.");
		}
		
		return $this->isChangeableField;
	}
	
	/**
	 * Returns the form field to set if the poll results are public.
	 * 
	 * @return	BooleanFormField
	 * @throws	\BadMethodCallException		if the form field has not been populated yet/form has not been built yet
	 */
	public function getIsPublicField() {
		if ($this->isPublicField === null) {
			throw new \BadMethodCallException("Poll form field can only be requested after the form has been built.");
		}
		
		return $this->isPublicField;
	}
	
	/**
	 * Returns the form field to set the maximum number of votes per user.
	 * 
	 * @return	IntegerFormField
	 * @throws	\BadMethodCallException		if the form field has not been populated yet/form has not been built yet
	 */
	public function getMaxVotesField() {
		if ($this->maxVotesField === null) {
			throw new \BadMethodCallException("Poll form field can only be requested after the form has been built.");
		}
		
		return $this->maxVotesField;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeDefinition() {
		return 'com.woltlab.wcf.poll';
	}
	
	/**
	 * Returns the form field to set the available poll answers.
	 * 
	 * @return	PollOptionsFormField
	 * @throws	\BadMethodCallException		if the form field has not been populated yet/form has not been built yet
	 */
	public function getOptionsField() {
		if ($this->optionsField === null) {
			throw new \BadMethodCallException("Poll form field can only be requested after the form has been built.");
		}
		
		return $this->optionsField;
	}
	
	/**
	 * Returns the form field to set the question of the poll.
	 * 
	 * @return	TextFormField
	 * @throws	\BadMethodCallException		if the form field has not been populated yet/form has not been built yet
	 */
	public function getQuestionField() {
		if ($this->questionField === null) {
			throw new \BadMethodCallException("Poll form field can only be requested after the form has been built.");
		}
		
		return $this->questionField;
	}
	
	/**
	 * Returns the form field to set whether viewing the poll results requires voting.
	 * 
	 * @return	BooleanFormField
	 * @throws	\BadMethodCallException		if the form field has not been populated yet/form has not been built yet
	 */
	public function getResultsRequireVoteField() {
		if ($this->resultsRequireVoteField === null) {
			throw new \BadMethodCallException("Poll form field can only be requested after the form has been built.");
		}
		
		return $this->resultsRequireVoteField;
	}
	
	/**
	 * Returns the form field to set whether the poll answers are sorted by votes when viewing
	 * the results.
	 * 
	 * @return	BooleanFormField
	 * @throws	\BadMethodCallException		if the form field has not been populated yet/form has not been built yet
	 */
	public function getSortByVotesField() {
		if ($this->sortByVotesField === null) {
			throw new \BadMethodCallException("Poll form field can only be requested after the form has been built.");
		}
		
		return $this->sortByVotesField;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAvailable() {
		return parent::isAvailable() && $this->objectType !== null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function updatedObject(array $data, IStorableObject $object, $loadValues = true) {
		if ($loadValues && $object instanceof IPollContainer && $object->getPollID() !== null) {
			$this->poll = new Poll($object->getPollID());
			if (!$this->poll->pollID) {
				$this->poll = null;
			}
			else {
				// `isPublic` cannot be changed when editing polls
				$this->getIsPublicField()->available(false);
			}
			
			$this->getQuestionField()->value($this->poll->question);
			$this->getOptionsField()->value($this->poll->getOptions());
			$this->getEndTimeField()->value($this->poll->endTime);
			$this->getMaxVotesField()->value($this->poll->maxVotes);
			$this->getIsChangeableField()->value($this->poll->isChangeable);
			$this->getIsPublicField()->value($this->poll->isPublic);
			$this->getResultsRequireVoteField()->value($this->poll->resultsRequireVote);
			$this->getSortByVotesField()->value($this->poll->sortByVotes);
		}
		
		return parent::updatedObject($data, $object);
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		parent::populate();
		
		$id = $this->wysiwygId . 'Poll';
		
		// add data handler to group poll data into a sub-array of parameters
		$this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor($id, function(IFormDocument $document, array $parameters) use($id) {
			if (!$this->isAvailable()) {
				return $parameters;
			}
			
			$wysiwygId = $this->getWysiwygId();
			
			foreach (self::FIELD_NAMES as $fieldName) {
				$parameters[$wysiwygId . '_pollData'][$fieldName] = $parameters['data'][$id . '_' . $fieldName];
				unset($parameters['data'][$id . '_' . $fieldName]);
			}
			
			// this will always add a poll array to the parameters but
			// `PollManager::savePoll()` is capable of correctly detecting
			// when, based on the given data, nothing has to be done
			
			return $parameters;
		}));
		
		$this->questionField = TextFormField::create($id . '_question')
			->label('wcf.poll.question')
			->maximumLength(255);
		
		// if either options or question is given, the other must also be given
		$this->optionsField = PollOptionsFormField::create($id . '_options')
			->wysiwygId($this->getWysiwygId())
			->addValidator(new FormFieldValidator('empty', function(PollOptionsFormField $formField) use ($id) {
				/** @var TextFormField $questionFormField */
				$questionFormField = $formField->getDocument()->getNodeById($id . '_question');
				
				if (empty($formField->getValue()) && $questionFormField->getValue() !== '') {
					$formField->addValidationError(new FormFieldValidationError('empty'));
				}
				else if (!empty($formField->getValue()) && $questionFormField->getValue() === '') {
					$questionFormField->addValidationError(new FormFieldValidationError('empty'));
				}
			}));
		
		$this->endTimeField = DateFormField::create($id . '_endTime')
			->label('wcf.poll.endTime')
			->supportTime()
			->addValidator(new FormFieldValidator('futureTime', function(DateFormField $formField) use ($id) {
				$endTime = $formField->getSaveValue();
				
				if ($endTime && $endTime <= TIME_NOW) {
					if ($this->poll === null || $this->poll->endTime >= TIME_NOW) {
						$formField->addValidationError(new FormFieldValidationError(
							'invalid',
							'wcf.poll.endTime.error.invalid'
						));
					}
				}
			}));
		
		$this->maxVotesField = IntegerFormField::create($id . '_maxVotes')
			->label('wcf.poll.maxVotes')
			->minimum(1)
			->maximum(POLL_MAX_OPTIONS)
			->value(1);
		
		$this->isChangeableField = BooleanFormField::create($id . '_isChangeable')
			->label('wcf.poll.isChangeable');
		
		/** @var IPollHandler $pollHandler */
		$pollHandler = null;
		if ($this->objectType !== null) {
			$pollHandler = $this->getObjectType()->getProcessor();
		}
		
		$this->isPublicField = BooleanFormField::create($id . '_isPublic')
			->label('wcf.poll.isPublic')
			->available($pollHandler !== null && $pollHandler->canStartPublicPoll());
		
		$this->resultsRequireVoteField = BooleanFormField::create($id . '_resultsRequireVote')
			->label('wcf.poll.resultsRequireVote')
			->description('wcf.poll.resultsRequireVote.description');
		
		$this->sortByVotesField = BooleanFormField::create($id . '_sortByVotes')
			->label('wcf.poll.sortByVotes');
		
		$this->appendChildren([
			$this->getQuestionField(),
			$this->getOptionsField(),
			$this->getEndTimeField(),
			$this->getMaxVotesField(),
			$this->getIsChangeableField(),
			$this->getIsPublicField(),
			$this->getResultsRequireVoteField(),
			$this->getSortByVotesField()
		]);
	}
}
