<?php

namespace wcf\system\service\worker;

use Base64Url\Base64Url;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use ParagonIE\ConstantTime\Base64UrlSafe;
use wcf\data\service\worker\ServiceWorker;
use wcf\util\JSON;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class VAPID
{
    public const PUBLIC_KEY_LENGTH = 65;
    public const PRIVATE_KEY_LENGTH = 32;

    /**
     * Add the VAPID header to the request headers.
     * {@link https://www.rfc-editor.org/rfc/rfc8282}
     *
     * @param ServiceWorker $serviceWorker
     * @param array $headers
     */
    public static function addHeader(ServiceWorker $serviceWorker, array &$headers): void
    {
        $rawPublicKey = Base64Url::decode(SERVICE_WORKER_PUBLIC_KEY);
        // Validate the length of the public key
        if (\mb_strlen($rawPublicKey, '8bit') !== VAPID::PUBLIC_KEY_LENGTH) {
            throw new \RuntimeException('Invalid public key length');
        }
        ['x' => $x, 'y' => $y] = Util::unserializePublicKey($rawPublicKey);

        $header = [
            'typ' => 'JWT',
            'alg' => 'ES256',
        ];
        $payload = JSON::encode([
            'aud' => $serviceWorker->getEndpoint(),
            'exp' => TIME_NOW + 43200, // 12h
            'sub' => "mailto:" . MAIL_ADMIN_ADDRESS,
        ], JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        if (!$payload) {
            throw new \RuntimeException('Could not encode payload');
        }

        $jwk = Util::createJWK($x, $y, SERVICE_WORKER_PRIVATE_KEY);
        $compactSerializer = new CompactSerializer();
        $jwsBuilder = new JWSBuilder(new AlgorithmManager([new ES256()]));
        $jws = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($jwk, $header)
            ->build();
        $jwt = $compactSerializer->serialize($jws, 0);

        if ($serviceWorker->contentEncoding === ServiceWorker::CONTENT_ENCODING_AESGCM) {
            $headers['Authorization'] = 'WebPush ' . $jwt;
            $headers['Crypto-Key'] .= ';p256ecdsa=' . SERVICE_WORKER_PUBLIC_KEY;
        } elseif ($serviceWorker->contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            $headers['Authorization'] = 'vapid t=' . $jwt . ', k=' . SERVICE_WORKER_PUBLIC_KEY;
        } else {
            throw new \InvalidArgumentException('Invalid content encoding: "' . $serviceWorker->contentEncoding . '"');
        }
    }
}
