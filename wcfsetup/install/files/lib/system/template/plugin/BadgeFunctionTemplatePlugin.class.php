<?php

namespace wcf\system\template\plugin;

use wcf\system\style\BadgeType;
use wcf\system\template\TemplateEngine;

/**
 * Template function plugin that renders a status badge with a unique color,
 * icon and label. The optional `label` replaces the default label with a
 * custom language item.
 *
 * Usage:
 *  {badge type='deleted'}
 *  {badge type='featured' class='jsLabelFeatured'}
 *  {badge type='featured' label='wcf.xyz.custom'}
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class BadgeFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    #[\Override]
    public function execute(array $tagArgs, TemplateEngine $tplObj): string
    {
        $type = $tagArgs['type'] ?? '';
        if (!\is_string($type) || $type === '') {
            throw new \InvalidArgumentException("The 'type' attribute must be present and non-empty.");
        }

        $badgeType = BadgeType::tryFrom($type);
        if ($badgeType === null) {
            throw new \InvalidArgumentException("An unsupported type '{$type}' was specified.");
        }

        $label = $tagArgs['label'] ?? '';
        if (!\is_string($label)) {
            throw new \InvalidArgumentException("The 'label' attribute must be a language item name.");
        }

        return $badgeType->toHtml(
            (string)($tagArgs['class'] ?? ''),
            $label !== '' ? $label : null,
        );
    }
}
