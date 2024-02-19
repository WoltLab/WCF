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
use wcf\system\SingletonFactory;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ServiceWorkerHandler extends SingletonFactory
{
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
    public static function createNewKeys(): void
    {
        $jwk = JWKFactory::createECKey(Encryption::CURVE_ALGORITHM);
        $binaryPublicKey = Util::serializePublicKey($jwk->get('x'), $jwk->get('y'));
        $binaryPrivateKey = \hex2bin(
            \str_pad(\bin2hex(Base64Url::decode($jwk->get('d'))), 2 * VAPID::PRIVATE_KEY_LENGTH, '0', STR_PAD_LEFT)
        );
        OptionEditor::import([
            'service_worker_public_key' => Base64UrlSafe::encodeUnpadded($binaryPublicKey),
            'service_worker_private_key' => Base64UrlSafe::encodeUnpadded($binaryPrivateKey),
        ]);
    }

    /**
     * Send the given payload to the service worker.
     *
     * @param ServiceWorker $serviceWorker
     * @param string $payload
     */
    public function sendToServiceWorker(ServiceWorker $serviceWorker, string $payload): void
    {
        if (\mb_strlen($payload, '8bit') > self::MAX_PAYLOAD_LENGTH) {
            throw new \RuntimeException(
                'Content is too large, maximum payload length is ' . self::MAX_PAYLOAD_LENGTH . ' bytes'
            );
        }

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Encoding' => $serviceWorker->contentEncoding,
            'TTL' => ServiceWorkerHandler::TTL,
        ];
        $content = Encryption::encrypt(
            $serviceWorker,
            $payload,
            $headers
        );
        $headers['Content-Length'] = \mb_strlen($content, '8bit');
        VAPID::addHeader($serviceWorker, $headers);

        $this->getClient()->send(
            new Request(
                'POST',
                $serviceWorker->endpoint,
                $headers,
                $content
            )
        );
    }

    private function getClient(): ClientInterface
    {
        if (!isset($this->client)) {
            $this->client = HttpFactory::makeClientWithTimeout(10);
        }
        return $this->client;
    }
}
