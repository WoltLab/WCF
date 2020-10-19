/**
 * Data handler for a acl form builder field in an Ajax form.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Acl
 * @since	5.2.3
 */
define(['Core', './Field'], function(Core, FormBuilderField) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function FormBuilderFieldAcl(fieldId) {
		this.init(fieldId);
		
		this._aclList = null;
	};
	Core.inherit(FormBuilderFieldAcl, FormBuilderField, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
		 */
		_getData: function() {
			var data = {};
			
			data[this._fieldId] = this._aclList.getData();
			
			return data;
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_readField
		 */
		_readField: function() {
			// does nothing
		},
		
		/**
		 * Sets the ACL list object used to extract the ACL values.
		 * 
		 * @param	{WCF.ACL.List}		aclList
		 */
		setAclList: function(aclList) {
			this._aclList = aclList;
		}
	});
	
	return FormBuilderFieldAcl;
});
