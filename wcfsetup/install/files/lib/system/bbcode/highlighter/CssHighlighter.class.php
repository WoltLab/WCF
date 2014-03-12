<?php
namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of cascading style sheets.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class CssHighlighter extends Highlighter {
	/**
	 * temporary string replacement map for properties that can also be tags
	 * @var	array<string>
	 */
	public static $duplicates = array(
		'table:' => 't@@able:',
		'caption:' => 'c@@aption:',
		'menu:' => 'm@@enu:',
		'code:' => 'c@@ode:',
		'sub:' => 's@@ub:',
		'pre:' => 'p@@re:',
		'small:' => 's@@mall:'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlightNumbers()
	 */
	protected function highlightNumbers($string) {
		$string = preg_replace('!(?<='.$this->separatorsRegEx.')(-?\d*\.?\d+(?:px|pt|em|%|ex|in|cm|mm|pc)?)(?='.$this->separatorsRegEx.')!i', '<span class="hlNumbers">\\0</span>', $string);
		
		// highlight colors (hexadecimal numbers)
		$string = preg_replace('!(?<='.$this->separatorsRegEx.')(#([0-9a-f]{3}|[0-9a-f]{6}))(?='.$this->separatorsRegEx.')!i', '<span class="hlColors">\\0</span>', $string);
		
		return $string;
	}
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlightKeywords()
	 */
	protected function highlightKeywords($string) {
		$string = parent::highlightKeywords($string);
		$string = preg_replace('!(?<='.$this->separatorsRegEx.')(@[a-z0-9-]+)(?='.$this->separatorsRegEx.')!i', '<span class="hlKeywords5">\\0</span>', $string);
		
		return $string;
	}
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlight()
	 */
	public function highlight($string) {
		$string = str_replace('span', '053a0024219422ca9215c0a3ed0578ee76cff477', $string); // fix to not highlight the spans of the highlighter
		$string = str_replace(':link', ':li@@nk', $string); // fix to highlight pseudo-class different than tag
		$string = str_replace(array('right:', 'left:'), array('r@@ight:', 'l@@eft:'), $string); // fix to highlight properties different than values
		$string = strtr($string, self::$duplicates); // fix to highlight properties different than tags
		
		$string = parent::highlight($string);
		
		$string = strtr($string, array_flip(self::$duplicates)); // fix to highlight properties different than tags
		$string = str_replace(array('r@@ight', 'l@@eft'), array('right', 'left'), $string); // fix to highlight properties different than values
		$string = str_replace('li@@nk', 'link', $string); // fix to highlight pseudo-class different than tag
		return str_replace('053a0024219422ca9215c0a3ed0578ee76cff477', 'span', $string); // fix to not highlight the spans of the highlighter
	}
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$singleLineComment
	 */
	protected $singleLineComment = array('//');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$separators
	 */
	protected $separators = array('(', ')', '{', '}', ';', '[', ']', ':', ',', '.');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords1
	 */
	protected $keywords1 = array(
		'azimuth',
		'background',
		'background-attachment',
		'background-clip',
		'background-color',
		'background-image',
		'background-origin',
		'background-position',
		'background-repeat',
		'background-size',
		'border',
		'border-bottom',
		'border-bottom-color',
		'border-bottom-radius',
		'border-bottom-left-radius',
		'border-bottom-right-radius',
		'border-bottom-style',
		'border-bottom-width',
		'border-collapse',
		'border-color',
		'border-l@@eft',
		'border-left-color',
		'border-left-radius',
		'border-left-style',
		'border-left-width',
		'border-radius',
		'border-r@@ight',
		'border-right-color',
		'border-right-radius',
		'border-right-style',
		'border-right-width',
		'border-spacing',
		'border-style',
		'border-top',
		'border-top-color',
		'border-top-radius',
		'border-top-left-radius',
		'border-top-right-radius',
		'border-top-style',
		'border-top-width',
		'border-width',
		'bottom',
		'box-shadow',
		'caption-side',
		'clear',
		'clip',
		'color',
		'content',
		'counter-increment',
		'counter-reset',
		'cue',
		'cue-after',
		'cue-before',
		'cursor',
		'direction',
		'display',
		'elevation',
		'empty-cells',
		'float',
		'font',
		'font-family',
		'font-size',
		'font-style',
		'font-variant',
		'font-weight',
		'height',
		'l@@eft',
		'letter-spacing',
		'line-height',
		'list-style',
		'list-style-image',
		'list-style-position',
		'list-style-type',
		'margin',
		'margin-bottom',
		'margin-l@@eft',
		'margin-r@@ight',
		'margin-top',
		'max-height',
		'max-width',
		'min-height',
		'min-width',
		'opacity',
		'orphans',
		'outline',
		'outline-color',
		'outline-style',
		'outline-width',
		'overflow',
		'overflow-x',
		'overflow-y',
		'padding',
		'padding-bottom',
		'padding-l@@eft',
		'padding-r@@ight',
		'padding-top',
		'page-break-after',
		'page-break-before',
		'page-break-inside',
		'pause',
		'pause-after',
		'pause-before',
		'pitch',
		'pitch-range',
		'play-during',
		'position',
		'quotes',
		'richness',
		'r@@ight',
		'scrollbar-3dlight-color',
		'scrollbar-arrow-color',
		'scrollbar-base-color',
		'scrollbar-darkshadow-color',
		'scrollbar-face-color',
		'scrollbar-highlight-color',
		'scrollbar-shadow-color',
		'scrollbar-track-color',
		'speak',
		'speak-header',
		'speak-numeral',
		'speak-punctuation',
		'speech-rate',
		'stress',
		'table-layout',
		'text-align',
		'text-decoration',
		'text-indent',
		'text-overflow',
		'text-shadow',
		'text-transform',
		'top',
		'unicode-bidi',
		'vertical-align',
		'visibility',
		'voice-family',
		'volume',
		'white-space',
		'widows',
		'width',
		'word-spacing',
		'word-wrap',
		'z-index',
		'!important',
		'@import',
		'@media'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords2
	 */
	protected $keywords2 = array(
		'left-side',
		'far-left',
		'left',
		'center-left',
		'center-right',
		'center',
		'far-right',
		'right-side',
		'right',
		'behind',
		'leftwards',
		'rightwards',
		'inherit',
		'scroll',
		'fixed',
		'transparent',
		'none',
		'repeat-x',
		'repeat-y',
		'repeat',
		'no-repeat',
		'collapse',
		'separate',
		'auto',
		'top',
		'bottom',
		'both',
		'open-quote',
		'close-quote',
		'no-open-quote',
		'no-close-quote',
		'crosshair',
		'default',
		'pointer',
		'move',
		'e-resize',
		'ne-resize',
		'nw-resize',
		'n-resize',
		'se-resize',
		'sw-resize',
		's-resize',
		'text',
		'wait',
		'help',
		'ltr',
		'rtl',
		'inline',
		'block',
		'list-item',
		'run-in',
		'compact',
		'marker',
		't@@able', // table
		'inline-table',
		'table-row-group',
		'table-header-group',
		'table-footer-group',
		'table-row',
		'table-column-group',
		'table-column',
		'table-cell',
		'table-caption',
		'below',
		'level',
		'above',
		'higher',
		'lower',
		'show',
		'hide',
		'c@@aption', // caption
		'icon',
		'm@@enu', // menu
		'message-box',
		'small-caption',
		'status-bar',
		'normal',
		'wider',
		'narrower',
		'ultra-condensed',
		'extra-condensed',
		'condensed',
		'semi-condensed',
		'semi-expanded',
		'expanded',
		'extra-expanded',
		'ultra-expanded',
		'italic',
		'oblique',
		'small-caps',
		'bold',
		'bolder',
		'lighter',
		'inside',
		'outside',
		'disc',
		'circle',
		'square',
		'decimal',
		'decimal-leading-zero',
		'lower-roman',
		'upper-roman',
		'lower-greek',
		'lower-alpha',
		'lower-latin',
		'upper-alpha',
		'upper-latin',
		'hebrew',
		'armenian',
		'georgian',
		'cjk-ideographic',
		'hiragana',
		'katakana',
		'hiragana-iroha',
		'katakana-iroha',
		'crop',
		'cross',
		'invert',
		'visible',
		'hidden',
		'always',
		'avoid',
		'x-low',
		'low',
		'medium',
		'high',
		'x-high',
		'mix?',
		'repeat?',
		'static',
		'relative',
		'absolute',
		'portrait',
		'landscape',
		'spell-out',
		'once',
		'digits',
		'continuous',
		'c@@ode', // code
		'x-slow',
		'slow',
		'fast',
		'x-fast',
		'faster',
		'slower',
		'justify',
		'underline',
		'overline',
		'line-through',
		'blink',
		'capitalize',
		'uppercase',
		'lowercase',
		'e@@mbed', // embed
		'bidi-override',
		'baseline',
		's@@ub', // sub
		'super',
		'text-top',
		'middle',
		'text-bottom',
		'silent',
		'x-soft',
		'soft',
		'loud',
		'x-loud',
		'p@@re', // pre
		'nowrap',
		'serif',
		'sans-serif',
		'cursive',
		'fantasy',
		'monospace',
		'empty',
		'string',
		'strict',
		'loose',
		'char',
		'true',
		'false',
		'dotted',
		'dashed',
		'solid',
		'double',
		'groove',
		'ridge',
		'inset',
		'outset',
		'larger',
		'smaller',
		'xx-small',
		'x-small',
		's@@mall', // small
		'large',
		'x-large',
		'xx-large',
		'all',
		'newspaper',
		'distribute',
		'distribute-all-lines',
		'distribute-center-last',
		'inter-word',
		'inter-ideograph',
		'inter-cluster',
		'kashida',
		'ideograph-alpha',
		'ideograph-numeric',
		'ideograph-parenthesis',
		'ideograph-space',
		'keep-all',
		'break-all',
		'break-word',
		'lr-tb',
		'tb-rl',
		'thin',
		'thick',
		'inline-block',
		'w-resize',
		'hand',
		'distribute-letter',
		'distribute-space',
		'whitespace',
		'male',
		'female',
		'child',
		'print',
		'screen',
		'tty',
		'aural',
		'all',
		'braille',
		'embossed',
		'handheld',
		'projection',
		'tv',
		'hsl',
		'hsla',
		'rgb',
		'rgba'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords3
	 */
	protected $keywords3 = array(
		'active',
		'after',
		'before',
		'checked',
		'disabled',
		'empty',
		'enabled',
		'first-child',
		'first-letter',
		'first-line',
		'first-of-type',
		'focus',
		'lang',
		'last-child',
		'last-of-type',
		'li@@nk', // link
		'hover',
		'not',
		'nth-child',
		'nth-last-child',
		'nth-of-type',
		'nth-last-of-type',
		'only-child',
		'only-of-type',
		'root',
		'target',
		'visited'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords4
	 */
	protected $keywords4 = array(
		'abbr',
		'acronym',
		'address',
		'area',
		'article',
		'aside',
		'audio',
		'a',
		'base',
		'bdi',
		'bdo',
		'big',
		'blockquote',
		'body',
		'br',
		'button',
		'b',
		'canvas',
		'caption',
		'cite',
		'code',
		'col',
		'colgroup',
		'command',
		'datalist',
		'dd',
		'del',
		'details',
		'dfn',
		'div',
		'dl',
		'dt',
		'embed',
		'em',
		'fieldset',
		'figcaption',
		'figure',
		'footer',
		'form',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'head',
		'header',
		'hgroup',
		'hr',
		'html',
		'iframe',
		'img',
		'input',
		'ins',
		'i',
		'kbd',
		'keygen',
		'label',
		'legend',
		'li',
		'link',
		'map',
		'mark',
		'menu',
		'meta',
		'meter',
		'nav',
		'noscript',
		'object',
		'ol',
		'optgroup',
		'option',
		'output',
		'param',
		'pre',
		'progress',
		'p',
		'q',
		'rbc',
		'rb',
		'rp',
		'rtc',
		'rt',
		'ruby',
		'samp',
		'script',
		'section',
		'select',
		'small',
		'source',
		'053a0024219422ca9215c0a3ed0578ee76cff477', // span
		'strong',
		'style',
		'sub',
		'summary',
		'sup',
		'table',
		'tbody',
		'td',
		'textarea',
		'tfoot',
		'thead',
		'th',
		'time',
		'title',
		'track',
		'tr',
		'tt',
		'ul',
		'u',
		'var',
		'video',
		'wbr'
	);
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$keywords5
	 */
	public $keywords5 = array(
		// modifying
		'darken',
		'lighten',
		'saturate',
		'desaturate',
		'fadein',
		'fadeout',
		'fade',
		'spin',
		'mix',
		// reading
		'lightness',
		'hue',
		'saturation',
		'alpha',
		'percentage',
		// typechecking
		'isnumber',
		'iscolor',
		'isstring',
		'iskeyword',
		'isurl',
		'ispixel',
		'ispercentage',
		'isem',
		// math
		'round',
		'ceil',
		'floor',
		// if
		'when',
		'not',
		'and',
		'true',
		// others
		'&'
	);
}
