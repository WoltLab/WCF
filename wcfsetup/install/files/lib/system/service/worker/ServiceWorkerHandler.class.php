<?php

namespace wcf\system\service\worker;

use GuzzleHttp\RequestOptions;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use wcf\data\option\OptionEditor;
use wcf\data\service\worker\ServiceWorker;
use wcf\system\io\HttpFactory;
use wcf\system\registry\RegistryHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ServiceWorkerHandler extends SingletonFactory
{
    private const REGISTRY_KEY = 'service_worker_key_hash';

    /**
     * Maximum payload length that can be sent to the service worker.
     * @see https://stackoverflow.com/a/66222350
     */
    public const MAX_PAYLOAD_LENGTH = 2847;

    /**
     * Time to live for the notification before it is discarded by the push-service provider.
     */
    public const TTL = 604800; // 7 days

    private WebPush $pushClient;

    /**
     * @internal
     */
    public function updateKeys(): void
    {
        $hash = RegistryHandler::getInstance()->get('com.woltlab.wcf', self::REGISTRY_KEY);
        if ($hash !== null && \hash_equals($hash, \hash('sha256', SERVICE_WORKER_PRIVATE_KEY))) {
            return;
        }
        $this->createNewKeys();
    }

    private function createNewKeys(): void
    {
        ['publicKey' => $publicKey, 'privateKey' => $privateKey] = VAPID::createVapidKeys();
        OptionEditor::import([
            'service_worker_public_key' => $publicKey,
            'service_worker_private_key' => $privateKey,
        ]);

        RegistryHandler::getInstance()->set(
            'com.woltlab.wcf',
            self::REGISTRY_KEY,
            \hash('sha256', $privateKey)
        );

        // Previous client keys are no longer valid
        $sql = "DELETE FROM wcf1_service_worker";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
    }

    /**
     * Send the given payload to the service worker.
     *
     * @param ServiceWorker $serviceWorker
     * @param string $payload
     *
     * @return MessageSentReport
     */
    public function sendOneNotification(
        ServiceWorker $serviceWorker,
        #[\SensitiveParameter] string $payload
    ): MessageSentReport {
        return $this->getClient()->sendOneNotification($serviceWorker, $payload);
    }

    private function getClient(): WebPush
    {
        if (!isset($this->pushClient)) {
            $this->pushClient = new WebPush([
                'VAPID' => [
                    'subject' => 'mailto:' . MAIL_ADMIN_ADDRESS,
                    'publicKey' => SERVICE_WORKER_PUBLIC_KEY,
                    'privateKey' => SERVICE_WORKER_PRIVATE_KEY,
                ],
            ], ['TTL' => self::TTL], null, [
                /** @see HttpFactory::makeClient() */
                RequestOptions::PROXY => PROXY_SERVER_HTTP,
                RequestOptions::HEADERS => [
                    'user-agent' => HttpFactory::getDefaultUserAgent(),
                ],
                RequestOptions::TIMEOUT => 60,
            ]);
            $this->pushClient->setAutomaticPadding(self::MAX_PAYLOAD_LENGTH);
            $this->pushClient->setReuseVAPIDHeaders(true);
        }

        return $this->pushClient;
    }
}
