<?php

namespace wcf\data\captcha\question;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of captcha questions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<CaptchaQuestion>
 */
class CaptchaQuestionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = CaptchaQuestion::class;
}
