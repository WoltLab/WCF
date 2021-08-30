/**
 * Manages the packages entered in a devtools project excluded package form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/ExcludedPackages
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */
define(["require", "exports", "tslib", "./AbstractPackageList", "../../../../../../Core", "../../../../../../Language", "../../../../../../Dom/Util"], function (require, exports, tslib_1, AbstractPackageList_1, Core, Language, Util_1) {
    "use strict";
    AbstractPackageList_1 = (0, tslib_1.__importDefault)(AbstractPackageList_1);
    Core = (0, tslib_1.__importStar)(Core);
    Language = (0, tslib_1.__importStar)(Language);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    class ExcludedPackages extends AbstractPackageList_1.default {
        constructor(formFieldId, existingPackages) {
            super(formFieldId, existingPackages);
            this.version = document.getElementById(`${this.formFieldId}_version`);
            if (this.version === null) {
                throw new Error(`Cannot find version form field for packages field with id '${this.formFieldId}'.`);
            }
            this.version.addEventListener("keypress", (ev) => this.keyPress(ev));
        }
        createSubmitFields(listElement, index) {
            super.createSubmitFields(listElement, index);
            const version = document.createElement("input");
            version.type = "hidden";
            version.name = `${this.formFieldId}[${index}][version]`;
            version.value = listElement.dataset.version;
            this.form.appendChild(version);
        }
        emptyInput() {
            super.emptyInput();
            this.version.value = "";
        }
        getInputData() {
            return Core.extend(super.getInputData(), {
                version: this.version.value,
            });
        }
        populateListItem(listItem, packageData) {
            super.populateListItem(listItem, packageData);
            listItem.dataset.version = packageData.version;
            listItem.innerHTML = ` ${Language.get("wcf.acp.devtools.project.excludedPackage.excludedPackage", {
                packageIdentifier: packageData.packageIdentifier,
                version: packageData.version,
            })}`;
        }
        validateInput() {
            return super.validateInput() && this.validateVersion(this.version);
        }
        validateVersion(versionElement) {
            const version = versionElement.value;
            if (version === "") {
                Util_1.default.innerError(versionElement, Language.get("wcf.global.form.error.empty"));
                return false;
            }
            else if (version !== "*") {
                return super.validateVersion(versionElement);
            }
            return true;
        }
    }
    Core.enableLegacyInheritance(ExcludedPackages);
    return ExcludedPackages;
});
