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
define(["require", "exports", "tslib", "../../Core", "../../Event/Handler", "./Field/Field", "./Field/Dependency/Manager"], function (require, exports, tslib_1, Core, EventHandler, Field_1, DependencyManager) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.unregisterForm = exports.registerForm = exports.registerField = exports.hasForm = exports.hasField = exports.getForm = exports.getField = exports.getData = void 0;
    Core = (0, tslib_1.__importStar)(Core);
    EventHandler = (0, tslib_1.__importStar)(EventHandler);
    Field_1 = (0, tslib_1.__importDefault)(Field_1);
    DependencyManager = (0, tslib_1.__importStar)(DependencyManager);
    const _fields = new Map();
    const _forms = new Map();
    /**
     * Returns a promise returning the data of the form with the given id.
     */
    function getData(formId) {
        if (!hasForm(formId)) {
            throw new Error("Unknown form with id '" + formId + "'.");
        }
        const promises = [];
        _fields.get(formId).forEach((field) => {
            const fieldData = field.getData();
            if (!(fieldData instanceof Promise)) {
                throw new TypeError("Data for field with id '" + field.getId() + "' is no promise.");
            }
            promises.push(fieldData);
        });
        return Promise.all(promises).then((promiseData) => {
            return promiseData.reduce((carry, current) => Core.extend(carry, current), {});
        });
    }
    exports.getData = getData;
    /**
     * Returns the registered form field with given.
     *
     * @since 5.2.3
     */
    function getField(formId, fieldId) {
        if (!hasField(formId, fieldId)) {
            throw new Error("Unknown field with id '" + formId + "' for form with id '" + fieldId + "'.");
        }
        return _fields.get(formId).get(fieldId);
    }
    exports.getField = getField;
    /**
     * Returns the registered form with given id.
     */
    function getForm(formId) {
        if (!hasForm(formId)) {
            throw new Error("Unknown form with id '" + formId + "'.");
        }
        return _forms.get(formId);
    }
    exports.getForm = getForm;
    /**
     * Returns `true` if a field with the given id has been registered for the form with the given id
     * and `false` otherwise.
     */
    function hasField(formId, fieldId) {
        if (!hasForm(formId)) {
            throw new Error("Unknown form with id '" + formId + "'.");
        }
        return _fields.get(formId).has(fieldId);
    }
    exports.hasField = hasField;
    /**
     * Returns `true` if a form with the given id has been registered and `false` otherwise.
     */
    function hasForm(formId) {
        return _forms.has(formId);
    }
    exports.hasForm = hasForm;
    /**
     * Registers the given field for the form with the given id.
     */
    function registerField(formId, field) {
        if (!hasForm(formId)) {
            throw new Error("Unknown form with id '" + formId + "'.");
        }
        if (!(field instanceof Field_1.default)) {
            throw new Error("Add field is no instance of 'WoltLabSuite/Core/Form/Builder/Field/Field'.");
        }
        const fieldId = field.getId();
        if (hasField(formId, fieldId)) {
            throw new Error("Form field with id '" + fieldId + "' has already been registered for form with id '" + formId + "'.");
        }
        _fields.get(formId).set(fieldId, field);
        EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "registerField", {
            field: field,
            formId: formId,
        });
    }
    exports.registerField = registerField;
    /**
     * Registers the form with the given id.
     */
    function registerForm(formId) {
        if (hasForm(formId)) {
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
    }
    exports.registerForm = registerForm;
    /**
     * Unregisters the form with the given id.
     */
    function unregisterForm(formId) {
        if (!hasForm(formId)) {
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
        DependencyManager.unregister(formId);
        EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "afterUnregisterForm", {
            formId: formId,
        });
    }
    exports.unregisterForm = unregisterForm;
});
