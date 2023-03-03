<?php

namespace wcf\data\language;

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
final class SetupLanguage extends Language
{
    /**
     * @inheritDoc
     */
    public function __construct(string $languageCode)
    {
        parent::__construct(null, ['languageCode' => $languageCode], null);

        if (!\file_exists($this->getXmlFilename())) {
            throw new \InvalidArgumentException(\sprintf(
                "Invalid languageCode '%s' given. The XML file '%s' does not exist.",
                $languageCode,
                $this->getXmlFilename()
            ));
        }
    }

    private function getXmlFilename(): string
    {
        return TMP_DIR . 'setup/lang/setup_' . $this->languageCode . '.xml';
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
        $xml->load($this->getXmlFilename());

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
