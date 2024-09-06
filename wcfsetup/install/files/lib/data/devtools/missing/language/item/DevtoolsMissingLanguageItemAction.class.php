<?php

namespace wcf\data\devtools\missing\language\item;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IDeleteAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Executes missing language item log entry-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.3
 *
 * @method  DevtoolsMissingLanguageItemEditor[] getObjects()
 * @method  DevtoolsMissingLanguageItemEditor   getSingleObject()
 */
class DevtoolsMissingLanguageItemAction extends AbstractDatabaseObjectAction implements IDeleteAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.configuration.package.canInstallPackage'];

    /**
     * Logs a missing language item.
     */
    public function logLanguageItem()
    {
        $stackTraceData = \array_map(static function ($item) {
            $item['args'] = \implode(', ', \array_map(static function ($item) {
                switch (\gettype($item)) {
                    case 'integer':
                    case 'double':
                        return $item;
                    case 'NULL':
                        return 'null';
                    case 'string':
                        return "'" . StringUtil::encodeHTML(\addcslashes(StringUtil::truncate($item), "\\'\n\r\t")) . "'";
                    case 'boolean':
                        return $item ? 'true' : 'false';
                    case 'array':
                        $keys = \array_keys($item);
                        if (\count($keys) > 5) {
                            return "[ " . \count($keys) . " items ]";
                        }

                        return '[ ' . \implode(', ', \array_map(static function ($item) {
                            return $item . ' => ';
                        }, $keys)) . ']';
                    case 'object':
                        if ($item instanceof \UnitEnum) {
                            return $item::class . '::' . $item->name;
                        }

                        return $item::class;
                    case 'resource':
                        return 'resource(' . \get_resource_type($item) . ')';
                    case 'resource (closed)':
                        return 'resource (closed)';
                }
            }, $item['args']));

            return $item;
        }, \wcf\functions\exception\sanitizeStacktrace(new \Exception(), true));

        $stackTrace = JSON::encode($stackTraceData);

        $sql = "INSERT INTO             wcf1_devtools_missing_language_item
                                        (languageID, languageItem, lastTime, stackTrace)
                VALUES                  (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE lastTime = ?,
                                        stackTrace = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->parameters['language']->languageID,
            $this->parameters['languageItem'],
            TIME_NOW,
            $stackTrace,

            TIME_NOW,
            $stackTrace,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        if (!ENABLE_DEVELOPER_TOOLS) {
            throw new IllegalLinkException();
        }

        parent::validateDelete();
    }

    /**
     * Validates the `clearLog` action.
     */
    public function validateClearLog()
    {
        if (!ENABLE_DEVELOPER_TOOLS) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
    }

    /**
     * Removes all entries from the missing language item log.
     */
    public function clearLog()
    {
        $sql = "DELETE FROM wcf1_devtools_missing_language_item";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
    }

    /**
     * Validates the `clearExistingLog` action.
     *
     * @since   5.4
     */
    public function validateClearExistingLog(): void
    {
        if (!ENABLE_DEVELOPER_TOOLS) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
    }

    /**
     * Removes the entries from the missing language item log for which a language item exists now.
     *
     * @since   5.4
     */
    public function clearExistingLog(): void
    {
        $sql = "DELETE      devtools_missing_language_item
                FROM        wcf1_devtools_missing_language_item devtools_missing_language_item
                INNER JOIN  wcf1_language_item language_item
                ON          language_item.languageItem = devtools_missing_language_item.languageItem
                        AND language_item.languageID = devtools_missing_language_item.languageID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
    }
}
