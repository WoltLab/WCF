/**
 * Abstract implementation of the JavaScript component of a form field handling a list of packages.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */
define(["require", "exports", "tslib", "../../../../../../Core", "../../../../../../Language", "../../../../../../Dom/Traverse", "../../../../../../Dom/Change/Listener", "../../../../../../Dom/Util"], function (require, exports, tslib_1, Core, Language, DomTraverse, Listener_1, Util_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    class AbstractPackageList {
        constructor(formFieldId, existingPackages) {
            this.formFieldId = formFieldId;
            this.packageList = document.getElementById(`${this.formFieldId}_packageList`);
            if (this.packageList === null) {
                throw new Error(`Cannot find package list for packages field with id '${this.formFieldId}'.`);
            }
            this.packageIdentifier = document.getElementById(`${this.formFieldId}_packageIdentifier`);
            if (this.packageIdentifier === null) {
                throw new Error(`Cannot find package identifier form field for packages field with id '${this.formFieldId}'.`);
            }
            this.packageIdentifier.addEventListener("keypress", (ev) => this.keyPress(ev));
            this.addButton = document.getElementById(`${this.formFieldId}_addButton`);
            if (this.addButton === null) {
                throw new Error(`Cannot find add button for packages field with id '${this.formFieldId}'.`);
            }
            this.addButton.addEventListener("click", (ev) => this.addPackage(ev));
            this.form = this.packageList.closest("form");
            if (this.form === null) {
                throw new Error(`Cannot find form element for packages field with id '${this.formFieldId}'.`);
            }
            this.form.addEventListener("submit", () => this.submit());
            existingPackages.forEach((data) => this.addPackageByData(data));
        }
        /**
         * Adds a package to the package list as a consequence of the given event.
         *
         * If the package data is invalid, an error message is shown and no package is added.
         */
        addPackage(event) {
            event.preventDefault();
            event.stopPropagation();
            // validate data
            if (!this.validateInput()) {
                return;
            }
            this.addPackageByData(this.getInputData());
            // empty fields
            this.emptyInput();
            this.packageIdentifier.focus();
        }
        /**
         * Adds a package to the package list using the given package data.
         */
        addPackageByData(packageData) {
            // add package to list
            const listItem = document.createElement("li");
            this.populateListItem(listItem, packageData);
            // add delete button
            const deleteButton = document.createElement("span");
            deleteButton.className = "icon icon16 fa-times pointer jsTooltip";
            deleteButton.title = Language.get("wcf.global.button.delete");
            deleteButton.addEventListener("click", (ev) => this.removePackage(ev));
            listItem.insertAdjacentElement("afterbegin", deleteButton);
            this.packageList.appendChild(listItem);
            Listener_1.default.trigger();
        }
        /**
         * Creates the hidden fields when the form is submitted.
         */
        createSubmitFields(listElement, index) {
            const packageIdentifier = document.createElement("input");
            packageIdentifier.type = "hidden";
            packageIdentifier.name = `${this.formFieldId}[${index}][packageIdentifier]`;
            packageIdentifier.value = listElement.dataset.packageIdentifier;
            this.form.appendChild(packageIdentifier);
        }
        /**
         * Empties the input fields.
         */
        emptyInput() {
            this.packageIdentifier.value = "";
        }
        /**
         * Returns the current data of the input fields to add a new package.
         */
        getInputData() {
            return {
                packageIdentifier: this.packageIdentifier.value,
            };
        }
        /**
         * Adds a package to the package list after pressing ENTER in a text field.
         */
        keyPress(event) {
            if (event.key === "Enter") {
                this.addPackage(event);
            }
        }
        /**
         * Adds all necessary package-relavant data to the given list item.
         */
        populateListItem(listItem, packageData) {
            listItem.dataset.packageIdentifier = packageData.packageIdentifier;
        }
        /**
         * Removes a package by clicking on its delete button.
         */
        removePackage(event) {
            event.currentTarget.closest("li").remove();
            // remove field errors if the last package has been deleted
            Util_1.default.innerError(this.packageList, "");
        }
        /**
         * Adds all necessary (hidden) form fields to the form when submitting the form.
         */
        submit() {
            DomTraverse.childrenByTag(this.packageList, "LI").forEach((listItem, index) => this.createSubmitFields(listItem, index));
        }
        /**
         * Returns `true` if the currently entered package data is valid. Otherwise `false` is returned and relevant error
         * messages are shown.
         */
        validateInput() {
            return this.validatePackageIdentifier();
        }
        /**
         * Returns `true` if the currently entered package identifier is valid. Otherwise `false` is returned and an error
         * message is shown.
         */
        validatePackageIdentifier() {
            const packageIdentifier = this.packageIdentifier.value;
            if (packageIdentifier === "") {
                Util_1.default.innerError(this.packageIdentifier, Language.get("wcf.global.form.error.empty"));
                return false;
            }
            if (packageIdentifier.length < 3) {
                Util_1.default.innerError(this.packageIdentifier, Language.get("wcf.acp.devtools.project.packageIdentifier.error.minimumLength"));
                return false;
            }
            else if (packageIdentifier.length > 191) {
                Util_1.default.innerError(this.packageIdentifier, Language.get("wcf.acp.devtools.project.packageIdentifier.error.maximumLength"));
                return false;
            }
            if (!AbstractPackageList.packageIdentifierRegExp.test(packageIdentifier)) {
                Util_1.default.innerError(this.packageIdentifier, Language.get("wcf.acp.devtools.project.packageIdentifier.error.format"));
                return false;
            }
            // check if package has already been added
            const duplicate = DomTraverse.childrenByTag(this.packageList, "LI").some((listItem) => listItem.dataset.packageIdentifier === packageIdentifier);
            if (duplicate) {
                Util_1.default.innerError(this.packageIdentifier, Language.get("wcf.acp.devtools.project.packageIdentifier.error.duplicate"));
                return false;
            }
            // remove outdated errors
            Util_1.default.innerError(this.packageIdentifier, "");
            return true;
        }
        /**
         * Returns `true` if the given version is valid. Otherwise `false` is returned and an error message is shown.
         */
        validateVersion(versionElement) {
            const version = versionElement.value;
            // see `wcf\data\package\Package::isValidVersion()`
            // the version is no a required attribute
            if (version !== "") {
                if (version.length > 255) {
                    Util_1.default.innerError(versionElement, Language.get("wcf.acp.devtools.project.packageVersion.error.maximumLength"));
                    return false;
                }
                if (!AbstractPackageList.versionRegExp.test(version)) {
                    Util_1.default.innerError(versionElement, Language.get("wcf.acp.devtools.project.packageVersion.error.format"));
                    return false;
                }
            }
            // remove outdated errors
            Util_1.default.innerError(versionElement, "");
            return true;
        }
    }
    // see `wcf\data\package\Package::isValidPackageName()`
    AbstractPackageList.packageIdentifierRegExp = new RegExp(/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/);
    // see `wcf\data\package\Package::isValidVersion()`
    AbstractPackageList.versionRegExp = new RegExp(/^([0-9]+).([0-9]+)\.([0-9]+)( (a|alpha|b|beta|d|dev|rc|pl) ([0-9]+))?$/i);
    Core.enableLegacyInheritance(AbstractPackageList);
    return AbstractPackageList;
});
