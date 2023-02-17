<?php

namespace wcf\data\language;

use wcf\system\exception\SystemException;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\system\WCF;
use wcf\util\XML;

/**
 * SetupLanguage is a modification of Language used during the setup process.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SetupLanguage extends Language
{
    /**
     * @inheritDoc
     */
    public function __construct($languageID, array $row, ?Language $language = null)
    {
        if ($row === null) {
            throw new SystemException('SetupLanguage only accepts an existing dataset.');
        }

        parent::__construct(null, $row, null);
    }

    /**
     * @inheritDoc
     */
    protected function loadCategory(string $category): bool
    {
        if ($category !== 'wcf.global') {
            return false;
        }

        \assert($this->items === []);

        // We must not access LanguageFactory, because it is not usable in
        // early WCFSetup initialization.
        $compiler = new TemplateScriptingCompiler(WCF::getTPL());

        $xml = new XML();
        $xml->load(TMP_DIR . 'setup/lang/setup_' . $this->languageCode . '.xml');

        $items = $xml->xpath()->query('/ns:language/ns:category/ns:item');
        foreach ($items as $item) {
            \assert($item instanceof \DOMElement);

            $name = $item->getAttribute('name');
            $value = $item->nodeValue;

            $this->items[$name] = $value;

            if (\str_contains($value, '{')) {
                // compile dynamic language variables
                $compiled = $compiler->compileString($name, $value);
                $this->dynamicItems[$name] = $compiled['template'];
            }
        }

        return true;
    }
}
