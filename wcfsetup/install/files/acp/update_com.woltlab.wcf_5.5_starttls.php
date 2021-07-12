<?php

/**
 * Migrates away from MAIL_SMTP_STARTTLS = 'may'.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\data\option\OptionAction;
use wcf\util\StringUtil;

if (MAIL_SMTP_STARTTLS != 'may') {
    return;
}

$value = 'encrypt';
if (StringUtil::startsWith(MAIL_SMTP_HOST, 'ssl://')) {
    $value = 'none';
} elseif (MAIL_SMTP_PORT == 465) {
    $value = 'none';
}

$optionAction = new OptionAction([], 'import', [
    'data' => [
        'mail_smtp_starttls' => $value,
    ],
]);
$optionAction->executeAction();
