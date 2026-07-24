<?php

namespace wcf\data\captcha\question;

use wcf\command\captcha\question\DisableCaptchaQuestion;
use wcf\command\captcha\question\EnableCaptchaQuestion;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Executes captcha question-related actions.
 *
 * Captcha questions should be created and updated through the
 * `CreateCaptchaQuestion` and `UpdateCaptchaQuestion` commands, the `create`
 * and `update` actions are `@deprecated 6.3`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<CaptchaQuestion, CaptchaQuestionEditor>
 */
class CaptchaQuestionAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.captcha.canManageCaptchaQuestion'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.captcha.canManageCaptchaQuestion'];

    /**
     * @deprecated 6.3
     */
    #[\Override]
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnableCaptchaQuestion` or `DisableCaptchaQuestion` commands instead.
     */
    #[\Override]
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
