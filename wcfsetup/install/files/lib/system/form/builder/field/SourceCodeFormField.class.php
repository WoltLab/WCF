<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field to enter source code.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 */
final class SourceCodeFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    IImmutableFormField,
    IMaximumLengthFormField,
    IMinimumLengthFormField
{
    use TAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TMaximumLengthFormField;
    use TMinimumLengthFormField;

    /**
     * @var string|null
     */
    protected $language;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_sourceCodeFormField';

    public const LANGUAGES = [
        // Languages supported by CodeMirror itself.
        'apl',
        'asciiarmor',
        'asn.1',
        'asterisk',
        'brainfuck',
        'clike',
        'clojure',
        'cmake',
        'cobol',
        'coffeescript',
        'commonlisp',
        'crystal',
        'css',
        'cypher',
        'd',
        'dart',
        'diff',
        'django',
        'dockerfile',
        'dtd',
        'dylan',
        'ebnf',
        'ecl',
        'eiffel',
        'elm',
        'erlang',
        'factor',
        'fcl',
        'forth',
        'fortran',
        'gas',
        'gfm',
        'gherkin',
        'go',
        'groovy',
        'haml',
        'handlebars',
        'haskell',
        'haskell-literate',
        'haxe',
        'htmlembedded',
        'htmlmixed',
        'http',
        'idl',
        'javascript',
        'jinja2',
        'jsx',
        'julia',
        'livescript',
        'lua',
        'markdown',
        'mathematica',
        'mbox',
        'mirc',
        'mllike',
        'modelica',
        'mscgen',
        'mumps',
        'nginx',
        'nsis',
        'ntriples',
        'octave',
        'oz',
        'pascal',
        'pegjs',
        'perl',
        'php',
        'pig',
        'powershell',
        'properties',
        'protobuf',
        'pug',
        'puppet',
        'python',
        'q',
        'r',
        'rpm',
        'rst',
        'ruby',
        'rust',
        'sas',
        'sass',
        'scheme',
        'shell',
        'sieve',
        'slim',
        'smalltalk',
        'smarty',
        'solr',
        'soy',
        'sparql',
        'spreadsheet',
        'sql',
        'stex',
        'stylus',
        'swift',
        'tcl',
        'textile',
        'tiddlywiki',
        'tiki',
        'toml',
        'tornado',
        'troff',
        'ttcn',
        'ttcn-cfg',
        'turtle',
        'twig',
        'vb',
        'vbscript',
        'velocity',
        'verilog',
        'vhdl',
        'vue',
        'wast',
        'webidl',
        'xml',
        'xquery',
        'yacas',
        'yaml',
        'yaml-frontmatter',
        'z80',

        // Additional language added/supported by us.
        'smartymixed',
    ];

    /**
     * Returns the source code language used or `null` if no language is specified.
     *
     * By default, `null` is returned.
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * Sets the source code language used or unset a previously set language if `null` is given.
     *
     * @throws  \InvalidArgumentException   if given language is unsupported
     */
    public function language(?string $language): self
    {
        if (!\in_array($language, self::LANGUAGES)) {
            throw new \InvalidArgumentException(
                "Unsupported language '{$language}' given for field '{$this->getId()}'."
            );
        }

        $this->language = $language;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->value === null || $this->value === '') {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }
        } else {
            $this->validateMinimumLength($this->value);
            $this->validateMaximumLength($this->value);
        }

        parent::validate();
    }
}
