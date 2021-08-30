/**
 * Manages the packages entered in a devtools project required package form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Acp/Builder/Field/Devtools/Project/RequiredPackages
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */
define(["require", "exports", "tslib", "./AbstractPackageList", "../../../../../../Core", "../../../../../../Language"], function (require, exports, tslib_1, AbstractPackageList_1, Core, Language) {
    "use strict";
    AbstractPackageList_1 = (0, tslib_1.__importDefault)(AbstractPackageList_1);
    Core = (0, tslib_1.__importStar)(Core);
    Language = (0, tslib_1.__importStar)(Language);
    class RequiredPackages extends AbstractPackageList_1.default {
        constructor(formFieldId, existingPackages) {
            super(formFieldId, existingPackages);
            this.minVersion = document.getElementById(`${this.formFieldId}_minVersion`);
            if (this.minVersion === null) {
                throw new Error(`Cannot find minimum version form field for packages field with id '${this.formFieldId}'.`);
            }
            this.minVersion.addEventListener("keypress", (ev) => this.keyPress(ev));
            this.file = document.getElementById(`${this.formFieldId}_file`);
            if (this.file === null) {
                throw new Error(`Cannot find file form field for required field with id '${this.formFieldId}'.`);
            }
        }
        createSubmitFields(listElement, index) {
            super.createSubmitFields(listElement, index);
            ["minVersion", "file"].forEach((property) => {
                const element = document.createElement("input");
                element.type = "hidden";
                element.name = `${this.formFieldId}[${index}][${property}]`;
                element.value = listElement.dataset[property];
                this.form.appendChild(element);
            });
        }
        emptyInput() {
            super.emptyInput();
            this.minVersion.value = "";
            this.file.checked = false;
        }
        getInputData() {
            return Core.extend(super.getInputData(), {
                file: this.file.checked,
                minVersion: this.minVersion.value,
            });
        }
        populateListItem(listItem, packageData) {
            super.populateListItem(listItem, packageData);
            listItem.dataset.minVersion = packageData.minVersion;
            listItem.dataset.file = packageData.file ? "1" : "0";
            listItem.innerHTML = ` ${Language.get("wcf.acp.devtools.project.requiredPackage.requiredPackage", {
                file: packageData.file,
                minVersion: packageData.minVersion,
                packageIdentifier: packageData.packageIdentifier,
            })}`;
        }
        validateInput() {
            return super.validateInput() && this.validateVersion(this.minVersion);
        }
    }
    Core.enableLegacyInheritance(RequiredPackages);
    return RequiredPackages;
});
