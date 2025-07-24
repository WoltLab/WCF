# `nelexa/buffer` -> Read And Write Binary Data

[![Packagist Version](https://img.shields.io/packagist/v/nelexa/buffer.svg)](https://packagist.org/packages/nelexa/buffer)
[![Packagist](https://img.shields.io/packagist/dt/nelexa/buffer.svg?color=%23ff007f)](https://packagist.org/packages/nelexa/buffer)
[![Build Status](https://travis-ci.org/Ne-Lexa/php-byte-buffer.svg?branch=master)](https://travis-ci.org/Ne-Lexa/php-byte-buffer
)
[![License](https://img.shields.io/packagist/l/nelexa/buffer.svg)](https://packagist.org/packages/nelexa/buffer)

This is classes defines methods for **reading and writing** values of all primitive types. Primitive values are translated to (or from) sequences of bytes according to the buffer's current byte order, which may be retrieved and modified via the order methods. The initial order of a byte buffer is always Buffer::BIG_ENDIAN.
 
### Requirements
* PHP >= 5.4 (64 bit)

### Installation
```bash
composer require nelexa/buffer
```

### Documentation

Class `\Nelexa\Buffer` is abstract and base methods for all other buffers.

Initialize buffer as string.
```php
$buffer = new \Nelexa\StringBuffer();
// or
$buffer = new \Nelexa\StringBuffer($text);
```

Initialize buffer as file.
```php
$buffer = new \Nelexa\FileBuffer($filename);
```

Initialize buffer as memory resource (php://memory).
```php
$buffer = new \Nelexa\MemoryReourceBuffer();
// or
$buffer = new \Nelexa\MemoryReourceBuffer($text);
```

Initialize buffer as stream resource.
```php
$fp = fopen('php://temp', 'w+b');
// or
$buffer = new \Nelexa\ResourceBuffer($fp);
```

Set read only buffer
```php
$buffer->setReadOnly(true);
```

Checking the possibility of recording in the buffer
```php
$boolValue = $buffer->isReadOnly();
```

Modifies this buffer's byte order, either `Buffer::BIG_ENDIAN` or `Buffer::LITTLE_ENDIAN`
```php
$buffer->setOrder(\Nelexa\Buffer::LITTLE_ENDIAN);
```

Get buffer's byte order
```php
$byteOrder = $buffer->order();
```

Get buffer size.
```php
$size = $buffer->size();
```

Set buffer position.
```php
$buffer->setPosition($position);
```

Get buffer position.
```php
$position = $buffer->position();
```

Skip bytes.
```php
$buffer->skip($count);

// example
$buffer->insertString('Test value');
assert($buffer->position() === 10);
$buffer->skip(-7);
assert($buffer->position() === 3);
$buffer->skip(2);
assert($buffer->position() === 5);
```

Skip primitive type size.
```php
$buffer->skipByte(); // skip 1 byte
$buffer->skipShort(); // skip 2 bytes
$buffer->skipInt(); // skip 4 bytes
$buffer->skipLong(); // skip 8 bytes
$buffer->skipFloat(); // skip 4 bytes
$buffer->skipDouble(); // skip 8 bytes
```

Rewinds this buffer. The position is set to zero.
```php
$buffer->rewind();

// example
$buffer->insertString('Test value');
assert($buffer->position() === 10);
$buffer->rewind();
assert($buffer->position() === 0);
assert($buffer->size() === 10);
```

Flips this buffer. The limit is set to the current position and then the position is set to zero.
```php
$buffer->flip();

// example
$buffer->insertString('Test value');
assert($buffer->position() === 10);
$buffer->setPosition(5);
$buffer->flip();
assert($buffer->position() === 0);
assert($buffer->size() === 5);
```

Returns the number of elements between the current position and the limit.
```php
$remaining = $buffer->remaining();

// example
$buffer->insertString('Test value');
assert($buffer->position() === 10);
$buffer->setPosition(7);
assert($buffer->remaining() === 10 - 7);
```

Tells whether there are any elements between the current position and the limit. True if, and only if, there is at least one element remaining in this buffer
```php
$boolValue = $buffer->hasRemaining();

// example
$buffer->insertString('Test value');
assert($buffer->position() === 10);
assert($buffer->hasRemaining() === false);
$buffer->setPosition(9);
assert($buffer->hasRemaining() === true);
```

Close buffer and release resources
```php
$buffer->close();
```

### Read buffer

Read the entire contents of the buffer into a string without changing the position of the buffer.
```php
$allBufferContent = $buffer->toString();

// example
$buffer->insertString('Test value');
assert($buffer->position() === 10);
$buffer->setPosition(4);
$allBufferContent = $buffer->toString();
assert($buffer->position() === 4);
assert($allBufferContent === 'Test value');
```

Reads the string at this buffer's current position, and then increments the position.
```php
$content = $buffer->get($length);

// example
$buffer->insertString('Test value');
assert($buffer->position() === 10);
$buffer->setPosition(3);
$content = $buffer->get(5);
assert($buffer->position() === 8);
assert($content === 't val');
```
##### Read literal types
Method                            | Type                    | Values
--------------------------------- | ----------------------- | -----------------
`$buffer->getBoolean`             | boolean                 | `true` or `false`
`$buffer->getByte()`              | byte                    | -128 ... 127
`$buffer->getUnsignedByte()`      | unsigned byte (ubyte)   | 0 ... 255
`$buffer->getShort()`             | short (2 bytes)         | -32768 ... 32767
`$buffer->getUnsignedShort()`     | unsigned short (ushort) | 0 ... 65535
`$buffer->getInt()`               | int (4 bytes)           | -2147483648 ... 2147483647
`$buffer->getUnsignedInt()`       | unsigned int (uint)     | 0 ... 4294967296
`$buffer->getLong()`              | long (8 bytes)          | -9223372036854775808 ... 9223372036854775807
`$buffer->getFloat()`             | float (4 bytes)         | single-precision 32-bit IEEE 754 floating point number
`$buffer->getDouble()`            | double (5 bytes)        | double-precision 64-bit IEEE 754 floating point number
`$buffer->getArrayBytes($length)` | byte[]                  | `array`
`$buffer->getString($length)`     | string (length bytes)   | `string`
`$buffer->getUTF()`               | string                  | `string`
`$buffer->getUTF16($length)`      | string (length * 2)     | `string`


### Write to buffer

#### Insert bytes to buffer
Insert string (byte[]) or Buffer to buffer.
```php
$buffer->insert('content');
// or
$buffer->insert(new StringBuffer('Other buffer'));

// example
assert($buffer->position() === 0);
assert($buffer->size() === 0);
$buffer->insert('Test value');
assert($buffer->position() === 10);
assert($buffer->size() === 10);
$buffer->setPosition(4);
$buffer->insert('ed');
assert($buffer->position() === 6);
assert($buffer->size() === 12);
assert($buffer->toString() === 'Tested value');
```
##### Insert primitive types
Insert boolean value `false` or `true`. Change size and position by +1.
```php
$buffer->insertBoolean($boolValue);
```
Insert byte (-128 >= byte <= 127). Change size and position by +1.
```php
$buffer->insertByte($byteValue);
```
Insert short value (-32768 >= short <= 32767). Change size and position by +2.
```php
$buffer->insertShort($shortValue);
```
Insert integer value (-2147483648 >= int <= 2147483647). Change size and position by +4.
```php
$buffer->insertInt($intValue);
```
Insert long value (-9223372036854775808 >= long <= 9223372036854775807). Change position +8.
```php
$buffer->insertLong($longValue);
```
Insert float value (single-precision 32-bit IEEE 754 floating point number). Change position +4.
```php
$buffer->insertFloat($floatValue);
```
Insert double value (double-precision 64-bit IEEE 754 floating point number). Change position +8.
```php
$buffer->insertDouble($doubleValue);
```
Insert array bytes. Change size and position by +(size array).
```php
$buffer->insertArrayBytes($bytes);
```
Insert string value. Change size and position by +(length string).
```php
$buffer->insertString($string);
```
Insert UTF-8 string with encoding first two bytes as length string. Change size and position by +(2 + length string).

Analog java [java.io.DataOutputStream#writeUTF(String str)](https://docs.oracle.com/javase/8/docs/api/java/io/DataOutputStream.html#writeUTF-java.lang.String-)
```php
$buffer->insertUTF($string);
```
Insert string with UTF-16 encoding. Change size and position by +(2 * length string).
```php
$buffer->insertUTF16($string);
```
#### Put bytes to buffer
Put string (byte[]) or Buffer to buffer and overwrite old value.
```php
$buffer->put('content');
// or
$buffer->put(new StringBuffer('Other buffer'));

// example
assert($buffer->position() === 0);
assert($buffer->size() === 0);
$buffer->insert('Test value');
assert($buffer->position() === 10);
assert($buffer->size() === 10);
$buffer->setPosition(4);
$buffer->put('ed');
assert($buffer->position() === 6);
assert($buffer->size() === 10);
assert($buffer->toString() === 'Testedalue');
```
##### Put primitive types
Put boolean value `false` or `true`. Change position by +1.
```php
$buffer->putBoolean($boolValue);
```
Put byte (-128 >= byte <= 127). Change position by +1.
```php
$buffer->putByte($byteValue);
```
Put short value (-32768 >= short <= 32767). Change position by +2.
```php
$buffer->putShort($shortValue);
```
Put integer value (-2147483648 >= int <= 2147483647). Change position by +4.
```php
$buffer->putInt($intValue);
```
Put long value (-9223372036854775808 >= long <= 9223372036854775807). Change position by +8.
```php
$buffer->putLong($longValue);
```
Put float value (single-precision 32-bit IEEE 754 floating point number). Change position +4.
```php
$buffer->putFloat($floatValue);
```
Put double value (double-precision 64-bit IEEE 754 floating point number). Change position +8.
```php
$buffer->putDouble($doubleValue);
```
Put array bytes. Change position by +(size array).
```php
$buffer->putArrayBytes($bytes);
```
Insert string value. Change position by +(length string).
```php
$buffer->putString($string);
```
Put UTF-8 string with encoding first two bytes as length string. Change position by +(2 + length string).

Analog java [java.io.DataOutputStream#writeUTF(String str)](https://docs.oracle.com/javase/8/docs/api/java/io/DataOutputStream.html#writeUTF-java.lang.String-)
```php
$buffer->puttUTF($string);
```
Put string with UTF-16 encoding. Change position by +(2 * length string).
```php
$buffer->putUTF16($string);
```
#### Replace bytes by buffer
Replace following a certain number of bytes by string or another Buffer.
```php
$buffer->replace('content', $length);
// or
$buffer->insert(new StringBuffer('Other buffer'), $length);

// example
assert($buffer->position() === 0);
assert($buffer->size() === 0);
$buffer->insert('Test value');
assert($buffer->position() === 10);
assert($buffer->size() === 10);
$buffer->setPosition(4);
$buffer->replace('ed', 4); // remove 4 next bytes and insert 2 bytes
assert($buffer->position() === 6);
assert($buffer->size() === 8);
assert($buffer->toString() === 'Testedlue');
```
##### Replace by primitive types
Replace by boolean value `false` or `true`. Change size by (-$length + 1) and position +1.
```php
$buffer->replaceBoolean($boolValue, $length);
```
Replace by byte (-128 >= byte <= 127). Change size by (-$length + 1) and position +1.
```php
$buffer->replaceByte($byteValue, $length);
```
Replace by short value (-32768 >= short <= 32767). Change size by (-$length + 2) and position +2.
```php
$buffer->replaceShort($shortValue, $length);
```
Replace by integer value (-2147483648 >= int <= 2147483647). Change size by (-$length + 4) and position +4.
```php
$buffer->replaceInt($intValue, $length);
```
Replace by long value (-9223372036854775808 >= long <= 9223372036854775807). Change size by (-$length + 8) and position +8.
```php
$buffer->replaceLong($longValue, $length);
```
Replace by float value (single-precision 32-bit IEEE 754 floating point number). Change size by (-$length + 4) and position +4.
```php
$buffer->replaceFloat($floatValue, $length);
```
Replace by double value (double-precision 64-bit IEEE 754 floating point number). Change size by (-$length + 8) and position +8.
```php
$buffer->replaceDouble($doubleValue, $length);
```
Replace by array bytes. Change size by (-$length + size array) and position +(size array).
```php
$buffer->replaceArrayBytes($bytes, $length);
```
Replace by string value. Change size by (-$length + length string) and position +(length string).
```php
$buffer->replaceString($string, $length);
```
Replace by UTF-8 string with encoding first two bytes as length string. Change size by (-$length + 2 + length string) and position +(2 + length string).

Analog java [java.io.DataOutputStream#writeUTF(String str)](https://docs.oracle.com/javase/8/docs/api/java/io/DataOutputStream.html#writeUTF-java.lang.String-)
```php
$buffer->replaceUTF($string, $length);
```
Replace by string with UTF-16 encoding. Change size by (-$length + 2 * length string) and position +(2 * length string).
```php
$buffer->replaceUTF16($string, $length);
```

### Remove bytes by buffer
Remove a certain number of bytes. Change size by -$length.
```php
$buffer->remove($length);

// example
assert($buffer->position() === 0);
assert($buffer->size() === 0);
$buffer->insert('Test value');
assert($buffer->position() === 10);
assert($buffer->size() === 10);
$buffer->setPosition(4);
$buffer->remove(3); // remove 3 next bytes
assert($buffer->position() === 4);
assert($buffer->size() === 7);
assert($buffer->toString() === 'Testlue');
```
Remove all bytes. Truncate buffer.
```php
$buffer->truncate($size = 0);

// example
assert($buffer->position() === 0);
assert($buffer->size() === 0);
$buffer->insert('Test value');
assert($buffer->position() === 10);
assert($buffer->size() === 10);
$buffer->truncate(0);
assert($buffer->position() === 0);
assert($buffer->size() === 0);
```

### Fluent interface
```php
// example
($buffer = new StringBuffer())
       ->insertByte(1)
       ->insertBoolean(true)
       ->insertShort(5551)
       ->skip(-2)
       ->insertUTF("Hello, World")
       ->truncate()
       ->insertString(str_rot13('Hello World'))
       ->setPosition(7)
       ->flip();
        
assert($this->buffer->size() === 7);
assert($this->buffer->position() === 0);
assert($this->buffer->toString() === str_rot13('Hello W'));
```
