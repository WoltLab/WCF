/**
 * Manager for registered Ajax forms and its fields that can be used to retrieve the current data
 * of the registered forms.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Manager
 * @since 5.2
 */
define(["require", "exports", "tslib", "../../Core", "../../Event/Handler", "./Field/Field", "./Field/Dependency/Manager"], function (require, exports, tslib_1, Core, EventHandler, Field_1, Manager_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    EventHandler = tslib_1.__importStar(EventHandler);
    Field_1 = tslib_1.__importDefault(Field_1);
    Manager_1 = tslib_1.__importDefault(Manager_1);
    const _fields = new Map();
    const _forms = new Map();
    const FormBuilderManager = {
        /**
         * Returns a promise returning the data of the form with the given id.
         */
        getData(formId) {
            if (!this.hasForm(formId)) {
                throw new Error("Unknown form with id '" + formId + "'.");
            }
            const promises = [];
            _fields.get(formId).forEach(function (field) {
                const fieldData = field.getData();
                if (!(fieldData instanceof Promise)) {
                    throw new TypeError("Data for field with id '" + field.getId() + "' is no promise.");
                }
                promises.push(fieldData);
            });
            return Promise.all(promises).then(function (promiseData) {
                let data = {};
                promiseData.forEach((_data) => {
                    data = Core.extend(data, _data);
                });
                return data;
            });
        },
        /**
         * Returns the registered form field with given.
         *
         * @since 5.2.3
         */
        getField(formId, fieldId) {
            if (!this.hasField(formId, fieldId)) {
                throw new Error("Unknown field with id '" + formId + "' for form with id '" + fieldId + "'.");
            }
            return _fields.get(formId).get(fieldId);
        },
        /**
         * Returns the registered form with given id.
         */
        getForm(formId) {
            if (!this.hasForm(formId)) {
                throw new Error("Unknown form with id '" + formId + "'.");
            }
            return _forms.get(formId);
        },
        /**
         * Returns `true` if a field with the given id has been registered for the form with the given id
         * and `false` otherwise.
         */
        hasField(formId, fieldId) {
            if (!this.hasForm(formId)) {
                throw new Error("Unknown form with id '" + formId + "'.");
            }
            return _fields.get(formId).has(fieldId);
        },
        /**
         * Returns `true` if a form with the given id has been registered and `false` otherwise.
         */
        hasForm(formId) {
            return _forms.has(formId);
        },
        /**
         * Registers the given field for the form with the given id.
         */
        registerField(formId, field) {
            if (!this.hasForm(formId)) {
                throw new Error("Unknown form with id '" + formId + "'.");
            }
            if (!(field instanceof Field_1.default)) {
                throw new Error("Add field is no instance of 'WoltLabSuite/Core/Form/Builder/Field/Field'.");
            }
            const fieldId = field.getId();
            if (this.hasField(formId, fieldId)) {
                throw new Error("Form field with id '" + fieldId + "' has already been registered for form with id '" + formId + "'.");
            }
            _fields.get(formId).set(fieldId, field);
            EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "registerField", {
                field: field,
                formId: formId,
            });
        },
        /**
         * Registers the form with the given id.
         */
        registerForm(formId) {
            if (this.hasForm(formId)) {
                throw new Error("Form with id '" + formId + "' has already been registered.");
            }
            const form = document.getElementById(formId);
            if (form === null) {
                throw new Error("Unknown form with id '" + formId + "'.");
            }
            _forms.set(formId, form);
            _fields.set(formId, new Map());
            EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "registerForm", {
                formId: formId,
            });
        },
        /**
         * Unregisters the form with the given id.
         */
        unregisterForm: function (formId) {
            if (!this.hasForm(formId)) {
                throw new Error("Unknown form with id '" + formId + "'.");
            }
            EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "beforeUnregisterForm", {
                formId: formId,
            });
            _forms.delete(formId);
            _fields.get(formId).forEach(function (field) {
                field.destroy();
            });
            _fields.delete(formId);
            Manager_1.default.unregister(formId);
            EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "afterUnregisterForm", {
                formId: formId,
            });
        },
    };
    Core.enableLegacyInheritance(FormBuilderManager);
    return FormBuilderManager;
});
