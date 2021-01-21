<?php

namespace wcf\system\devtools\pip;

use wcf\system\form\builder\IFormDocument;

/**
 * Default interface for package installation plugins that support adding and editing
 * entries via a graphical user interface in the developer tools.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Devtools\Pip
 * @since   5.2
 */
interface IGuiPackageInstallationPlugin extends IIdempotentPackageInstallationPlugin
{
    /**
     * Adds a new entry of this pip based on the data provided by the given
     * form.
     *
     * @param   IFormDocument       $form
     */
    public function addEntry(IFormDocument $form);

    /**
     * Deletes an existing pip entry and removes it from database.
     *
     * @param   string      $identifier     identifier of deleted entry
     * @param   bool        $addDeleteInstruction   if `true`, an explicit delete instruction is added
     *
     * @throws  \InvalidArgumentException   if no such entry exists or delete instruction should be added but is not supported
     */
    public function deleteEntry($identifier, $addDeleteInstruction);

    /**
     * Edits the entry of this pip with the given identifier based on the data
     * provided by the given form and returns the new identifier of the entry
     * (or the old identifier if it has not changed).
     *
     * @param   IFormDocument       $form
     * @param   string          $identifier
     * @return  string          new identifier
     */
    public function editEntry(IFormDocument $form, $identifier);

    /**
     * Returns additional template code for the form to add and edit entries.
     *
     * @return  string
     */
    public function getAdditionalTemplateCode();

    /**
     * Returns a list of all pip entries of this pip.
     *
     * @return  IDevtoolsPipEntryList
     */
    public function getEntryList();

    /**
     * Informs the pip of the identifier of the edited entry if the form to
     * edit that entry has been submitted.
     *
     * @param   string      $identifier
     *
     * @throws  \InvalidArgumentException   if no such entry exists
     */
    public function setEditedEntryIdentifier($identifier);

    /**
     * Adds the data of the pip entry with the given identifier into the
     * given form and returns `true`. If no entry with the given identifier
     * exists, `false` is returned.
     *
     * @param   string          $identifier
     * @param   IFormDocument       $document
     * @return  bool
     */
    public function setEntryData($identifier, IFormDocument $document);

    /**
     * Returns the list of available entry types. If only one entry type is
     * available, this method returns an empty array.
     *
     * For package installation plugins that support entries and categories
     * for these entries, `['entries', 'categories']` should be returned.
     *
     * @return  string[]
     */
    public function getEntryTypes();

    /**
     * Populates the given form to be used for adding and editing entries
     * managed by this PIP.
     *
     * @param   IFormDocument       $form
     */
    public function populateForm(IFormDocument $form);

    /**
     * Sets the type of the currently handled pip entries.
     *
     * @param   string      $entryType  currently handled pip entry type
     *
     * @throws  \InvalidArgumentException   if the given entry type is invalid (see `getEntryTypes()` method)
     */
    public function setEntryType($entryType);

    /**
     * Returns `true` if this package installation plugin supports delete
     * instructions.
     *
     * @return  bool
     */
    public function supportsDeleteInstruction();
}
