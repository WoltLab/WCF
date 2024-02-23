<?php

namespace wcf\system\service\worker;

use Base64Url\Base64Url;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Jose\Component\KeyManagement\JWKFactory;
use ParagonIE\ConstantTime\Base64UrlSafe;
use wcf\data\option\OptionEditor;
use wcf\data\service\worker\ServiceWorker;
use wcf\system\io\HttpFactory;
use wcf\system\registry\RegistryHandler;
use wcf\system\SingletonFactory;

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

    private ClientInterface $client;

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
        $jwk = JWKFactory::createECKey(Encryption::CURVE_ALGORITHM);
        $binaryPublicKey = Util::serializePublicKey($jwk->get('x'), $jwk->get('y'));
        $binaryPrivateKey = \hex2bin(
            \str_pad(\bin2hex(Base64Url::decode($jwk->get('d'))), 2 * VAPID::PRIVATE_KEY_LENGTH, '0', STR_PAD_LEFT)
        );
        $base64PrivateKey = Base64UrlSafe::encodeUnpadded($binaryPrivateKey);
        OptionEditor::import([
            'service_worker_public_key' => Base64UrlSafe::encodeUnpadded($binaryPublicKey),
            'service_worker_private_key' => $base64PrivateKey,
        ]);

        RegistryHandler::getInstance()->set(
            'com.woltlab.wcf',
            self::REGISTRY_KEY,
            \hash('sha256', $base64PrivateKey)
        );
    }

    /**
     * Send the given payload to the service worker.
     *
     * @param ServiceWorker $serviceWorker
     * @param string $payload
     */
    public function sendToServiceWorker(ServiceWorker $serviceWorker, #[\SensitiveParameter] string $payload): void
    {
        if (\mb_strlen($payload, '8bit') > self::MAX_PAYLOAD_LENGTH) {
            throw new \RuntimeException(
                'Content is too large, maximum payload length is ' . self::MAX_PAYLOAD_LENGTH . ' bytes'
            );
        }

        $request = new Request('POST', $serviceWorker->endpoint, [
            'content-type' => 'application/octet-stream',
            'content-encoding' => $serviceWorker->contentEncoding,
            'ttl' => self::TTL,
        ]);

        $request = Encryption::encrypt(
            $serviceWorker,
            $payload,
            $request
        );
        $request = VAPID::addHeader($serviceWorker, $request);

        $this->getClient()->send($request);
    }

    private function getClient(): ClientInterface
    {
        if (!isset($this->client)) {
            $this->client = HttpFactory::makeClientWithTimeout(10);
        }

        return $this->client;
    }
}
