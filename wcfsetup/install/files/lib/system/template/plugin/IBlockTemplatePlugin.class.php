<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;

/**
 * Block functions encloses a template block and operate on the contents of this block.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IBlockTemplatePlugin
{
    /**
     * Executes this template block.
     *
     * @param array<string|int, mixed> $tagArgs
     * @param TemplateEngine $tplObj
     * @return  string
     */
    public function execute(array $tagArgs, string $blockContent, TemplateEngine $tplObj);

    /**
     * Initialises this template block.
     *
     * @param array<string|int, mixed> $tagArgs
     * @param TemplateEngine $tplObj
     * @return void
     */
    public function init(array $tagArgs, TemplateEngine $tplObj);

    /**
     * This function is called before every execution of this block function.
     *
     * @param TemplateEngine $tplObj
     * @return  bool
     */
    public function next(TemplateEngine $tplObj);
}
