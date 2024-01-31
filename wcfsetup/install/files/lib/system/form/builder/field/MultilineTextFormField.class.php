<?php

namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for multiline text values.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class MultilineTextFormField extends TextFormField
{
    /**
     * number of rows of the textarea
     * @var int
     */
    protected $rows = 10;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_multilineTextFormField';

    /**
     * @inheritDoc
     * @since       5.4
     */
    protected function getValidAutoCompleteTokens(): array
    {
        return \array_merge(
            parent::getValidAutoCompleteTokens(),
            ['street-address']
        );
    }

    /**
     * Returns the number of rows of the textarea. If the number of rows has not been
     * explicitly set, `10` is returned.
     *
     * @return  int number of textarea rows
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Sets the number of rows of the textarea and returns this field.
     *
     * @param int $rows number of textarea rows
     * @return  static              this field
     *
     * @throws  \InvalidArgumentException   if given number of rows is invalid
     */
    public function rows($rows)
    {
        if ($rows <= 0) {
            throw new \InvalidArgumentException("Given number of rows is not positive for field '{$this->getId()}'.");
        }

        $this->rows = $rows;

        return $this;
    }

    /**
     * @inheritDoc
     * @since       5.4
     */
    protected static function getReservedFieldAttributes(): array
    {
        return \array_merge(
            parent::getReservedFieldAttributes(),
            [
                'rows',
            ]
        );
    }
}
