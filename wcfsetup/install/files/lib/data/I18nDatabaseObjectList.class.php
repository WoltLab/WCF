<?php

namespace wcf\data;

use wcf\system\WCF;

/**
 * Abstract class for a list of database objects with better sorting of i18n-based columns.
 *
 * @author  Florian Gail
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
abstract class I18nDatabaseObjectList extends DatabaseObjectList
{
    /**
     * Array of column names, that are eventually filled with language items.
     * Those additional joins are going to slow down the system; you should use this
     * class only when it's really needed.
     * The key represents the original field name.
     * The value represents the new field name containing the localized field-value.
     *
     * @example   [ 'title' => 'titleSortField' ]
     *
     * @var string[]
     */
    public $i18nFields = [];

    /**
     * @inheritDoc
     * @param int $languageID id of the language that should be used
     * @throws \DomainException
     */
    public function __construct($languageID = null)
    {
        parent::__construct();

        if ($languageID === null) {
            $languageID = WCF::getLanguage()->languageID;
        }

        if (!empty($this->i18nFields)) {
            if (\count($this->i18nFields) !== \count(\array_flip($this->i18nFields))) {
                throw new \DomainException("Array values of '" . $this->className . "::\$i18nFields' must be unique.");
            }

            foreach ($this->i18nFields as $key => $value) {
                if (!\preg_match('/^[a-z][a-zA-Z0-9]*$/', $key) || !\preg_match('/^[a-z][a-zA-Z0-9]*$/', $value)) {
                    throw new \DomainException("Array keys and values of '" . $this->className . "::\$i18nFields' must start with a small letter and consist of letters and number only.");
                }

                $matchTable = 'i18n_' . \sha1($key);

                $this->sqlSelects .= (!empty($this->sqlSelects) ? ', ' : '') . "COALESCE(" . $matchTable . ".languageItemValue, " . $this->getDatabaseTableAlias() . "." . $key . ") AS " . $value;
                $this->sqlJoins .= "
                    LEFT JOIN   wcf1_language_item " . $matchTable . "
                    ON          " . $matchTable . ".languageItem = " . $this->getDatabaseTableAlias() . "." . $key . "
                            AND " . $matchTable . ".languageID = " . $languageID;
            }
        }
    }
}
