<?php

namespace wcf\system\template\plugin;

use wcf\system\html\simple\HtmlSimpleParser;
use wcf\system\template\TemplateEngine;

/**
 * Template block plugin handling embedded object data.
 *
 * This template plugin is intended for internal use only.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class EmbeddedObjectBlockTemplatePlugin implements IBlockTemplatePlugin
{
    /**
     * internal loop counter
     * @var int
     */
    protected $counter = 0;

    #[\Override]
    public function execute(array $tagArgs, string $blockContent, TemplateEngine $tplObj)
    {
        $serializedData = \base64_decode($blockContent, true);
        if ($serializedData === false) {
            throw new \UnexpectedValueException("The block content is not valid base64 data.");
        }

        $data = \unserialize($serializedData, ['allowed_classes' => false]);
        if (!\is_array($data)) {
            throw new \UnexpectedValueException("The block content does not contain valid embedded object data.");
        }

        return HtmlSimpleParser::getInstance()->replaceTag($data);
    }

    #[\Override]
    public function init(array $tagArgs, TemplateEngine $tplObj)
    {
        $this->counter = 0;
    }

    #[\Override]
    public function next(TemplateEngine $tplObj)
    {
        if ($this->counter === 0) {
            $this->counter++;

            return true;
        }

        return false;
    }
}
