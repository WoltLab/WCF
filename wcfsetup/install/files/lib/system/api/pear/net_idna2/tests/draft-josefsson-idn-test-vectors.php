<?php
require_once 'Net/IDNA2.php';

// Test cases from https://www.gnu.org/software/libidn/draft-josefsson-idn-test-vectors.html

define('IDNA_ACE_PREFIX', 'xn--');

class IDNATest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->idn = new Net_IDNA2();
    }

    static function unichr($chr) {
        return mb_convert_encoding('&#' . intval($chr) . ';', 'UTF-8', 'HTML-ENTITIES');
    }

    private function hexarray2string($arr) {
        return implode('', array_map(array('self', 'unichr'), $arr));
    }

    public function testDecode1() {
        // Arabic (Egyptian)
        $expected = $this->hexarray2string(array(
            0x0644, 0x064A, 0x0647, 0x0645, 0x0627, 0x0628, 0x062A, 0x0643,
	        0x0644, 0x0645, 0x0648, 0x0634, 0x0639, 0x0631, 0x0628, 0x064A,
	        0x061F
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."egbpdaj6bu4bxfgehfvwxn");
        $this->assertSame($expected, $result);
    }

    public function testDecode2() {
        // Chinese (simplified)
        $expected = $this->hexarray2string(array(
            0x4ED6, 0x4EEC, 0x4E3A, 0x4EC0, 0x4E48, 0x4E0D, 0x8BF4, 0x4E2D, 0x6587
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."ihqwcrb4cv8a8dqg056pqjye");
        $this->assertSame($expected, $result);
    }

    public function testDecode3() {
        // Chinese (traditional)
        $expected = $this->hexarray2string(array(
            0x4ED6, 0x5011, 0x7232, 0x4EC0, 0x9EBD, 0x4E0D, 0x8AAA, 0x4E2D, 0x6587
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."ihqwctvzc91f659drss3x8bo0yb");
        $this->assertSame($expected, $result);
    }

    public function testDecode4() {
        // Czech
        $expected = $this->hexarray2string(array(
            0x0050, 0x0072, 0x006F, 0x010D, 0x0070, 0x0072, 0x006F, 0x0073,
	        0x0074, 0x011B, 0x006E, 0x0065, 0x006D, 0x006C, 0x0075, 0x0076,
	        0x00ED, 0x010D, 0x0065, 0x0073, 0x006B, 0x0079
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."Proprostnemluvesky-uyb24dma41a");
        $this->assertSame($expected, $result);
    }

    public function testDecode5() {
        // Hebrew
        $expected = $this->hexarray2string(array(
            0x05DC, 0x05DE, 0x05D4, 0x05D4, 0x05DD, 0x05E4, 0x05E9, 0x05D5,
	        0x05D8, 0x05DC, 0x05D0, 0x05DE, 0x05D3, 0x05D1, 0x05E8, 0x05D9,
	        0x05DD, 0x05E2, 0x05D1, 0x05E8, 0x05D9, 0x05EA
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."4dbcagdahymbxekheh6e0a7fei0b");
        $this->assertSame($expected, $result);
    }

      public function testDecode6() {
        // Hindi (Devanagari)
        $expected = $this->hexarray2string(array(
            0x092F, 0x0939, 0x0932, 0x094B, 0x0917, 0x0939, 0x093F, 0x0928,
        	0x094D, 0x0926, 0x0940, 0x0915, 0x094D, 0x092F, 0x094B, 0x0902,
	        0x0928, 0x0939, 0x0940, 0x0902, 0x092C, 0x094B, 0x0932, 0x0938,
	        0x0915, 0x0924, 0x0947, 0x0939, 0x0948, 0x0902
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."i1baa7eci9glrd9b2ae1bj0hfcgg6iyaf8o0a1dig0cd");
        $this->assertSame($expected, $result);
    }

    public function testDecode7() {
        // Japanese (kanji and hiragana)
        $expected = $this->hexarray2string(array(
            0x306A, 0x305C, 0x307F, 0x3093, 0x306A, 0x65E5, 0x672C, 0x8A9E,
        	0x3092, 0x8A71, 0x3057, 0x3066, 0x304F, 0x308C, 0x306A, 0x3044,
        	0x306E, 0x304B
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."n8jok5ay5dzabd5bym9f0cm5685rrjetr6pdxa");
        $this->assertSame($expected, $result);
    }

    public function testDecode8() {
        // Russian (Cyrillic)
        $expected = $this->hexarray2string(array(
            0x043F, 0x043E, 0x0447, 0x0435, 0x043C, 0x0443, 0x0436, 0x0435,
        	0x043E, 0x043D, 0x0438, 0x043D, 0x0435, 0x0433, 0x043E, 0x0432,
        	0x043E, 0x0440, 0x044F, 0x0442, 0x043F, 0x043E, 0x0440, 0x0443,
        	0x0441, 0x0441, 0x043A, 0x0438
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."b1abfaaepdrnnbgefbadotcwatmq2g4l");
        $this->assertSame($expected, $result);
    }

    public function testDecode9() {
        // Spanish
        $expected = $this->hexarray2string(array(
            0x0050, 0x006F, 0x0072, 0x0071, 0x0075, 0x00E9, 0x006E, 0x006F,
        	0x0070, 0x0075, 0x0065, 0x0064, 0x0065, 0x006E, 0x0073, 0x0069,
        	0x006D, 0x0070, 0x006C, 0x0065, 0x006D, 0x0065, 0x006E, 0x0074,
        	0x0065, 0x0068, 0x0061, 0x0062, 0x006C, 0x0061, 0x0072, 0x0065,
        	0x006E, 0x0045, 0x0073, 0x0070, 0x0061, 0x00F1, 0x006F, 0x006C
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."PorqunopuedensimplementehablarenEspaol-fmd56a");
        $this->assertSame($expected, $result);
    }

    public function testDecode10() {
        // Vietnamese
        $expected = $this->hexarray2string(array(
            0x0054, 0x1EA1, 0x0069, 0x0073, 0x0061, 0x006F, 0x0068, 0x1ECD,
        	0x006B, 0x0068, 0x00F4, 0x006E, 0x0067, 0x0074, 0x0068, 0x1EC3,
        	0x0063, 0x0068, 0x1EC9, 0x006E, 0x00F3, 0x0069, 0x0074, 0x0069,
        	0x1EBF, 0x006E, 0x0067, 0x0056, 0x0069, 0x1EC7, 0x0074
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."TisaohkhngthchnitingVit-kjcr8268qyxafd2f1b9g");
        $this->assertSame($expected, $result);
    }

    public function testDecode11() {
        // Japanese
        $expected = $this->hexarray2string(array(
            0x0033, 0x5E74, 0x0042, 0x7D44, 0x91D1, 0x516B, 0x5148, 0x751F
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."3B-ww4c5e180e575a65lsy2b");
        $this->assertSame($expected, $result);
    }

    public function testDecode12() {
        // Japanese
        $expected = $this->hexarray2string(array(
            0x5B89, 0x5BA4, 0x5948, 0x7F8E, 0x6075, 0x002D, 0x0077, 0x0069,
        	0x0074, 0x0068, 0x002D, 0x0053, 0x0055, 0x0050, 0x0045, 0x0052,
        	0x002D, 0x004D, 0x004F, 0x004E, 0x004B, 0x0045, 0x0059, 0x0053
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."-with-SUPER-MONKEYS-pc58ag80a8qai00g7n9n");
        $this->assertSame($expected, $result);
    }

    public function testDecode13() {
        // Japanese
        $expected = $this->hexarray2string(array(
            0x0048, 0x0065, 0x006C, 0x006C, 0x006F, 0x002D, 0x0041, 0x006E,
	        0x006F, 0x0074, 0x0068, 0x0065, 0x0072, 0x002D, 0x0057, 0x0061,
        	0x0079, 0x002D, 0x305D, 0x308C, 0x305E, 0x308C, 0x306E, 0x5834,
        	0x6240
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."Hello-Another-Way--fc4qua05auwb3674vfr0b");
        $this->assertSame($expected, $result);
    }

    public function testDecode14() {
        // Japanese
        $expected = $this->hexarray2string(array(
            0x3072, 0x3068, 0x3064, 0x5C4B, 0x6839, 0x306E, 0x4E0B, 0x0032
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."2-u9tlzr9756bt3uc0v");
        $this->assertSame($expected, $result);
    }

    public function testDecode15() {
        // Japanese
        $expected = $this->hexarray2string(array(
            0x004D, 0x0061, 0x006A, 0x0069, 0x3067, 0x004B, 0x006F, 0x0069,
        	0x3059, 0x308B, 0x0035, 0x79D2, 0x524D
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."MajiKoi5-783gue6qz075azm5e");
        $this->assertSame($expected, $result);
    }

    public function testDecode16() {
        // Japanese
        $expected = $this->hexarray2string(array(
            0x30D1, 0x30D5, 0x30A3, 0x30FC, 0x0064, 0x0065, 0x30EB, 0x30F3, 0x30D0
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."de-jg4avhby1noc0d");
        $this->assertSame($expected, $result);
    }

    public function testDecode17() {
        // Japanese
        $expected = $this->hexarray2string(array(
            0x305D, 0x306E, 0x30B9, 0x30D4, 0x30FC, 0x30C9, 0x3067
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."d9juau41awczczp");
        $this->assertSame($expected, $result);
    }

    public function testDecode18() {
        // Greek
        $expected = $this->hexarray2string(array(
            0x03b5, 0x03bb, 0x03bb, 0x03b7, 0x03bd, 0x03b9, 0x03ba, 0x03ac
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."hxargifdar");
        $this->assertSame($expected, $result);
    }

    public function testDecode19() {
        // Maltese (Malti)
        $expected = $this->hexarray2string(array(
            0x0062, 0x006f, 0x006e, 0x0121, 0x0075, 0x0073, 0x0061, 0x0127,
            0x0127, 0x0061
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."bonusaa-5bb1da");
        $this->assertSame($expected, $result);
    }

    public function testDecode20() {
        // Russian (Cyrillic)
        $expected = $this->hexarray2string(array(
            0x043f, 0x043e, 0x0447, 0x0435, 0x043c, 0x0443, 0x0436, 0x0435,
            0x043e, 0x043d, 0x0438, 0x043d, 0x0435, 0x0433, 0x043e, 0x0432,
            0x043e, 0x0440, 0x044f, 0x0442, 0x043f, 0x043e, 0x0440, 0x0443,
            0x0441, 0x0441, 0x043a, 0x0438
	    ));
        $result = $this->idn->decode(IDNA_ACE_PREFIX."b1abfaaepdrnnbgefbadotcwatmq2g4l");
        $this->assertSame($expected, $result);
    }

    public function testEncode1() {
        // Arabic (Egyptian)
        $idna = $this->hexarray2string(array(
            0x0644, 0x064A, 0x0647, 0x0645, 0x0627, 0x0628, 0x062A, 0x0643,
	        0x0644, 0x0645, 0x0648, 0x0634, 0x0639, 0x0631, 0x0628, 0x064A,
	        0x061F
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."egbpdaj6bu4bxfgehfvwxn", $result);
    }

    public function testEncode2() {
        // Chinese (simplified)
        $idna = $this->hexarray2string(array(
            0x4ED6, 0x4EEC, 0x4E3A, 0x4EC0, 0x4E48, 0x4E0D, 0x8BF4, 0x4E2D, 0x6587
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."ihqwcrb4cv8a8dqg056pqjye", $result);
    }

    public function testEncode3() {
        // Chinese (traditional)
        $idna = $this->hexarray2string(array(
            0x4ED6, 0x5011, 0x7232, 0x4EC0, 0x9EBD, 0x4E0D, 0x8AAA, 0x4E2D, 0x6587
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."ihqwctvzc91f659drss3x8bo0yb", $result);
    }

    public function testEncode4() {
        // Czech
        $idna = $this->hexarray2string(array(
            0x0050, 0x0072, 0x006F, 0x010D, 0x0070, 0x0072, 0x006F, 0x0073,
	        0x0074, 0x011B, 0x006E, 0x0065, 0x006D, 0x006C, 0x0075, 0x0076,
	        0x00ED, 0x010D, 0x0065, 0x0073, 0x006B, 0x0079
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."proprostnemluvesky-uyb24dma41a", $result);
    }

    public function testEncode5() {
        // Hebrew
        $idna = $this->hexarray2string(array(
            0x05DC, 0x05DE, 0x05D4, 0x05D4, 0x05DD, 0x05E4, 0x05E9, 0x05D5,
	        0x05D8, 0x05DC, 0x05D0, 0x05DE, 0x05D3, 0x05D1, 0x05E8, 0x05D9,
	        0x05DD, 0x05E2, 0x05D1, 0x05E8, 0x05D9, 0x05EA
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."4dbcagdahymbxekheh6e0a7fei0b", $result);
    }

      public function testEncode6() {
        // Hindi (Devanagari)
        $idna = $this->hexarray2string(array(
            0x092F, 0x0939, 0x0932, 0x094B, 0x0917, 0x0939, 0x093F, 0x0928,
        	0x094D, 0x0926, 0x0940, 0x0915, 0x094D, 0x092F, 0x094B, 0x0902,
	        0x0928, 0x0939, 0x0940, 0x0902, 0x092C, 0x094B, 0x0932, 0x0938,
	        0x0915, 0x0924, 0x0947, 0x0939, 0x0948, 0x0902
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."i1baa7eci9glrd9b2ae1bj0hfcgg6iyaf8o0a1dig0cd", $result);
    }

    public function testEncode7() {
        // Japanese (kanji and hiragana)
        $idna = $this->hexarray2string(array(
            0x306A, 0x305C, 0x307F, 0x3093, 0x306A, 0x65E5, 0x672C, 0x8A9E,
        	0x3092, 0x8A71, 0x3057, 0x3066, 0x304F, 0x308C, 0x306A, 0x3044,
        	0x306E, 0x304B
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."n8jok5ay5dzabd5bym9f0cm5685rrjetr6pdxa", $result);
    }

    public function testEncode8() {
        // Russian (Cyrillic)
        $idna = $this->hexarray2string(array(
            0x043F, 0x043E, 0x0447, 0x0435, 0x043C, 0x0443, 0x0436, 0x0435,
        	0x043E, 0x043D, 0x0438, 0x043D, 0x0435, 0x0433, 0x043E, 0x0432,
        	0x043E, 0x0440, 0x044F, 0x0442, 0x043F, 0x043E, 0x0440, 0x0443,
        	0x0441, 0x0441, 0x043A, 0x0438
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."b1abfaaepdrnnbgefbadotcwatmq2g4l", $result);
    }

    public function testEncode9() {
        // Spanish
        $idna = $this->hexarray2string(array(
            0x0050, 0x006F, 0x0072, 0x0071, 0x0075, 0x00E9, 0x006E, 0x006F,
        	0x0070, 0x0075, 0x0065, 0x0064, 0x0065, 0x006E, 0x0073, 0x0069,
        	0x006D, 0x0070, 0x006C, 0x0065, 0x006D, 0x0065, 0x006E, 0x0074,
        	0x0065, 0x0068, 0x0061, 0x0062, 0x006C, 0x0061, 0x0072, 0x0065,
        	0x006E, 0x0045, 0x0073, 0x0070, 0x0061, 0x00F1, 0x006F, 0x006C
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."porqunopuedensimplementehablarenespaol-fmd56a", $result);
    }

    public function testEncode10() {
        // Vietnamese
        $idna = $this->hexarray2string(array(
            0x0054, 0x1EA1, 0x0069, 0x0073, 0x0061, 0x006F, 0x0068, 0x1ECD,
        	0x006B, 0x0068, 0x00F4, 0x006E, 0x0067, 0x0074, 0x0068, 0x1EC3,
        	0x0063, 0x0068, 0x1EC9, 0x006E, 0x00F3, 0x0069, 0x0074, 0x0069,
        	0x1EBF, 0x006E, 0x0067, 0x0056, 0x0069, 0x1EC7, 0x0074
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."tisaohkhngthchnitingvit-kjcr8268qyxafd2f1b9g", $result);
    }

    public function testEncode11() {
        // Japanese
        $idna = $this->hexarray2string(array(
            0x0033, 0x5E74, 0x0042, 0x7D44, 0x91D1, 0x516B, 0x5148, 0x751F
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."3b-ww4c5e180e575a65lsy2b", $result);
    }

    public function testEncode12() {
        // Japanese
        $idna = $this->hexarray2string(array(
            0x5B89, 0x5BA4, 0x5948, 0x7F8E, 0x6075, 0x002D, 0x0077, 0x0069,
        	0x0074, 0x0068, 0x002D, 0x0053, 0x0055, 0x0050, 0x0045, 0x0052,
        	0x002D, 0x004D, 0x004F, 0x004E, 0x004B, 0x0045, 0x0059, 0x0053
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."-with-super-monkeys-pc58ag80a8qai00g7n9n", $result);
    }

    public function testEncode13() {
        // Japanese
        $idna = $this->hexarray2string(array(
            0x0048, 0x0065, 0x006C, 0x006C, 0x006F, 0x002D, 0x0041, 0x006E,
	        0x006F, 0x0074, 0x0068, 0x0065, 0x0072, 0x002D, 0x0057, 0x0061,
        	0x0079, 0x002D, 0x305D, 0x308C, 0x305E, 0x308C, 0x306E, 0x5834,
        	0x6240
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."hello-another-way--fc4qua05auwb3674vfr0b", $result);
    }

    public function testEncode14() {
        // Japanese
        $idna = $this->hexarray2string(array(
            0x3072, 0x3068, 0x3064, 0x5C4B, 0x6839, 0x306E, 0x4E0B, 0x0032
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."2-u9tlzr9756bt3uc0v", $result);
    }

    public function testEncode15() {
        // Japanese
        $idna = $this->hexarray2string(array(
            0x004D, 0x0061, 0x006A, 0x0069, 0x3067, 0x004B, 0x006F, 0x0069,
        	0x3059, 0x308B, 0x0035, 0x79D2, 0x524D
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."majikoi5-783gue6qz075azm5e", $result);
    }

    public function testEncode16() {
        // Japanese
        $idna = $this->hexarray2string(array(
            0x30D1, 0x30D5, 0x30A3, 0x30FC, 0x0064, 0x0065, 0x30EB, 0x30F3, 0x30D0
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."de-jg4avhby1noc0d", $result);
    }

    public function testEncode17() {
        // Japanese
        $idna = $this->hexarray2string(array(
            0x305D, 0x306E, 0x30B9, 0x30D4, 0x30FC, 0x30C9, 0x3067
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."d9juau41awczczp", $result);
    }

    public function testEncode18() {
        // Greek
        $idna = $this->hexarray2string(array(
            0x03b5, 0x03bb, 0x03bb, 0x03b7, 0x03bd, 0x03b9, 0x03ba, 0x03ac
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."hxargifdar", $result);
    }

    public function testEncode19() {
        // Maltese (Malti)
        $idna = $this->hexarray2string(array(
            0x0062, 0x006f, 0x006e, 0x0121, 0x0075, 0x0073, 0x0061, 0x0127,
            0x0127, 0x0061
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."bonusaa-5bb1da", $result);
    }

    public function testEncode20() {
        // Russian (Cyrillic)
        $idna = $this->hexarray2string(array(
            0x043f, 0x043e, 0x0447, 0x0435, 0x043c, 0x0443, 0x0436, 0x0435,
            0x043e, 0x043d, 0x0438, 0x043d, 0x0435, 0x0433, 0x043e, 0x0432,
            0x043e, 0x0440, 0x044f, 0x0442, 0x043f, 0x043e, 0x0440, 0x0443,
            0x0441, 0x0441, 0x043a, 0x0438
	    ));
        $result = $this->idn->encode($idna);
        $this->assertSame(IDNA_ACE_PREFIX."b1abfaaepdrnnbgefbadotcwatmq2g4l", $result);
    }

}

