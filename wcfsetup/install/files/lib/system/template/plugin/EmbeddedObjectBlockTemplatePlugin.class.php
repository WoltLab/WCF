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
 * @since   3.0
 */
class EmbeddedObjectBlockTemplatePlugin implements IBlockTemplatePlugin
{
    /**
     * internal loop counter
     * @var int
     */
    protected $counter = 0;

    /**
     * @inheritDoc
     */
    public function execute($tagArgs, $blockContent, TemplateEngine $tplObj)
    {
        $data = \unserialize(\base64_decode($blockContent));

        return HtmlSimpleParser::getInstance()->replaceTag($data);
    }

    /**
     * @inheritDoc
     */
    public function init($tagArgs, TemplateEngine $tplObj)
    {
        $this->counter = 0;
    }

    /**
     * @inheritDoc
     */
    public function next(TemplateEngine $tplObj)
    {
        if ($this->counter == 0) {
            $this->counter++;

            return true;
        }

        return false;
    }
}
