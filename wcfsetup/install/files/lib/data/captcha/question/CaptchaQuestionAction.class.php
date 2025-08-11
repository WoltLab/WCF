<?php

namespace wcf\data\captcha\question;

use wcf\command\captcha\question\DisableCaptchaQuestion;
use wcf\command\captcha\question\EnableCaptchaQuestion;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TI18nDatabaseObjectAction;

/**
 * Executes captcha question-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<CaptchaQuestion, CaptchaQuestionEditor>
 */
class CaptchaQuestionAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TI18nDatabaseObjectAction;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.captcha.canManageCaptchaQuestion'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.captcha.canManageCaptchaQuestion'];

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getI18nSaveTypes(): array
    {
        return [
            'question' => 'wcf.captcha.question.question.question\d+',
            'answers' => 'wcf.captcha.question.answers.question\d+',
        ];
    }

    #[\Override]
    public function getLanguageCategory(): string
    {
        return 'wcf.captcha.question';
    }

    #[\Override]
    public function getPackageID(): int
    {
        return PACKAGE_ID;
    }

    #[\Override]
    public function update()
    {
        parent::update();

        foreach ($this->objects as $object) {
            $this->saveI18nValue($object->getDecoratedObject());
        }
    }

    #[\Override]
    public function create()
    {
        // Question column doesn't have a default value
        $this->parameters['data']['question'] = $this->parameters['data']['question'] ?? '';

        $captchaQuestion = parent::create();

        $this->saveI18nValue($captchaQuestion);

        return $captchaQuestion;
    }

    #[\Override]
    public function delete()
    {
        $returnValue = parent::delete();

        $this->deleteI18nValues();

        return $returnValue;
    }

    /**
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnableCaptchaQuestion` or `DisableCaptchaQuestion` commands instead.
     */
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled) {
                (new EnableCaptchaQuestion($editor->getDecoratedObject()))();
            } else {
                (new DisableCaptchaQuestion($editor->getDecoratedObject()))();
            }
        }
    }
}
