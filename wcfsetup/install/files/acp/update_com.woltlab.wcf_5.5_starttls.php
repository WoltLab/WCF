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
use wcf\system\email\Email;
use wcf\system\email\transport\exception\TransientFailure;
use wcf\system\io\RemoteFile;
use wcf\util\StringUtil;

if (MAIL_SMTP_STARTTLS != 'may') {
    return;
}

$value = 'encrypt';
if (StringUtil::startsWith(MAIL_SMTP_HOST, 'ssl://')) {
    // Anything using proper SSL can't use STARTTLS.
    $value = 'none';
} elseif (MAIL_SMTP_PORT == 465) {
    // Anything on port 465 must be using proper SSL.
    $value = 'none';
} elseif (MAIL_SEND_METHOD == 'smtp') {
    // For all the other configurations that use SMTP as the transport we
    // need to verify whether TLS works or not.

    $getCode = static function (RemoteFile $connection) {
        $code = null;
        do {
            $data = $connection->gets();
            if (\preg_match('/^(\d{3})([- ])(.*)$/', $data, $matches)) {
                if ($code === null) {
                    $code = \intval($matches[1]);
                }

                if ($code == $matches[1]) {
                    if ($matches[2] === ' ') {
                        return $code;
                    }
                } else {
                    throw new TransientFailure("Unexpected reply '" . $data . "' from SMTP server. Code does not match previous codes from multiline answer.");
                }
            } else {
                if ($connection->eof()) {
                    throw new TransientFailure("Unexpected EOF / connection close from SMTP server.");
                }

                throw new TransientFailure("Unexpected reply '" . $data . "' from SMTP server.");
            }
        } while (true);
    };

    try {
        $connection = new RemoteFile(MAIL_SMTP_HOST, MAIL_SMTP_PORT, 5);
        $success = false;
        if ($getCode($connection) == 220) {
            $connection->write('EHLO ' . Email::getHost() . "\r\n");
            if ($getCode($connection) == 250) {
                $connection->write("STARTTLS\r\n");
                if ($getCode($connection) == 220) {
                    if ($connection->setTLS(true)) {
                        $connection->write('EHLO ' . Email::getHost() . "\r\n");
                        if ($getCode($connection) == 250) {
                            $success = true;
                            try {
                                $connection->write("QUIT\r\n");
                            } catch (\Exception $e) {
                                // Ignore errors during disconnect.
                            }
                        }
                    }
                }
            }
        }

        if (!$success) {
            $value = 'none';
        }
    } catch (\Exception $e) {
        $value = 'none';
    } finally {
        try {
            $connection->close();
        } catch (\Exception $e) {
            // Ignore errors during disconnect.
        }
    }
}

$optionAction = new OptionAction([], 'import', [
    'data' => [
        'mail_smtp_starttls' => $value,
    ],
]);
$optionAction->executeAction();
