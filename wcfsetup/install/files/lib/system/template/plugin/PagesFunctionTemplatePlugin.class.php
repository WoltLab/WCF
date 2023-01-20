<?php

namespace wcf\system\template\plugin;

use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template function plugin which generates sliding pagers.
 *
 * Starting with WoltLab Suite 6.0 you should migrate to the
 * new web component that provides a similar API.
 *
 * Example:
 * <woltlab-core-pagination
 *     page="1"
 *     count="10"
 *     url="{link application='foo' controller='bar'}{/link}"
 * ></woltlab-core-pagination>
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.0 Migrate to `<woltlab-core-pagination>`
 */
final class PagesFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        // needed params: controller, link, page, pages
        if (!isset($tagArgs['link'])) {
            throw new SystemException("missing 'link' argument in pages tag");
        }
        if (!isset($tagArgs['controller'])) {
            throw new SystemException("missing 'controller' argument in pages tag");
        }
        if (!isset($tagArgs['pages'])) {
            if (($tagArgs['pages'] = $tplObj->get('pages')) === null) {
                throw new SystemException("missing 'pages' argument in pages tag");
            }
        }

        $html = '';

        if ($tagArgs['pages'] > 1) {
            // create and encode route link
            $parameters = [];
            if (isset($tagArgs['id'])) {
                $parameters['id'] = $tagArgs['id'];
            }
            if (isset($tagArgs['title'])) {
                $parameters['title'] = $tagArgs['title'];
            }
            if (isset($tagArgs['object'])) {
                $parameters['object'] = $tagArgs['object'];
            }
            if (isset($tagArgs['application'])) {
                $parameters['application'] = $tagArgs['application'];
            }

            // The previous implementation required the `pageNo=%d` placeholder
            // to be present in the link argument.
            $tagArgs['link'] = \preg_replace('~(?:^pageNo=%d(?:&|$))|(?:&pageNo=%d(?=&|$))~', '', $tagArgs['link']);

            $link = StringUtil::encodeHTML(LinkHandler::getInstance()->getLink(
                $tagArgs['controller'],
                $parameters,
                $tagArgs['link']
            ));

            if (!isset($tagArgs['page'])) {
                if (($tagArgs['page'] = $tplObj->get('pageNo')) === null) {
                    $tagArgs['page'] = 0;
                }
            }

            $html = \sprintf(
                '<woltlab-core-pagination page="%d" count="%d" url="%s"></woltlab-core-pagination>',
                $tagArgs['page'],
                $tagArgs['pages'],
                $link,
            );
        }

        // assign html output to template var
        if (isset($tagArgs['assign'])) {
            $tplObj->assign($tagArgs['assign'], $html);
            if (!isset($tagArgs['print']) || !$tagArgs['print']) {
                return '';
            }
        }

        return $html;
    }
}
