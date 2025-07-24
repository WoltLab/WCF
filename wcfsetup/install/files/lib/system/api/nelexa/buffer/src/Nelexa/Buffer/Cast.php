<?php

namespace Nelexa\Buffer;

class Cast
{
    /**
     * A constant holding the minimum value a byte can
     * have, -2^7.
     */
    const BYTE_MIN_VALUE = -128;

    /**
     * A constant holding the maximum value a byte can
     * have, 2^7-1.
     */
    const BYTE_MAX_VALUE = 127;

    /**
     * A constant holding the minimum value a short can
     * have, -2^15.
     */
    const SHORT_MIN_VALUE = -32768;

    /**
     * A constant holding the maximum value a short can
     * have, 2^15-1.
     */
    const SHORT_MAX_VALUE = 32767;

    /**
     * A constant holding the minimum value an int can
     * have, -2^31.
     */
    const INTEGER_MIN_VALUE = -2147483648;

    /**
     * A constant holding the maximum value an int can
     * have, 2^31-1.
     */
    const INTEGER_MAX_VALUE = 2147483647;

    /**
     * A constant holding the minimum value a long can
     * have, -2^63.
     */
    const LONG_MIN_VALUE = -9223372036854775808;

    /**
     * A constant holding the maximum value a long can
     * have, 2^63-1.
     */
    const LONG_MAX_VALUE = 9223372036854775807;

    /**
     * Cast to byte (-128 >= byte <= 127)
     *
     * @param int $i
     * @return int
     */
    public static function toByte($i)
    {
        $i &= 0xff;
        if ($i < 128) {
            return $i;
        }
        return $i - 256;
    }

    /**
     * Cast to unsigned byte (0 >= short <= 255)
     *
     * @param int $i
     * @return int
     */
    public static function toUnsignedByte($i)
    {
        return $i & 0xff;
    }

    /**
     * Cast to short (-32768 >= short <= 32767)
     *
     * @param int $i
     * @return int
     */
    public static function toShort($i)
    {
        $i &= 0xffff;
        if ($i < 32768) {
            return $i;
        }
        return $i - 65536;
    }

    /**
     * Cast to unsigned short (0 >= int <= 65535)
     *
     * @param int $i
     * @return int
     */
    public static function toUnsignedShort($i)
    {
        return $i & 0xffff;
    }

    /**
     * Cast to int (-2147483648 >= int <= 2147483647)
     *
     * @param int $i
     * @return int
     */
    public static function toInt($i)
    {
        if (PHP_INT_SIZE === 8) {
            $i &= 0xffffffff;
            if ($i < 2147483648) {
                return $i;
            }
            return $i - 4294967296;
        }
        return $i;
    }

    /**
     * Cast to unsigned int (0 >= long <= 4294967296)
     *
     * @param int $i
     * @return int
     */
    public static function toUnsignedInt($i)
    {
        return $i & 0xffffffff;
    }

    /**
     * Cast to long (-9223372036854775808 >= long <= 9223372036854775807)
     *
     * @param int $i
     * @return int
     */
    public static function toLong($i)
    {
        $i = (int)$i;
        if ($i > static::LONG_MAX_VALUE) {
            throw new \RuntimeException('Invalid long value');
        }

        if ($i < static::LONG_MIN_VALUE) {
            throw new \RuntimeException('Invalid long value');
        }
        return $i;
    }
}
