/**
 * Manages form field dependencies.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager
 * @since	3.2
 */
define(['Dictionary', 'Dom/ChangeListener', 'EventHandler', 'List', 'Dom/Util'], function(Dictionary, DomChangeListener, EventHandler, List, DomUtil) {
	"use strict";
	
	/**
	 * is `true` if containters are currently checked for their availablility, otherwise `false`
	 * @type	{boolean}
	 * @private
	 */
	var _checkingContainers = false;
	
	/**
	 * is `true` if containter will be checked again after the current check for their availablility
	 * has finished, otherwise `false`
	 * @type	{boolean}
	 * @private
	 */
	var _checkContainersAgain = true;
	
	/**
	 * list of containers hidden due to their own dependencies
	 * @type	{List}
	 * @private
	 */
	var _dependencyHiddenNodes = new List();
	
	/**
	 * list of fields for which event listeners have been registered
	 * @type	{Dictionary}
	 * @private
	 */
	var _fields = new Dictionary();
	
	/**
	 * list of registered forms
	 * @type	{List}
	 * @private
	 */
	var _forms = new List();
	
	/**
	 * list of dependencies grouped by the dependent node they belong to
	 * @type	{Dictionary}
	 * @private
	 */
	var _nodeDependencies = new Dictionary();
	
	/**
	 * list of required fields
	 * @type	{List}
	 * @private
	 */
	var _validatedFields = new List();
	
	return {
		/**
		 * Check if for an invalid form field if it has been hidden due to dependencies
		 * and discards any validation error message if that is the case.
		 * 
		 * @param	{Event}		event	`invalid` form field event 
		 * @protected
		 */
		_checkRequiredField: function(event) {
			_dependencyHiddenNodes.forEach(function(hiddenNode) {
				if (DomUtil.contains(hiddenNode, event.currentTarget)) {
					event.preventDefault();
					event.stopPropagation();
				};
			});
		},
		
		/**
		 * Registers the (new) required fields of all registered forms.
		 * 
		 * @protected
		 */
		_registerValidatedFields: function() {
			_forms.forEach(function(form) {
				// `minlength` does not trigger `invalid` events
				elBySelAll('[max], [maxlength], [min], [required]', form, function(validatedField) {
					if (!_validatedFields.has(validatedField)) {
						_validatedFields.add(validatedField);
						
						validatedField.addEventListener('invalid', this._checkRequiredField.bind(this));
					}
				}.bind(this))
			}.bind(this));
		},
		
		/**
		 * Hides the given node because of its own dependencies.
		 * 
		 * @param	{HTMLElement}	node	hidden node
		 * @protected
		 */
		_hide: function(node) {
			elHide(node);
			_dependencyHiddenNodes.add(node);
		},
		
		/**
		 * Shows the given node because of its own dependencies.
		 * 
		 * @param	{HTMLElement}	node	shown node
		 * @protected
		 */
		_show: function(node) {
			elShow(node);
			_dependencyHiddenNodes.delete(node);
		},
		
		/**
		 * Registers a new form field dependency.
		 * 
		 * @param	{WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract}	dependency	new dependency
		 */
		addDependency: function(dependency) {
			var dependentNode = dependency.getDependentNode();
			if (!_nodeDependencies.has(dependentNode.id)) {
				_nodeDependencies.set(dependentNode.id, [dependency]);
			}
			else {
				_nodeDependencies.get(dependentNode.id).push(dependency);
			}
			
			var fields = dependency.getFields();
			for (var i = 0, length = fields.length; i < length; i++) {
				var field = fields[i];
				if (!_fields.has(field.id)) {
					_fields.set(field.id, field);
					
					if (field.tagName === 'INPUT' && (field.type === 'checkbox' || field.type === 'radio')) {
						field.addEventListener('change', this.checkDependencies.bind(this));
					}
					else {
						field.addEventListener('input', this.checkDependencies.bind(this));
					}
				}
			}
		},
		
		/**
		 * Check all dependencies if they are met.
		 */
		checkDependencies: function() {
			var obsoleteNodes = [];
			
			_nodeDependencies.forEach(function(nodeDependencies, nodeId) {
				var dependentNode = elById(nodeId);
				
				// check if dependent node still exists
				if (dependentNode === null) {
					obsoleteNodes.push(dependentNode);
					return;
				}
				
				for (var i = 0, length = nodeDependencies.length; i < length; i++) {
					// if any dependency is met, the element is visible
					if (nodeDependencies[i].checkDependency()) {
						this._show(dependentNode);
						return;
					}
				}
				
				// no node dependencies is met
				this._hide(dependentNode);
			}.bind(this));
			
			// delete dependencies for removed elements
			for (var i = 0, length = obsoleteNodes.length; i < length; i++) {
				_nodeDependencies.delete(obsoleteNodes.id);
			}
			
			this.checkContainers();
		},
		
		/**
		 * Adds the given callback to the list of callbacks called when checking containers.
		 * 
		 * @param	{function}	callback
		 */
		addContainerCheckCallback: function(callback) {
			if (typeof callback !== 'function') {
				throw new TypeError("Expected a valid callback for parameter 'callback'.");
			}
			
			EventHandler.add('com.woltlab.wcf.form.builder.dependency', 'checkContainers', callback);
		},
		
		/**
		 * Checks the containers for their availablility.
		 * 
		 * If this function is called while containers are currently checked, the containers
		 * will be checked after the current check has been finished completely.
		 */
		checkContainers: function() {
			// check if containers are currently being checked
			if (_checkingContainers === true) {
				// and if that is the case, calling this method indicates, that after the current round,
				// containters should be checked to properly propagate changes in children to their parents
				_checkContainersAgain = true;
				
				return;
			}
			
			// starting to check containers also resets the flag to check containers again after the current check 
			_checkingContainers = true;
			_checkContainersAgain = false;
			
			EventHandler.fire('com.woltlab.wcf.form.builder.dependency', 'checkContainers');
			
			// finish checking containers and check if containters should be checked again
			_checkingContainers = false;
			if (_checkContainersAgain) {
				this.checkContainers();
			}
		},
		
		/**
		 * Returns `true` if the given node has been hidden because of its own dependencies.
		 * 
		 * @param	{HTMLElement}	node	checked node
		 * @return	{boolean}
		 */
		isHiddenByDependencies: function(node) {
			return _dependencyHiddenNodes.has(node);
		},
		
		/**
		 * Registers the form with the given id with the dependency manager.
		 * 
		 * @param	{string}	formId		id of register form
		 * @throws	{Error}				if given form id is invalid or has already been registered
		 */
		register: function(formId) {
			var form = elById(formId);
			
			if (form === null) {
				throw new Error("Unknown element with id '" + formId + "'");
			}
			if (form.tagName !== 'FORM') {
				throw new Error("Element with id '" + formId + "' is no form.");
			}
			
			if (_forms.has(form)) {
				throw new Error("Form with id '" + formId + "' has already been registered.");
			}
			
			_forms.add(form);
			
			this._registerValidatedFields();
			
			DomChangeListener.add('WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager', this._registerValidatedFields.bind(this));
		}
	};
});
