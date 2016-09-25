<?php
namespace wcf\system\html\input\node;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;

/**
 * Processes `<woltlab-color>` to check for valid color arguments.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Node
 * @since       3.0
 */
class HtmlInputNodeWoltlabColor extends AbstractHtmlInputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'woltlab-color';
	
	public static $validColors = [
		'000000', '000080', '0000CD', '0000FF', '006400', '008000', '008080', '00FF00',
		'00FFFF', '2F4F4F', '40E0D0', '4B0082', '696969', '800000', '800080', '808080',
		'8B4513', 'A52A2A', 'A9A9A9', 'ADD8E6', 'AFEEEE', 'B22222', 'D3D3D3', 'DAA520',
		'DDA0DD', 'E6E6FA', 'EE82EE', 'F0F8FF', 'F0FFF0', 'F0FFFF', 'FAEBD7', 'FF0000',
		'FF8C00', 'FFA07A', 'FFA500', 'FFD700', 'FFF0F5', 'FFFF00', 'FFFFE0', 'FFFFFF'
	];
	
	protected static $colorsToLab = [];
	
	/**
	 * @inheritDoc
	 */
	public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		if (BBCodeHandler::getInstance()->isAvailableBBCode('color')) {
			return [];
		}
		
		if (!$htmlNodeProcessor->getDocument()->getElementsByTagName('woltlab-color')->length) {
			return [];
		}
		
		return ['color'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			if (preg_match('~\bwoltlab-color-([a-z0-9]{6})\b~i', $element->getAttribute('class'), $matches)) {
				$color = strtoupper($matches[1]);
				if (!in_array($color, self::$validColors)) {
					$color = $this->getClosestColor($color);
				}
				
				$element->setAttribute('class', 'woltlab-color-'.$color);
			}
			else {
				DOMUtil::removeNode($element, true);
			}
		}
	}
	
	protected function getClosestColor($hex) {
		if (empty(self::$colorsToLab)) {
			// build static lookup table
			foreach (self::$validColors as $color) {
				self::$colorsToLab[$color] = $this->hexToLab($color);
			}
		}
		
		$lab1 = $this->hexToLab($hex);
		
		$diff = 0;
		$newColor = '';
		
		foreach (self::$colorsToLab as $color => $lab2) {
			$newDiff = $this->deltaE_cie1994($lab1, $lab2);
			
			if ($newColor === '' || $diff > $newDiff) {
				$diff = $newDiff;
				$newColor = $color;
			}
		}
		
		return $newColor;
	}
	
	protected function hexToLab($hex) {
		return $this->xyzToLab(
			$this->rgbToXyz(
				$this->hexToRgb($hex)
			)
		);
	}
	
	protected function hexToRgb($hex) {
		// [r, g, b]
		return [
			hexdec($hex{0}.$hex{1}),
			hexdec($hex{2}.$hex{3}),
			hexdec($hex{4}.$hex{5})
		];
	}
	
	protected function rgbToXyz($rgb) {
		// convert into values between 0 and 1
		$red = $rgb[0] / 255;
		$green = $rgb[1] / 255;
		$blue = $rgb[2] / 255;
		
		if ($red > 0.04045) {
			$red = ($red + 0.055) / 1.055;
			$red = pow($red, 2.4);
		}
		else {
			$red = $red / 12.92;
		}
		
		if ($green > 0.04045) {
			$green = ($green + 0.055) / 1.055;
			$green = pow($green, 2.4);
		}
		else {
			$green = $green / 12.92;
		}
		
		if ($blue > 0.04045) {
			$blue = ($blue + 0.055) / 1.055;
			$blue = pow($blue, 2.4);
		}
		else {
			$blue = $blue / 12.92;
		}
		
		$red *= 100;
		$green *= 100;
		$blue *= 100;
		
		// [x, y, z]
		return [
			$red * 0.4124 + $green * 0.3576 + $blue * 0.1805,
			$red * 0.2126 + $green * 0.7152 + $blue * 0.0722,
			$red * 0.0193 + $green * 0.1192 + $blue * 0.9505
		];
	}
	
	protected function xyzToLab($xyz) {
		$x = $xyz[0] / 95.047;
		$y = $xyz[1] / 100;
		$z = $xyz[2] / 108.883;
		
		if ($x > 0.008856) {
			$x = pow($x, 1 / 3);
		}
		else {
			$x = 7.787 * $x + 16 / 116;
		}
		
		if ($y > 0.008856) {
			$y = pow($y, 1 / 3);
		}
		else {
			$y = (7.787 * $y) + (16 / 116);
		}
		
		if ($z > 0.008856) {
			$z = pow($z, 1 / 3);
		}
		else {
			$z = 7.787 * $z + 16 / 116;
		}
		
		// [l, a, b]
		return [
			116 * $y - 16,
			500 * ($x - $y),
			200 * ($y - $z)
		];
	}
	
	protected function deltaE_cie1994($lab1, $lab2) {
		// Delta E (CIE 1994) difference of two colors
		// http://www.brucelindbloom.com/index.html?Eqn_DeltaE_CIE94.html
		$c1 = sqrt($lab1[1] * $lab1[1] + $lab1[2] * $lab1[2]);
		$c2 = sqrt($lab2[1] * $lab2[1] + $lab2[2] * $lab2[2]);
		
		$dc = $c1 - $c2;
		$dl = $lab1[0] - $lab2[0];
		$da = $lab1[1] - $lab2[1];
		$db = $lab1[2] - $lab2[2];
		$dh = ($da * $da) + ($db * $db) - ($dc * $dc);
		$dh = ($dh < 0) ? 0 : sqrt($dh);
		
		$first = $dl;
		$second = $dc / (1 + 0.045 * $c1);
		$third = $dh / (1 + 0.015 * $c1);
		
		return sqrt($first * $first + $second * $second + $third * $third);
	}
}
