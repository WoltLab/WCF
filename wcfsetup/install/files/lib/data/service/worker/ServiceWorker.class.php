<?php

namespace wcf\data\service\worker;

use wcf\data\DatabaseObject;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 *
 * @property-read int $userID
 * @property-read string $endpoint
 * @property-read string $publicKey
 * @property-read string $authToken
 * @property-read string $contentEncoding
 */
final class ServiceWorker extends DatabaseObject
{
    public const string CONTENT_ENCODING_AESGCM = 'aesgcm';
    public const string CONTENT_ENCODING_AES128GCM = 'aes128gcm';
}
