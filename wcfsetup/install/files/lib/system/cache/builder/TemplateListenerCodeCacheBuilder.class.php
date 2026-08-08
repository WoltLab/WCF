<?php

namespace wcf\system\cache\builder;

use wcf\data\template\listener\TemplateListenerList;

/**
 * Caches the template listener code for a certain environment.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TemplateListenerCodeCacheBuilder extends AbstractCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters)
    {
        // get template codes for specified template
        $templateListenerList = new TemplateListenerList();
        $templateListenerList->getConditionBuilder()->add(
            "template_listener.environment = ?",
            [$parameters['environment']]
        );
        $templateListenerList->sqlOrderBy = 'template_listener.niceValue ASC, template_listener.listenerID ASC';
        $templateListenerList->readObjects();

        $data = [];
        foreach ($templateListenerList->getObjects() as $templateListener) {
            if (!isset($data[$templateListener->templateName])) {
                $data[$templateListener->templateName] = [];
            }

            $templateCode = $templateListener->templateCode;
            $listenerOptions = $templateListener->options;
            $listenerPermissions = $templateListener->permissions;
            // wrap template listener code in if condition for options
            // and permissions check
            if (
                ($listenerOptions !== null && $listenerOptions !== '')
                || ($listenerPermissions !== null && $listenerPermissions !== '')
            ) {
                $templateCode = '{if ';

                $options = [];
                if ($listenerOptions !== null && $listenerOptions !== '') {
                    $options = \explode(',', \strtoupper($listenerOptions));

                    $options = \array_map(static function ($value) {
                        return "('{$value}'|defined && {$value})";
                    }, $options);

                    $templateCode .= '(' . \implode(' || ', $options) . ')';
                }
                if ($listenerPermissions !== null && $listenerPermissions !== '') {
                    $permissions = \explode(',', $listenerPermissions);

                    $permissions = \array_map(static function ($value) {
                        return "\$__wcf->session->getPermission('" . $value . "')";
                    }, $permissions);

                    if (!empty($options)) {
                        $templateCode .= " && ";
                    }

                    $templateCode .= '(' . \implode(' || ', $permissions) . ')';
                }

                $templateCode .= '}' . $templateListener->templateCode . '{/if}';
            }

            $data[$templateListener->templateName][$templateListener->eventName][] = $templateCode;
        }

        return $data;
    }
}
