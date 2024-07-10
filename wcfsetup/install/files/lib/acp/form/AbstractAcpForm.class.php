<?php

namespace wcf\acp\form;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\language\I18nValue;
use wcf\system\WCF;

/**
 * Default implementation for ACP forms with i18n support.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.1
 */
abstract class AbstractAcpForm extends AbstractForm
{
    /**
     * action type
     * @var string
     */
    public $action = 'add';

    /**
     * @var I18nValue[]
     */
    public $i18nValues = [];

    /**
     * Registers a new i18n value.
     *
     * @param I18nValue $value
     */
    public function registerI18nValue(I18nValue $value)
    {
        $fieldName = $value->getFieldName();

        if (isset($this->i18nValues[$fieldName])) {
            throw new \InvalidArgumentException("Duplicate value definition for '{$fieldName}'.");
        } elseif (!\property_exists($this, $fieldName)) {
            throw new \UnexpectedValueException("Implementing class does not expose the property '{$fieldName}'.");
        }

        $this->i18nValues[$fieldName] = $value;

        I18nHandler::getInstance()->register($fieldName);
    }

    /**
     * Retrieves an i18n value object.
     *
     * @param string $fieldName
     * @return      I18nValue|null
     */
    public function getI18nValue($fieldName)
    {
        return $this->i18nValues[$fieldName] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (!empty($this->i18nValues)) {
            I18nHandler::getInstance()->readValues();

            foreach ($this->i18nValues as $fieldName => $value) {
                if (I18nHandler::getInstance()->isPlainValue($fieldName)) {
                    $this->{$fieldName} = I18nHandler::getInstance()->getValue($fieldName);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        foreach ($this->i18nValues as $fieldName => $value) {
            if (
                !I18nHandler::getInstance()->validateValue(
                    $fieldName,
                    $value->getFlag(I18nValue::REQUIRE_I18N),
                    $value->getFlag(I18nValue::ALLOW_EMPTY)
                )
            ) {
                throw new UserInputException(
                    $fieldName,
                    (I18nHandler::getInstance()->isPlainValue($fieldName)) ? 'empty' : 'multilingual'
                );
            }
        }
    }

    /**
     * Reads the i18n data for the given object.
     *
     * @param DatabaseObject $databaseObject
     */
    public function readDataI18n(DatabaseObject $databaseObject)
    {
        if (empty($_POST) && !empty($this->i18nValues)) {
            foreach ($this->i18nValues as $fieldName => $value) {
                I18nHandler::getInstance()->setOptions(
                    $fieldName,
                    $value->getPackageID(),
                    $databaseObject->{$fieldName} ?? '',
                    "{$value->getLanguageItem()}\\d+"
                );
            }
        }
    }

    /**
     * Saves the i18n data for the given database object befor the changes of
     * the given database object are saved.
     *
     * @param DatabaseObject $databaseObject
     * @return  string[]
     */
    public function beforeSaveI18n(DatabaseObject $databaseObject)
    {
        $values = [];

        foreach ($this->i18nValues as $fieldName => $value) {
            $this->{$fieldName} = $value->getLanguageItem() . $databaseObject->getObjectID();
            if (I18nHandler::getInstance()->isPlainValue($fieldName)) {
                I18nHandler::getInstance()->remove($fieldName);

                $values[$fieldName] = I18nHandler::getInstance()->getValue($fieldName);
                $this->{$fieldName} = $values[$fieldName];
            } else {
                I18nHandler::getInstance()->save(
                    $fieldName,
                    $this->{$fieldName},
                    $value->getLanguageCategory(),
                    $value->getPackageID()
                );

                $values[$fieldName] = I18nHandler::getInstance()->getValues($fieldName)[WCF::getLanguage()->languageID];
            }
        }

        return $values;
    }

    /**
     * Saves the i18n data for the given database object after the given database
     * object has been created.
     *
     * @param DatabaseObject $databaseObject
     * @param string $editorClass
     */
    public function saveI18n(DatabaseObject $databaseObject, $editorClass)
    {
        $data = [];

        $objectID = $databaseObject->getObjectID();
        foreach ($this->i18nValues as $fieldName => $value) {
            if (!I18nHandler::getInstance()->isPlainValue($fieldName)) {
                $languageItem = $value->getLanguageItem() . $objectID;
                I18nHandler::getInstance()->save(
                    $fieldName,
                    $languageItem,
                    $value->getLanguageCategory(),
                    $value->getPackageID()
                );

                $data[$fieldName] = $languageItem;
            }
        }

        if (!empty($data)) {
            /** @var DatabaseObjectEditor $editor */
            $editor = new $editorClass($databaseObject);
            $editor->update($data);
        }
    }

    /**
     * Resets the form values and calls the saved event.
     */
    public function reset()
    {
        $this->saved();

        if (!empty($this->i18nValues)) {
            foreach ($this->i18nValues as $fieldName => $value) {
                $this->{$fieldName} = '';
            }

            I18nHandler::getInstance()->reset();
        }

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        if (!empty($this->i18nValues)) {
            $useRequestData = ($this->action === 'add') ? true : !empty($_POST);

            I18nHandler::getInstance()->assignVariables($useRequestData);
        }

        WCF::getTPL()->assign([
            'action' => $this->action,
        ]);
    }
}
