/**
 * Abstract implementation of a form field dependency.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Manager", "../../../../Core"], function (require, exports, tslib_1, Manager_1, Core) {
    "use strict";
    Manager_1 = tslib_1.__importDefault(Manager_1);
    Core = tslib_1.__importStar(Core);
    class FormBuilderFormFieldDependency {
        constructor(dependentElementId, fieldId) {
            this.init(dependentElementId, fieldId);
        }
        /**
         * Returns `true` if the dependency is met.
         */
        checkDependency() {
            throw new Error("Missing implementation of WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract.checkDependency!");
        }
        /**
         * Return the node whose availability depends on the value of a field.
         */
        getDependentNode() {
            return this._dependentElement;
        }
        /**
         * Returns the field the availability of the element dependents on.
         */
        getField() {
            return this._field;
        }
        /**
         * Returns all fields requiring event listeners for this dependency to be properly resolved.
         */
        getFields() {
            return this._fields;
        }
        /**
         * Initializes the new dependency object.
         */
        init(dependentElementId, fieldId) {
            this._dependentElement = document.getElementById(dependentElementId);
            if (this._dependentElement === null) {
                throw new Error("Unknown dependent element with container id '" + dependentElementId + "Container'.");
            }
            this._field = document.getElementById(fieldId);
            if (this._field === null) {
                this._fields = [];
                document.querySelectorAll("input[type=radio][name=" + fieldId + "]").forEach((field) => {
                    this._fields.push(field);
                });
                if (!this._fields.length) {
                    document
                        .querySelectorAll('input[type=checkbox][name="' + fieldId + '[]"]')
                        .forEach((field) => {
                        this._fields.push(field);
                    });
                    if (!this._fields.length) {
                        throw new Error("Unknown field with id '" + fieldId + "'.");
                    }
                }
            }
            else {
                this._fields = [this._field];
                // Handle special case of boolean form fields that have two form fields.
                if (this._field.tagName === "INPUT" &&
                    this._field.type === "radio" &&
                    this._field.dataset.noInputId !== "") {
                    this._noField = document.getElementById(this._field.dataset.noInputId);
                    if (this._noField === null) {
                        throw new Error("Cannot find 'no' input field for input field '" + fieldId + "'");
                    }
                    this._fields.push(this._noField);
                }
            }
            Manager_1.default.addDependency(this);
        }
    }
    Core.enableLegacyInheritance(FormBuilderFormFieldDependency);
    return FormBuilderFormFieldDependency;
});
