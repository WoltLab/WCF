<?php

namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of cascading style sheets.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Bbcode\Highlighter
 * @deprecated  since 5.2, use Prism to highlight your code.
 */
class CssHighlighter extends Highlighter
{
    /**
     * @inheritDoc
     */
    protected function highlightNumbers($string)
    {
        $string = \preg_replace(
            '!(?<=' . $this->separatorsRegEx . ')(-?\d*\.?\d+(?:px|pt|em|%|ex|in|cm|mm|pc)?)(?=' . $this->separatorsRegEx . ')!i',
            '<span class="hlNumbers">\\0</span>',
            $string
        );

        // highlight colors (hexadecimal numbers)
        return \preg_replace(
            '!(?<=' . $this->separatorsRegEx . ')(#([0-9a-f]{3}|[0-9a-f]{6}))(?=' . $this->separatorsRegEx . ')!i',
            '<span class="hlColors">\\0</span>',
            $string
        );
    }

    /**
     * @inheritDoc
     */
    protected function highlightKeywords($string)
    {
        $string = parent::highlightKeywords($string);

        return \preg_replace(
            '!(?<=' . $this->separatorsRegEx . ')(@[a-z0-9-]+)(?=' . $this->separatorsRegEx . ')!i',
            '<span class="hlKeywords5">\\0</span>',
            $string
        );
    }

    /**
     * @inheritDoc
     */
    public function highlight($string)
    {
        $string = \str_replace(
            'span',
            '053a0024219422ca9215c0a3ed0578ee76cff477',
            $string
        ); // fix to not highlight the spans of the highlighter
        $string = \str_replace(':link', ':li@@nk', $string); // fix to highlight pseudo-class different than tag
        $string = \str_replace(
            ['right:', 'left:'],
            ['r@@ight:', 'l@@eft:'],
            $string
        ); // fix to highlight properties different than values

        $string = parent::highlight($string);

        $string = \str_replace(
            ['r@@ight', 'l@@eft'],
            ['right', 'left'],
            $string
        ); // fix to highlight properties different than values
        $string = \str_replace('li@@nk', 'link', $string); // fix to highlight pseudo-class different than tag

        return \str_replace(
            '053a0024219422ca9215c0a3ed0578ee76cff477',
            'span',
            $string
        ); // fix to not highlight the spans of the highlighter
    }

    /**
     * @inheritDoc
     */
    protected $singleLineComment = ['//'];

    /**
     * @inheritDoc
     */
    protected $separators = ['(', ')', '{', '}', ';', '[', ']', ':', ',', '.'];

    /**
     * @inheritDoc
     */
    protected $keywords1 = [
        'align-content',
        'align-items',
        'align-self',
        'animation',
        'animation-delay',
        'animation-direction',
        'animation-duration',
        'animation-fill-mode',
        'animation-iteration-count',
        'animation-name',
        'animation-play-state',
        'animation-timing-function',
        'azimuth',
        'backface-visibility',
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
        'border-image',
        'border-image-outset',
        'border-image-repeat',
        'border-image-slice',
        'border-image-source',
        'border-image-width',
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
        'box-sizing',
        'caption-side',
        'clear',
        'clip',
        'color',
        'column-count',
        'column-fill',
        'column-gap',
        'column-rule',
        'column-rule-color',
        'column-rule-style',
        'column-rule-width',
        'column-span',
        'column-width',
        'columns',
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
        'flex',
        'flex-basis',
        'flex-direction',
        'flex-flow',
        'flex-grow',
        'flex-shrink',
        'flex-wrap',
        'float',
        'font',
        'font-family',
        'font-size',
        'font-size-adjust',
        'font-stretch',
        'font-style',
        'font-variant',
        'font-weight',
        'height',
        'justify-content',
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
        'order',
        'orphans',
        'outline',
        'outline-color',
        'outline-offset',
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
        'perspective',
        'perspective-origin',
        'pitch',
        'pitch-range',
        'play-during',
        'position',
        'quotes',
        'resize',
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
        'tab-size',
        'table-layout',
        'text-align',
        'text-align-last',
        'text-decoration',
        'text-decoration-color',
        'text-decoration-line',
        'text-decoration-style',
        'text-indent',
        'text-justify',
        'text-overflow',
        'text-shadow',
        'text-transform',
        'top',
        'transform',
        'transform-origin',
        'transform-style',
        'transition',
        'transition-delay',
        'transition-duration',
        'transition-property',
        'transition-timing-function',
        'unicode-bidi',
        'vertical-align',
        'visibility',
        'voice-family',
        'volume',
        'white-space',
        'widows',
        'width',
        'word-break',
        'word-spacing',
        'word-wrap',
        'z-index',
        '!important',
        '@charset',
        '@font-face',
        '@import',
        '@keyframes',
        '@media',
        '@page',
    ];

    /**
     * @inheritDoc
     */
    protected $keywords2 = [
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
        'table', // table
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
        'caption', // caption
        'icon',
        'menu', // menu
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
        'code',
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
        'embed',
        'bidi-override',
        'baseline',
        'sub',
        'super',
        'text-top',
        'middle',
        'text-bottom',
        'silent',
        'x-soft',
        'soft',
        'loud',
        'x-loud',
        'pre',
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
        'small',
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
        'rgba',
        'flex',
        'inline-flex',
        'initial',
        'matrix',
        'matrix3d',
        'perspective',
        'rotate',
        'rotate3d',
        'rotatex',
        'rotatey',
        'rotatez',
        'scale',
        'scale3d',
        'scalex',
        'scaley',
        'scalez',
        'skew',
        'skwex',
        'skewy',
        'skewz',
        'translate',
        'translate3d',
        'translatex',
        'translatey',
        'translatez',
    ];

    /**
     * @inheritDoc
     */
    protected $keywords3 = [
        'active',
        'after',
        'any',
        'before',
        'checked',
        'default',
        'dir',
        'disabled',
        'empty',
        'enabled',
        'first',
        'first-child',
        'first-letter',
        'first-line',
        'first-of-type',
        'focus',
        'fullscreen',
        'indeterminate',
        'in-range',
        'invalid',
        'lang',
        'last-child',
        'last-of-type',
        'li@@nk', // link
        'hover',
        'not',
        'nth-child',
        'nth-last-child',
        'nth-last-of-type',
        'nth-of-type',
        'only-child',
        'only-of-type',
        'optional',
        'out-of-range',
        'read-only',
        'read-write',
        'root',
        'scope',
        'target',
        'valid',
        'visited',
    ];

    /**
     * @inheritDoc
     */
    protected $keywords4 = [
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
        'wbr',
    ];

    /**
     * @inheritDoc
     */
    public $keywords5 = [
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
        '&',
    ];
}
