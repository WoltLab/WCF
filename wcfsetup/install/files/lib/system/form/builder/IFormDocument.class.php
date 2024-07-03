<?php

namespace wcf\system\form\builder;

use wcf\data\IStorableObject;
use wcf\system\form\builder\button\IFormButton;
use wcf\system\form\builder\data\IFormDataHandler;
use wcf\system\form\builder\field\IFormField;

/**
 * Represents a "whole" form (document).
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IFormDocument extends IFormParentNode
{
    /**
     * represents the form mode for creating a new object
     * @var string
     */
    const FORM_MODE_CREATE = 'create';

    /**
     * represents the form mode for updating a new object
     * @var string
     */
    const FORM_MODE_UPDATE = 'update';

    /**
     * Sets the `action` property of the HTML `form` element and returns this document.
     *
     * @param string $action form action
     * @return  static              this document
     *
     * @throws  \InvalidArgumentException   if the given action is invalid
     */
    public function action($action);

    /**
     * Adds the given button to the `formSubmit` element at the end of the form and returns this
     * document.
     *
     * @param IFormButton $button added button
     * @return  static              this document
     */
    public function addButton(IFormButton $button);

    /**
     * Sets whether the default button is added to the form during in the `build()` method.
     *
     * @param bool $addDefaultButton
     * @return  static              this document
     * @throws  \BadMethodCallException     if the form has already been built
     */
    public function addDefaultButton($addDefaultButton = true);

    /**
     * Sets whether this form is requested via an AJAX request or processes data via an AJAX
     * request and returns his document.
     *
     * @param bool $ajax
     * @return  static      this document
     */
    public function ajax($ajax = true);

    /**
     * Is called once after all nodes have been added to this document.
     *
     * This method is intended to trigger `IFormNode::populate()` to allow nodes to
     * perform actions that require the whole document having finished constructing
     * itself and every parent-child relationship being established.
     *
     * @return  static              this document
     *
     * @throws  \BadMethodCallException     if this document has already been built
     */
    public function build();

    /**
     * Returns `true` if the form data has been read via `readData()` and `false` otherwise.
     *
     * @return  bool
     */
    public function didReadValues();

    /**
     * Sets the error message of this form using the given language item and returns this
     * document. If `null` is passed, the error message is unset.
     *
     * Unsetting the current error message causes `IFormDocument::getErrorMessage()` to
     * return the default error message.
     *
     * @param null|string $languageItem language item containing the error message or `null` to unset error message
     * @param array $variables additional variables used when resolving the language item
     * @return  static              this document
     *
     * @throws  \InvalidArgumentException   if the given form mode is invalid
     */
    public function errorMessage($languageItem = null, array $variables = []);

    /**
     * Sets the form mode (see `self::FORM_MODE_*` constants).
     *
     * @param string $formMode form mode
     * @return  static              this document
     *
     * @throws  \BadMethodCallException     if the form mode has already been set
     * @throws  \InvalidArgumentException   if the given form mode is invalid
     */
    public function formMode($formMode);

    /**
     * Returns the `action` property of the HTML `form` element.
     *
     * @return  string              form action
     *
     * @throws  \BadMethodCallException     if no action has been set and `isAjax()` is `false`
     */
    public function getAction();

    /**
     * Returns the button with the given id.
     *
     * @param string $buttonId id of requested button
     * @return  IFormButton
     *
     * @throws  \InvalidArgumentException   if no such button exists
     */
    public function getButton($buttonId);

    /**
     * Returns the buttons registered for this form document.
     *
     * @return  IFormButton[]
     */
    public function getButtons();

    /**
     * Returns the array passed as the `$parameters` argument of the constructor
     * of a database object action.
     *
     * @return  array       data passed to database object action
     *
     * @throws  \BadMethodCallException     if the method is called before `readValues()` is called
     */
    public function getData();

    /**
     * Returns the data handler for this document that is used to process the
     * field data into a parameters array for the constructor of a database
     * object action.
     *
     * Note: The data handler comes with `DefaultFormFieldDataProcessor` as its
     * initial data processor.
     *
     * @return  IFormDataHandler    form data handler
     */
    public function getDataHandler();

    /**
     * Returns the encoding type of this form. If the form contains any
     * `IFileFormField`, `multipart/form-data` is returned, otherwise `null`
     * is returned.
     *
     * @return  null|string     form encoding type
     */
    public function getEnctype();

    /**
     * Returns the error message for the whole form.
     *
     * By default, `wcf.global.form.error` in the active user's language is returned.
     * This method always returns the error message! To check, if the error message should
     * be displayed, use `IParentFormNode::hasValidationErrors()` and
     * `IFormDocument::showsErrorMessage()`.
     *
     * @return  string
     */
    public function getErrorMessage();

    /**
     * Returns the form mode (see `self::FORM_MODE_*` constants).
     *
     * The form mode can help validators to determine whether a new object
     * is added or an existing object is edited. If no form mode has been
     * explicitly set, `self::FORM_MODE_CREATE` is set and returned.
     *
     * @return  string      form mode
     */
    public function getFormMode();

    /**
     * Returns the `method` property of the HTML `form` element. If no method
     * has been set, `post` is returned.
     *
     * @return  string      form method
     */
    public function getMethod();

    /**
     * Returns the global form prefix that is prepended to form elements' names and ids to
     * avoid conflicts with other forms. If no prefix has been set, an empty string is returned.
     *
     * Note: If a prefix `foo` has been set, this method returns `foo_`.
     *
     * @return  string      global form element prefix
     */
    public function getPrefix();

    /**
     * Returns the request data of the form's fields.
     *
     * If no request data is set, `$_POST` will be set as the request data.
     *
     * @param null|string $index array index of the returned data
     * @return  array|mixed         request data of the form's fields or specific index data if index is given
     *
     * @throws  \InvalidArgumentException   if invalid index is given
     */
    public function getRequestData($index = null);

    /**
     * Returns the success message for the whole form.
     *
     * By default, `wcf.global.form.add` or `wcf.global.form.edit` in the active user's language
     * is returned depending on the current form mode.
     *
     * @return  string
     */
    public function getSuccessMessage();

    /**
     * Returns `true` if a button with the given id exists and `false` otherwise.
     *
     * @param string $buttonId id of checked button
     * @return  bool
     */
    public function hasButton($buttonId);

    /**
     * Returns `true` if the default button is added to the form during in the `build()` method
     * and `false` otherwise.
     *
     * By default, the default button is added.
     * Each implementing class can define itself what it considers its default button.
     *
     * @return  bool
     */
    public function hasDefaultButton();

    /**
     * Returns `true` if there is any request data or, if a parameter is given, if
     * there is request data with a specific index.
     *
     * If no request data is set, `$_POST` will be set as the request data.
     *
     * @param null|string $index array index of the returned data
     * @return  bool
     */
    public function hasRequestData($index = null);

    /**
     * Returns `true` if this form is requested via an AJAX request or processes data via an
     * AJAX request and `false` otherwise.
     *
     * By default, this method returns `false`.
     *
     * @return  bool
     */
    public function isAjax();

    /**
     * Returns `true` if the form document is in invalid due to external factors and is `false`
     * otherwise.
     *
     * By default, the form document is not invalid.
     *
     * @return  bool
     */
    public function isInvalid();

    /**
     * Sets if the form document is in invalid due to external factors.
     *
     * @param bool $invalid
     * @return  static              this document
     */
    public function invalid($invalid = true);

    /**
     * Returns `true` if the information about required fields has to be shown below the form.
     *
     * @return  bool
     * @since   5.3
     */
    public function needsRequiredFieldsInfo();

    /**
     * Sets the updated object (and loads the field values from the given object) and returns
     * this document.
     *
     * Per default, for each field, `IFormField::updatedObject()` is called.
     * This method automatically sets the form mode to `self::FORM_MODE_UPDATE`.
     *
     * @param IStorableObject $object updated object
     * @param bool $loadValues indicates if the object's values are loaded
     * @return  static                  this document
     */
    public function updatedObject(IStorableObject $object, $loadValues = true);

    /**
     * Sets whether required fields are marked in the output and returns this document.
     *
     * @since       5.4
     * @return      static      this document
     */
    public function markRequiredFields(bool $markRequiredFields = true);

    /**
     * Returns `true` if required fields are marked in the output and `false` otherwise.
     *
     * By default, required fields are marked in the output.
     *
     * @since       5.4
     */
    public function marksRequiredFields(): bool;

    /**
     * Sets the `method` property of the HTML `form` element and returns this document.
     *
     * @param string $method form method
     * @return  static              this document
     *
     * @throws  \InvalidArgumentException   if the given method is invalid
     */
    public function method($method);

    /**
     * Sets the global form prefix that is prepended to form elements' names and ids to
     * avoid conflicts with other forms and returns this document.
     *
     * Note: The prefix is not relevant when using the `IFormParentNode::getNodeById()`.
     * It is only relevant when printing the form and reading the form values.
     *
     * @param string $prefix global form prefix
     * @return  static              this document
     *
     * @throws  \InvalidArgumentException   if the given prefix is invalid
     */
    public function prefix($prefix);

    /**
     * Sets the request data of the form's fields.
     *
     * @param array $requestData request data of the form's fields
     * @return  static              this document
     *
     * @throws  \BadMethodCallException     if request data has already been set
     */
    public function requestData(array $requestData);

    /**
     * Sets if the global form error message should be shown if the form has validation errors.
     *
     * @param bool $showErrorMessage
     * @return  static                  this document
     */
    public function showErrorMessage($showErrorMessage = true);

    /**
     * Sets if the global form success message should be shown.
     *
     * @param bool $showSuccessMessage
     * @return  static                  this document
     */
    public function showSuccessMessage($showSuccessMessage = true);

    /**
     * Returns `true` if the global form error message should be shown if the form has validation
     * errors.
     *
     * By default, the global form error message is shown.
     *
     * @return  bool
     */
    public function showsErrorMessage();

    /**
     * Returns `true` if the global form success message should be shown.
     *
     * By default, the global form error message is not shown.
     *
     * @return  bool
     */
    public function showsSuccessMessage();

    /**
     * Sets the success message of this form using the given language item and returns this
     * document. If `null` is passed, the success message is unset.
     *
     * Unsetting the current success message causes `IFormDocument::getSuccessMessage()()` to
     * return the default success message.
     *
     * @param null|string $languageItem language item containing the success message or `null` to unset error message
     * @param array $variables additional variables used when resolving the language item
     * @return  static              this document
     *
     * @throws  \InvalidArgumentException   if the given form mode is invalid
     */
    public function successMessage($languageItem = null, array $variables = []);

    /**
     * Returns the form field with the given id or `null` if no such field exists.
     *
     * @since 6.1
     */
    public function getFormField(string $nodeId): ?IFormField;
}
