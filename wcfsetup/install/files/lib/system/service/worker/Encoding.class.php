<?php

namespace wcf\system\service\worker;

use ParagonIE\ConstantTime\Base64;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
enum Encoding
{
    case Aes128Gcm;
    case AesGcm;

    public static function fromString(string $encoding): self
    {
        return match ($encoding) {
            'aes128gcm' => self::Aes128Gcm,
            'aesgcm' => self::AesGcm,
            default => throw new \InvalidArgumentException("Invalid encoding: {$encoding}"),
        };
    }

    public function getEncryptionContentCodingHeader(
        int $length,
        string $salt,
        string $publicKey
    ): string {
        return match ($this) {
            /** {@link https://datatracker.ietf.org/doc/html/rfc8188#section-2.1} */
            self::Aes128Gcm => \pack(
                'A*NCA*',
                $salt,
                $length,
                \strlen($publicKey),
                $publicKey
            ),
            self::AesGcm => '',
        };
    }

    public function pad(#[\SensitiveParameter] string $payload): string
    {
        $length = \mb_strlen($payload, '8bit');
        $paddingLength = ServiceWorkerHandler::MAX_PAYLOAD_LENGTH - $length;
        $padding = \str_repeat("\x00", $paddingLength);

        return match ($this) {
            self::Aes128Gcm => "{$payload}\x02{$padding}",
            self::AesGcm => \sprintf(
                '%s%s%s',
                \pack('n', $paddingLength),
                $padding,
                $payload,
            ),
        };
    }

    public function getInfo(string $type, ?string $context): string
    {
        if ($this === self::AesGcm) {
            \assert($context !== null);
            \assert(\mb_strlen($context, '8bit') === 135);

            return \sprintf(
                "Content-Encoding: %s\x00%s",
                $type,
                Encryption::CURVE_ALGORITHM . $context
            );
        }
        return "Content-Encoding: {$type}\x00";
    }

    /**
     * {@link https://datatracker.ietf.org/doc/html/draft-ietf-httpbis-encryption-encoding-00#section-4.2}
     */
    public function getContext(string $clientPublicKey, string $serverPublicKey): ?string
    {
        if ($this === self::Aes128Gcm) {
            return null;
        }
        \assert(\mb_strlen($clientPublicKey, '8bit') === VAPID::PUBLIC_KEY_LENGTH);

        $len = \pack('n', 65);

        return \sprintf(
            "\x00%s%s%s%s",
            $len,
            $clientPublicKey,
            $len,
            $serverPublicKey
        );
    }

    public function getIKM(
        string $authToken,
        #[\SensitiveParameter] string $sharedSecret,
        string $userPublicKey,
        string $newPublicKey
    ): string {
        if ($this === Encoding::AesGcm) {
            $info = "Content-Encoding: auth\x00";
        } elseif ($this === Encoding::Aes128Gcm) {
            $info = "WebPush: info\x00{$userPublicKey}{$newPublicKey}";
        } else {
            throw new \LogicException('Unreachable');
        }

        return \hash_hkdf(
            Encryption::HASH_ALGORITHM,
            $sharedSecret,
            32,
            $info,
            Base64::decode($authToken, true)
        );
    }

    public function toString(): string
    {
        return match ($this) {
            self::Aes128Gcm => 'aes128gcm',
            self::AesGcm => 'aesgcm',
        };
    }
}
