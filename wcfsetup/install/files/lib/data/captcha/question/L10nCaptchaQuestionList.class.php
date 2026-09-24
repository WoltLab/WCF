<?php

namespace wcf\data\captcha\question;

use wcf\system\l10n\L10nStorage;

/**
 * List of captcha questions with localized question values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nCaptchaQuestionList extends CaptchaQuestionList
{
    public function __construct()
    {
        parent::__construct();

        $storage = new L10nStorage(CaptchaQuestion::getL10nDefinition());

        $this->sqlSelects .= (!empty($this->sqlSelects) ? ', ' : '')
            . $storage->getSubSelect('question', $this->getDatabaseTableAlias())
            . ' AS question';
    }
}
