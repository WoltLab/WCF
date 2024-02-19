<?php

namespace wcf\data\service\worker;

use wcf\data\DatabaseObject;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 *
 * @property-read int $workerID
 * @property-read int $userID
 * @property-read string $endpoint
 * @property-read string $publicKey
 * @property-read string $authToken
 * @property-read string $contentEncoding
 */
class ServiceWorker extends DatabaseObject
{
    public const CONTENT_ENCODING_AESGCM = 'aesgcm';
    public const CONTENT_ENCODING_AES128GCM = 'aes128gcm';

    /**
     * Parses the endpoint and returns the scheme and host.
     */
    public function getEndpoint(): string
    {
        return \parse_url($this->endpoint, PHP_URL_SCHEME) . '://' . \parse_url($this->endpoint, PHP_URL_HOST);
    }
}
