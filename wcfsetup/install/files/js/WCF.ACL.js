"use strict";

/**
 * Namespace for ACL
 */
WCF.ACL = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * ACL support for WCF
	 *
	 * @author        Alexander Ebert
	 * @copyright	2001-2020 WoltLab GmbH
	 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
	 * @deprecated    Deprecated since 6.1. Use `WoltLabSuite/Core/Ui/Acl/List` instead.
	 */
	WCF.ACL.List = Class.extend({
		/**
		 * name of the category the acl options belong to
		 * @var        string
		 */
		_categoryName: "",

		/**
		 * ACL container
		 * @var        jQuery
		 */
		_container: null,

		/**
		 * list of ACL container elements
		 * @var        object
		 */
		_containerElements: {},

		/**
		 * object id
		 * @var        integer
		 */
		_objectID: 0,

		/**
		 * object type id
		 * @var        integer
		 */
		_objectTypeID: null,

		/**
		 * list of available ACL options
		 * @var        object
		 */
		_options: {},

		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,

		/**
		 * user search handler
		 * @var        WCF.Search.User
		 */
		_search: null,

		/**
		 * list of ACL settings
		 * @var        object
		 */
		_values: {
			group: {},
			user: {},
		},

		/**
		 * Initializes the ACL configuration.
		 *
		 * @param        string                containerSelector
		 * @param        integer                objectTypeID
		 * @param        string                categoryName
		 * @param        integer                objectID
		 * @param        boolean                includeUserGroups
		 * @param        string|undefined       aclValuesFieldName
		 */
		init: function (
			containerSelector,
			objectTypeID,
			categoryName,
			objectID,
			includeUserGroups,
			initialPermissions,
			aclValuesFieldName,
		) {
			this._objectID = objectID || 0;
			this._objectTypeID = objectTypeID;
			this._categoryName = categoryName;
			if (includeUserGroups === undefined) {
				includeUserGroups = true;
			}
			this._values = {
				group: {},
				user: {},
			};
			this._aclValuesFieldName = aclValuesFieldName || "aclValues";

			this._proxy = new WCF.Action.Proxy({
				showLoadingOverlay: false,
				success: $.proxy(this._success, this),
			});

			// bind hidden container
			this._container = $(containerSelector).hide().addClass("aclContainer");

			// insert container elements
			var $elementContainer = this._container.children("dd");
			var $aclList = $('<ul class="aclList containerList" />').appendTo($elementContainer);
			var $searchInput = $(
				'<input type="text" class="long" placeholder="' +
					WCF.Language.get(
						"wcf.acl.search." + (!includeUserGroups ? "user." : "") + "description",
					) +
					'" />',
			).appendTo($elementContainer);
			var $permissionList = $('<ul class="aclPermissionList containerList" />')
				.hide()
				.appendTo($elementContainer);
			elData($permissionList[0], "grant", WCF.Language.get("wcf.acl.option.grant"));
			elData($permissionList[0], "deny", WCF.Language.get("wcf.acl.option.deny"));

			// set elements
			this._containerElements = {
				aclList: $aclList,
				permissionList: $permissionList,
				searchInput: $searchInput,
			};

			// prepare search input
			this._search = new WCF.Search.User(
				$searchInput,
				$.proxy(this.addObject, this),
				includeUserGroups,
			);

			// bind event listener for submit
			var $form = this._container.parents("form:eq(0)");
			$form.submit($.proxy(this.submit, this));

			// reset ACL on click
			var $resetButton = $form.find("input[type=reset]:eq(0)");
			if ($resetButton.length) {
				$resetButton.click($.proxy(this._reset, this));
			}

			if (initialPermissions) {
				this._success(initialPermissions);
			} else {
				this._loadACL();
			}
		},

		/**
		 * Restores the original ACL state.
		 */
		_reset: function () {
			// reset stored values
			this._values = {
				group: {},
				user: {},
			};

			// remove entries
			this._containerElements.aclList.empty();
			this._containerElements.searchInput.val("");

			// deselect all input elements
			this._containerElements.permissionList
				.hide()
				.find("input[type=checkbox]")
				.prop("checked", false);
		},

		/**
		 * Loads current ACL configuration.
		 */
		_loadACL: function () {
			this._proxy.setOption("data", {
				actionName: "loadAll",
				className: "wcf\\data\\acl\\option\\ACLOptionAction",
				parameters: {
					categoryName: this._categoryName,
					objectID: this._objectID,
					objectTypeID: this._objectTypeID,
				},
			});
			this._proxy.sendRequest();
		},

		/**
		 * Adds a new object to acl list.
		 *
		 * @param        object                data
		 */
		addObject: function (data) {
			var $listItem = this._createListItem(data.objectID, data.label, data.type);

			// toggle element
			this._savePermissions();
			this._containerElements.aclList.children("li").removeClass("active");
			$listItem.addClass("active");

			this._search.addExcludedSearchValue(data.label);

			// uncheck all option values
			this._containerElements.permissionList.find("input[type=checkbox]").prop("checked", false);

			// clear search input
			this._containerElements.searchInput.val("");

			// show permissions
			this._containerElements.permissionList.show();

			WCF.DOMNodeInsertedHandler.execute();
		},

		/**
		 * Creates a list item with the given data and returns it.
		 *
		 * @param        integer                objectID
		 * @param        string                label
		 * @param        string                type
		 * @return        jQuery
		 */
		_createListItem: function (objectID, label, type) {
			var $listItem = $(
				'<li><fa-icon size="16" name="' +
					(type === "group" ? "users" : "user") +
					'" solid></fa-icon> <span class="aclLabel">' +
					label +
					"</span></li>",
			).appendTo(this._containerElements.aclList);
			$listItem
				.data("objectID", objectID)
				.data("type", type)
				.data("label", label)
				.click($.proxy(this._click, this));
			$(
				'<button type="button" title="' +
					WCF.Language.get("wcf.global.button.delete") +
					'"><fa-icon size="16" name="xmark" solid></fa-icon></button>',
			)
				.click($.proxy(this._removeItem, this))
				.appendTo($listItem);

			return $listItem;
		},

		/**
		 * Removes an item from list.
		 *
		 * @param        object                event
		 */
		_removeItem: function (event) {
			this._savePermissions();

			var $listItem = $(event.currentTarget).parent();
			var $type = $listItem.data("type");
			var $objectID = $listItem.data("objectID");

			this._search.removeExcludedSearchValue($listItem.data("label"));
			$listItem.remove();

			// remove stored data
			if (this._values[$type][$objectID]) {
				delete this._values[$type][$objectID];
			}

			// try to select something else
			this._selectFirstEntry();
		},

		/**
		 * Selects the first available entry.
		 */
		_selectFirstEntry: function () {
			var $listItem = this._containerElements.aclList.children("li:eq(0)");
			if ($listItem.length) {
				this._select($listItem, false);
			} else {
				this._reset();
			}
		},

		/**
		 * Parses current ACL configuration.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			if (!$.getLength(data.returnValues.options)) {
				return;
			}

			// prepare options
			var $count = 0;
			var $structure = {};
			for (var $optionID in data.returnValues.options) {
				var $option = data.returnValues.options[$optionID];

				var $listItem = $("<li><span>" + $option.label + "</span></li>")
					.data("optionID", $optionID)
					.data("optionName", $option.optionName);
				var $grantPermission = $('<input type="checkbox" id="grant' + $optionID + '" />')
					.appendTo($listItem)
					.wrap(
						'<label for="grant' +
							$optionID +
							'" class="jsTooltip" title="' +
							WCF.Language.get("wcf.acl.option.grant") +
							'" />',
					);
				var $denyPermission = $('<input type="checkbox" id="deny' + $optionID + '" />')
					.appendTo($listItem)
					.wrap(
						'<label for="deny' +
							$optionID +
							'" class="jsTooltip" title="' +
							WCF.Language.get("wcf.acl.option.deny") +
							'" />',
					);

				$grantPermission
					.data("type", "grant")
					.data("optionID", $optionID)
					.change($.proxy(this._change, this));
				$denyPermission
					.data("type", "deny")
					.data("optionID", $optionID)
					.change($.proxy(this._change, this));

				if (!$structure[$option.categoryName]) {
					$structure[$option.categoryName] = [];
				}

				if ($option.categoryName === "") {
					$listItem.appendTo(this._containerElements.permissionList);
				} else {
					$structure[$option.categoryName].push($listItem);
				}

				$count++;
			}

			if ($.getLength($structure)) {
				for (var $categoryName in $structure) {
					var $listItems = $structure[$categoryName];

					if (data.returnValues.categories[$categoryName]) {
						$(
							'<li class="aclCategory">' +
								data.returnValues.categories[$categoryName] +
								"</li>",
						).appendTo(this._containerElements.permissionList);
					}

					for (var $i = 0, $length = $listItems.length; $i < $length; $i++) {
						$listItems[$i].appendTo(this._containerElements.permissionList);
					}
				}
			}

			// set data
			this._parseData(data, "group");
			this._parseData(data, "user");

			// show container
			this._container.show();

			// Because the container might have been hidden before, we must ensure that
			// form builder field dependencies are checked again to avoid having ACL
			// form fields not being shown in form builder forms.
			require(["WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager"], function (
				FormBuilderFieldDependencyManager,
			) {
				FormBuilderFieldDependencyManager.checkDependencies();
			});

			// pre-select an entry
			this._selectFirstEntry();
		},

		/**
		 * Parses user and group data.
		 *
		 * @param        object                data
		 * @param        string                type
		 */
		_parseData: function (data, type) {
			if (!$.getLength(data.returnValues[type].option)) {
				return;
			}

			// add list items
			for (var $typeID in data.returnValues[type].label) {
				this._createListItem($typeID, data.returnValues[type].label[$typeID], type);

				this._search.addExcludedSearchValue(data.returnValues[type].label[$typeID]);
			}

			// add options
			this._values[type] = data.returnValues[type].option;

			WCF.DOMNodeInsertedHandler.execute();
		},

		/**
		 * Prepares permission list for a specific object.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			var $listItem = $(event.currentTarget);
			if ($listItem.hasClass("active")) {
				return;
			}

			this._select($listItem, true);
		},

		/**
		 * Selects the given item and marks it as active.
		 *
		 * @param        jQuery                listItem
		 * @param        boolean                savePermissions
		 */
		_select: function (listItem, savePermissions) {
			// save previous permissions
			if (savePermissions) {
				this._savePermissions();
			}

			// switch active item
			this._containerElements.aclList.children("li").removeClass("active");
			listItem.addClass("active");

			// apply permissions for current item
			this._setupPermissions(listItem.data("type"), listItem.data("objectID"));
		},

		/**
		 * Toggles between deny and grant.
		 *
		 * @param        object                event
		 */
		_change: function (event) {
			var $checkbox = $(event.currentTarget);
			var $optionID = $checkbox.data("optionID");
			var $type = $checkbox.data("type");

			if ($checkbox.is(":checked")) {
				if ($type === "deny") {
					$("#grant" + $optionID).prop("checked", false);
				} else {
					$("#deny" + $optionID).prop("checked", false);
				}
			}
		},

		/**
		 * Setups permission input for given object.
		 *
		 * @param        string                type
		 * @param        integer                objectID
		 */
		_setupPermissions: function (type, objectID) {
			// reset all checkboxes to unchecked
			this._containerElements.permissionList.find("input[type='checkbox']").prop("checked", false);

			// use stored permissions if applicable
			if (this._values[type] && this._values[type][objectID]) {
				for (var $optionID in this._values[type][objectID]) {
					if (this._values[type][objectID][$optionID] == 1) {
						$("#grant" + $optionID)
							.prop("checked", true)
							.trigger("change");
					} else {
						$("#deny" + $optionID)
							.prop("checked", true)
							.trigger("change");
					}
				}
			}

			// show permissions
			this._containerElements.permissionList.show();
		},

		/**
		 * Saves currently set permissions.
		 */
		_savePermissions: function () {
			// get active object
			var $activeObject = this._containerElements.aclList.find("li.active");
			if (!$activeObject.length) {
				return;
			}

			var $objectID = $activeObject.data("objectID");
			var $type = $activeObject.data("type");

			// clear old values
			this._values[$type][$objectID] = {};
			this._containerElements.permissionList.find("input[type='checkbox']").each(
				function (index, checkbox) {
					var $checkbox = $(checkbox);
					var $optionValue = $checkbox.data("type") === "deny" ? 0 : 1;
					var $optionID = $checkbox.data("optionID");

					if ($checkbox.is(":checked")) {
						// store value
						this._values[$type][$objectID][$optionID] = $optionValue;

						// reset value afterwards
						$checkbox.prop("checked", false);
					} else if (
						this._values[$type] &&
						this._values[$type][$objectID] &&
						this._values[$type][$objectID][$optionID] &&
						this._values[$type][$objectID][$optionID] == $optionValue
					) {
						delete this._values[$type][$objectID][$optionID];
					}
				}.bind(this),
			);
		},

		/**
		 * Prepares ACL values on submit.
		 *
		 * @param        object                event
		 */
		submit: function (event) {
			this._savePermissions();

			this._save("group");
			this._save("user");
		},

		/**
		 * Inserts hidden form elements for each value.
		 *
		 * @param        string                $type
		 */
		_save: function ($type) {
			if ($.getLength(this._values[$type])) {
				var $form = this._container.parents("form:eq(0)");

				for (var $objectID in this._values[$type]) {
					var $object = this._values[$type][$objectID];

					for (var $optionID in $object) {
						$(
							'<input type="hidden" name="' +
								this._aclValuesFieldName +
								"[" +
								$type +
								"][" +
								$objectID +
								"][" +
								$optionID +
								']" value="' +
								$object[$optionID] +
								'" />',
						).appendTo($form);
					}
				}
			}
		},

		/**
		 * Returns the ACL data stored for this list.
		 *
		 * @return	object
		 * @since	5.2.3
		 */
		getData: function () {
			this._savePermissions();

			return this._values;
		},
	});
}
else {
	WCF.ACL.List = Class.extend({
		_categoryName: "",
		_container: {},
		_containerElements: {},
		_objectID: 0,
		_objectTypeID: {},
		_options: {},
		_proxy: {},
		_search: {},
		_values: {},
		init: function() {},
		_reset: function() {},
		_loadACL: function() {},
		addObject: function() {},
		_createListItem: function() {},
		_removeItem: function() {},
		_selectFirstEntry: function() {},
		_success: function() {},
		_parseData: function() {},
		_click: function() {},
		_select: function() {},
		_change: function() {},
		_setupPermissions: function() {},
		_savePermissions: function() {},
		submit: function() {},
		_save: function() {}
	});
}
