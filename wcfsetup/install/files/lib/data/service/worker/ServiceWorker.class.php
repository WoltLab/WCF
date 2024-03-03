<?php

namespace wcf\data\service\worker;

use Minishlink\WebPush\SubscriptionInterface;
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
class ServiceWorker extends DatabaseObject implements SubscriptionInterface
{
    #[\Override]
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    #[\Override]
    public function getContentEncoding(): string
    {
        return $this->contentEncoding;
    }

    #[\Override]
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    #[\Override]
    public function getAuthToken(): ?string
    {
        return $this->authToken;
    }
}
