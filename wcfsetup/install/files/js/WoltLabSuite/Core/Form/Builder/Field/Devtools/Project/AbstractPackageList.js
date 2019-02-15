/**
 * Abstract implementation of the JavaScript component of a form field handling
 * a list of packages.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since	5.2
 */
define(['Dom/ChangeListener', 'Dom/Traverse', 'Dom/Util', 'EventKey', 'Language'], function(DomChangeListener, DomTraverse, DomUtil, EventKey, Language) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function AbstractPackageList(formFieldId, existingPackages) {
		this.init(formFieldId, existingPackages);
	};
	AbstractPackageList.prototype = {
		/**
		 * Initializes the package list handler.
		 * 
		 * @param	{string}	formFieldId		id of the associated form field
		 * @param	{object[]}	existingPackages	data of existing packages
		 */
		init: function(formFieldId, existingPackages) {
			this._formFieldId = formFieldId;
			
			this._packageList = elById(this._formFieldId + '_packageList');
			if (this._packageList === null) {
				throw new Error("Cannot find package list for packages field with id '" + this._formFieldId + "'.");
			}
			
			this._packageIdentifier = elById(this._formFieldId + '_packageIdentifier');
			if (this._packageIdentifier === null) {
				throw new Error("Cannot find package identifier form field for packages field with id '" + this._formFieldId + "'.");
			}
			this._packageIdentifier.addEventListener('keypress', this._keyPress.bind(this));
			
			this._addButton = elById(this._formFieldId + '_addButton');
			if (this._addButton === null) {
				throw new Error("Cannot find add button for packages field with id '" + this._formFieldId + "'.");
			}
			this._addButton.addEventListener('click', this._addPackage.bind(this));
			
			this._form = this._packageList.closest('form');
			if (this._form === null) {
				throw new Error("Cannot find form element for packages field with id '" + this._formFieldId + "'.");
			}
			this._form.addEventListener('submit', this._submit.bind(this));
			
			existingPackages.forEach(this._addPackageByData.bind(this));
		},
		
		/**
		 * Adds a package to the package list as a consequence of the given
		 * event. If the package data is invalid, an error message is shown
		 * and no package is added.
		 * 
		 * @param	{Event}		event	event that triggered trying to add the package
		 */
		_addPackage: function(event) {
			event.preventDefault();
			event.stopPropagation();
			
			// validate data
			if (!this._validateInput()) {
				return;
			}
			
			this._addPackageByData(this._getInputData());
			
			// empty fields
			this._emptyInput();
			
			this._packageIdentifier.focus();
		},
		
		/**
		 * Adds a package to the package list using the given package data.
		 * 
		 * @param	{object}	packageData
		 */
		_addPackageByData: function(packageData) {
			// add package to list
			var listItem = elCreate('li');
			this._populateListItem(listItem, packageData);
			
			// add delete button
			var deleteButton = elCreate('span');
			deleteButton.className = 'icon icon16 fa-times pointer jsTooltip';
			elAttr(deleteButton, 'title', Language.get('wcf.global.button.delete'));
			deleteButton.addEventListener('click', this._removePackage.bind(this));
			DomUtil.prepend(deleteButton, listItem);
			
			this._packageList.appendChild(listItem);
			
			DomChangeListener.trigger();
		},
		
		/**
		 * Creates the hidden fields when the form is submitted.
		 * 
		 * @param	{HTMLElement}	listElement	package list element from the package list
		 * @param	{int}		index		package index
		 */
		_createSubmitFields: function(listElement, index) {
			var packageIdentifier = elCreate('input');
			elAttr(packageIdentifier, 'type', 'hidden');
			elAttr(packageIdentifier, 'name', this._formFieldId + '[' + index + '][packageIdentifier]')
			packageIdentifier.value = elData(listElement, 'package-identifier');
			this._form.appendChild(packageIdentifier);
		},
		
		/**
		 * Empties the input fields.
		 */
		_emptyInput() {
			this._packageIdentifier.value = '';
		},
		
		/**
		 * Returns the error element for the given form field element.
		 * If `createIfNonExistent` is not given or `false`, `null` is returned
		 * if there is no error element, otherwise an empty error element
		 * is created and returned.
		 * 
		 * @param	{?boolean}	createIfNonExistent
		 * @return	{?HTMLElement}
		 */
		_getErrorElement: function(element, createIfNoNExistent) {
			var error = DomTraverse.nextByClass(element, 'innerError');
			
			if (error === null && createIfNoNExistent) {
				error = elCreate('small');
				error.className = 'innerError';
				
				DomUtil.insertAfter(error, element);
			}
			
			return error;
		},
		
		/**
		 * Returns the current data of the input fields to add a new package. 
		 * 
		 * @return	{object}
		 */
		_getInputData: function() {
			return {
				packageIdentifier: this._packageIdentifier.value
			};
		},
		
		/**
		 * Returns the error element for the package identifier form field.
		 * If `createIfNonExistent` is not given or `false`, `null` is returned
		 * if there is no error element, otherwise an empty error element
		 * is created and returned.
		 * 
		 * @param	{?boolean}	createIfNonExistent
		 * @return	{?HTMLElement}
		 */
		_getPackageIdentifierErrorElement: function(createIfNonExistent) {
			return this._getErrorElement(this._packageIdentifier, createIfNonExistent);
		},
		
		/**
		 * Adds a package to the package list after pressing ENTER in a
		 * text field.
		 * 
		 * @param	{Event}		event
		 */
		_keyPress: function(event) {
			if (EventKey.Enter(event)) {
				this._addPackage(event);
			}
		},
		
		/**
		 * Adds all necessary package-relavant data to the given list item.
		 * 
		 * @param	{HTMLElement}	listItem	package list element holding package data
		 * @param	{object}	packageData	package data
		 */
		_populateListItem(listItem, packageData) {
			elData(listItem, 'package-identifier', packageData.packageIdentifier);
		},
		
		/**
		 * Removes a package by clicking on its delete button.
		 * 
		 * @param	{Event}		event		delete button click event
		 */
		_removePackage: function(event) {
			elRemove(event.currentTarget.closest('li'));
			
			// remove field errors if the last package has been deleted
			if (
				!this._packageList.childElementCount &&
				this._packageList.nextElementSibling.tagName === 'SMALL' &&
				this._packageList.nextElementSibling.classList.contains('innerError')
			) {
				elRemove(this._packageList.nextElementSibling);
			}
		},
		
		/**
		 * Adds all necessary (hidden) form fields to the form when
		 * submitting the form.
		 */
		_submit: function() {
			DomTraverse.childrenByTag(this._packageList, 'LI').forEach(this._createSubmitFields.bind(this));
		},
		
		/**
		 * Returns `true` if the currently entered package data is valid.
		 * Otherwise `false` is returned and relevant error messages are
		 * shown.
		 * 
		 * @return	{boolean}
		 */
		_validateInput: function() {
			return this._validatePackageIdentifier();
		},
		
		/**
		 * Returns `true` if the currently entered package identifier is
		 * valid. Otherwise `false` is returned and an error message is
		 * shown.
		 * 
		 * @return	{boolean}
		 */
		_validatePackageIdentifier: function() {
			var packageIdentifier = this._packageIdentifier.value;
			
			if (packageIdentifier === '') {
				this._getPackageIdentifierErrorElement(true).textContent = Language.get('wcf.global.form.error.empty');
				
				return false;
			}
			
			if (packageIdentifier.length < 3) {
				this._getPackageIdentifierErrorElement(true).textContent = Language.get('wcf.acp.devtools.project.packageIdentifier.error.minimumLength');
				
				return false;
			}
			else if (packageIdentifier.length > 191) {
				this._getPackageIdentifierErrorElement(true).textContent = Language.get('wcf.acp.devtools.project.packageIdentifier.error.maximumLength');
				
				return false;
			}
			
			// see `wcf\data\package\Package::isValidPackageName()`
			if (!packageIdentifier.match(/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/)) {
				this._getPackageIdentifierErrorElement(true).textContent = Language.get('wcf.acp.devtools.project.packageIdentifier.error.format');
				
				return false;
			}
			
			// check if package has already been added
			var duplicate = false;
			DomTraverse.childrenByTag(this._packageList, 'LI').forEach(function(listItem, index) {
				if (elData(listItem, 'package-identifier') === packageIdentifier) {
					duplicate = true;
				}
			});
			
			if (duplicate) {
				this._getPackageIdentifierErrorElement(true).textContent = Language.get('wcf.acp.devtools.project.packageIdentifier.error.duplicate');
				
				return false;
			}
			
			// remove outdated errors
			var error = this._getPackageIdentifierErrorElement();
			if (error !== null) {
				elRemove(error);
			}
			
			return true;
		},
		
		/**
		 * Returns `true` if the given version is valid. Otherwise `false`
		 * is returned and an error message is shown.
		 * 
		 * @param	{string}	version			validated version
		 * @param	{function}	versionErrorGetter	returns the version error element
		 * @return	{boolean}
		 */
		_validateVersion: function(version, versionErrorGetter) {
			// see `wcf\data\package\Package::isValidVersion()`
			// the version is no a required attribute
			if (version !== '') {
				if (version.length > 255) {
					versionErrorGetter(true).textContent = Language.get('wcf.acp.devtools.project.packageVersion.error.maximumLength');
					
					return false;
				}
				
				// see `wcf\data\package\Package::isValidVersion()`
				if (!version.match(/^([0-9]+)\.([0-9]+)\.([0-9]+)(\ (a|alpha|b|beta|d|dev|rc|pl)\ ([0-9]+))?$/i)) {
					versionErrorGetter(true).textContent = Language.get('wcf.acp.devtools.project.packageVersion.error.format');
					
					return false;
				}
			}
			
			// remove outdated errors
			var error = versionErrorGetter();
			if (error !== null) {
				elRemove(error);
			}
			
			return true;
		}
	};
	
	return AbstractPackageList;
});
