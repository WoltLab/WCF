/**
 * Manages form field dependencies.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager
 * @since	5.2
 */
define(['Dictionary', 'Dom/ChangeListener', 'EventHandler', 'List', 'Dom/Traverse', 'Dom/Util', 'ObjectMap'], function(Dictionary, DomChangeListener, EventHandler, List, DomTraverse, DomUtil, ObjectMap) {
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
	 * cache of validation-related properties of hidden form fields
	 * @type	{ObjectMap}
	 * @private
	 */
	var _validatedFieldProperties = new ObjectMap();
	
	return {
		/**
		 * Hides the given node because of its own dependencies.
		 * 
		 * @param	{HTMLElement}	node	hidden node
		 * @protected
		 */
		_hide: function(node) {
			elHide(node);
			_dependencyHiddenNodes.add(node);
			
			// also hide tab menu entry
			if (node.classList.contains('tabMenuContent')) {
				elBySelAll('li', DomTraverse.prevByClass(node, 'tabMenu'), function(tabLink) {
					if (elData(tabLink, 'name') === elData(node, 'name')) {
						elHide(tabLink);
					}
				});
			}
			
			elBySelAll('[max], [maxlength], [min], [required]', node, function(validatedField) {
				var properties = new Dictionary();
				
				var max = elAttr(validatedField, 'max');
				if (max) {
					properties.set('max', max);
					validatedField.removeAttribute('max');
				}
				
				var maxlength = elAttr(validatedField, 'maxlength');
				if (maxlength) {
					properties.set('maxlength', maxlength);
					validatedField.removeAttribute('maxlength');
				}
				
				var min = elAttr(validatedField, 'min');
				if (min) {
					properties.set('min', min);
					validatedField.removeAttribute('min');
				}
				
				if (validatedField.required) {
					properties.set('required', true);
					validatedField.removeAttribute('required');
				}
				
				_validatedFieldProperties.set(validatedField, properties);
			});
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
			
			// also show tab menu entry
			if (node.classList.contains('tabMenuContent')) {
				elBySelAll('li', DomTraverse.prevByClass(node, 'tabMenu'), function(tabLink) {
					if (elData(tabLink, 'name') === elData(node, 'name')) {
						elShow(tabLink);
					}
				});
			}
			
			elBySelAll('input, select', node, function(validatedField) {
				// if a container is shown, ignore all fields that
				// have a hidden parent element within the container
				var parentNode = validatedField.parentNode;
				while (parentNode !== node && parentNode.style.getPropertyValue('display') !== 'none') {
					parentNode = parentNode.parentNode;
				}
				
				if (parentNode === node && _validatedFieldProperties.has(validatedField)) {
					var properties = _validatedFieldProperties.get(validatedField);
					
					if (properties.has('max')) {
						elAttr(validatedField, 'max', properties.get('max'));
					}
					if (properties.has('maxlength')) {
						elAttr(validatedField, 'maxlength', properties.get('maxlength'));
					}
					if (properties.has('min')) {
						elAttr(validatedField, 'min', properties.get('min'));
					}
					if (properties.has('required')) {
						elAttr(validatedField, 'required', '');
					}
					
					_validatedFieldProperties.delete(validatedField);
				}
			});
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
				var id = DomUtil.identify(field);
				
				if (!_fields.has(id)) {
					_fields.set(id, field);
					
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
		 * Checks if all dependencies are met.
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
					// if any dependency is not met, hide the element
					if (!nodeDependencies[i].checkDependency()) {
						this._hide(dependentNode);
						return;
					}
				}
				
				// all node dependency is met
				this._show(dependentNode);
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
		 * Checks the containers for their availability.
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
			if (_dependencyHiddenNodes.has(node)) {
				return true;
			}
			
			var returnValue = false;
			_dependencyHiddenNodes.forEach(function(hiddenNode) {
				if (DomUtil.contains(hiddenNode, node)) {
					returnValue = true;
				}
			});
			
			return returnValue;
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
				var dialogContent = DomTraverse.parentByClass(form, 'dialogContent');
				
				if (dialogContent === null) {
					throw new Error("Element with id '" + formId + "' is no form.");
				}
			}
			
			if (_forms.has(form)) {
				throw new Error("Form with id '" + formId + "' has already been registered.");
			}
			
			_forms.add(form);
		}
	};
});
