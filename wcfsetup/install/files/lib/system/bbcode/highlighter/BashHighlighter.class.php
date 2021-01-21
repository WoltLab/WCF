<?php

namespace wcf\system\bbcode\highlighter;

/**
 * Highlights syntax of bash scripts.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Bbcode\Highlighter
 * @deprecated  since 5.2, use Prism to highlight your code.
 */
class BashHighlighter extends Highlighter
{
    /**
     * @inheritDoc
     */
    protected $separators = [';', '='];

    /**
     * @inheritDoc
     */
    protected $quotes = ['"', "'", '`'];

    /**
     * @inheritDoc
     */
    protected $singleLineComment = ['#'];

    /**
     * @inheritDoc
     */
    protected $commentStart = [];

    /**
     * @inheritDoc
     */
    protected $commentEnd = [];

    /**
     * @inheritDoc
     */
    protected $operators = ['||', '&&', '&', '|', '<<=', '>>=', '<<', '+=', '-=', '*=', '/=', '%=',
        '-gt', '-lt', '-n', '-a', '-o',
        '+', '-', '*', '/', '%', '<', '?', ':', '==', '!=', '=', '!', '>', '2>', '>>', ];

    /**
     * @inheritDoc
     */
    protected $keywords1 = [
        'true',
        'false',
    ];

    /**
     * @inheritDoc
     */
    protected $keywords2 = [
        'if',
        'then',
        'else',
        'fi',
        'for',
        'until',
        'while',
        'do',
        'done',
        'case',
        'in',
        'esac',
    ];

    /**
     * @inheritDoc
     */
    protected $keywords3 = [
        'echo',
        'exit',
        'unset',
        'read',
        '[', ']', 'test',
        'let',
        'sed',
        'grep',
        'awk',
    ];

    /**
     * @inheritDoc
     */
    protected $keywords4 = [
        '$?',
    ];
}
