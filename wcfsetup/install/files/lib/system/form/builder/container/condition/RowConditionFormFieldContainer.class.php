<?php

namespace wcf\system\form\builder\container\condition;

use wcf\data\IStorableObject;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\container\RowFormFieldContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormDocument;

/**
 * Represents a form container that contains form fields which are displayed in rows.
 * This container is specifically designed for conditions and processes the data
 * accordingly, allowing for the collection of data from its child form fields.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class RowConditionFormFieldContainer extends RowFormFieldContainer
{
    #[\Override]
    public function populate()
    {
        $this->getDocument()->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    $this->getId() . "DataProcessor",
                    function (IFormDocument $document, array $parameters) {
                        $data = [];

                        foreach ($this->children() as $child) {
                            if (!($child instanceof IFormField)) {
                                continue;
                            }

                            $id = $child->getId();
                            $name = $this->getName($child);

                            $data[$name] = $parameters['data'][$id];
                            unset($parameters['data'][$id]);
                        }

                        if ($data !== []) {
                            $parameters['data'][$this->getId()] = $data;
                        }

                        return $parameters;
                    },
                )
            );

        return parent::populate();
    }

    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        if ($loadValues && isset($data[$this->getId()])) {
            $values = [];
            foreach ($data[$this->getId()] as $name => $value) {
                $values[$this->getId() . $name] = $value;
            }

            foreach ($this->children() as $child) {
                if ($child instanceof IFormField || $child instanceof IFormContainer) {
                    $child->updatedObject($values, $object, $loadValues);
                }
            }
        }

        return $this;
    }

    private function getName(IFormField|IFormContainer $child): string
    {
        $id = $child->getId();

        return \mb_substr($id, \mb_strlen($this->getId()));
    }
}
