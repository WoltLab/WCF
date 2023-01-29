<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;

/**
 * Shows the form to edit an existing captcha question.
 *
 * @property    CaptchaQuestion $formObject
 *
 * @author  Florian Gail, Matthias Schmidt
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CaptchaQuestionEditForm extends CaptchaQuestionAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.captcha.question.list';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    public function readParameters()
    {
        parent::readParameters();

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
            $this->formObject = new CaptchaQuestion($queryParameters['id']);

            if (!$this->formObject->getObjectID()) {
                throw new IllegalLinkException();
            }
        } catch (MappingError) {
            throw new IllegalLinkException();
        }
    }
}
