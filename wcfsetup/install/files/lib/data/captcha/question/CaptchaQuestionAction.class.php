<?php

namespace wcf\data\captcha\question;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\TI18nDatabaseObjectAction;

/**
 * Executes captcha question-related actions.
 *
 * @author  Florian Gail, Matthias Schmidt
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  CaptchaQuestionEditor[]     getObjects()
 * @method  CaptchaQuestionEditor       getSingleObject()
 */
class CaptchaQuestionAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TDatabaseObjectToggle;
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
     * @inheritDoc
     * @return  CaptchaQuestion
     */
    public function create()
    {
        $this->prefillI18nColumn('question', 'wcf.captcha.question.question.question\d+');

        /** @var CaptchaQuestion $object */
        $object = parent::create();

        $this->saveI18nColumn(
            'question',
            'wcf.captcha.question.question.question\d+',
            'wcf.captcha.question',
            1,
            $object
        );
        $this->saveI18nColumn(
            'answer',
            'wcf.captcha.question.answers.question\d+',
            'wcf.captcha.question',
            1,
            $object
        );

        return new CaptchaQuestion($object->getObjectID());
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        foreach ($this->getObjects() as $object) {
            $this->saveI18nColumn(
                'question',
                'wcf.captcha.question.question.question\d+',
                'wcf.captcha.question',
                1,
                $object
            );
            $this->saveI18nColumn(
                'answer',
                'wcf.captcha.question.answers.question\d+',
                'wcf.captcha.question',
                1,
                $object
            );
        }
    }
}
