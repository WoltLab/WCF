/**
 * Manages form field dependencies.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */
define(["require", "exports", "tslib", "../../../../Dom/Util", "../../../../Event/Handler"], function (require, exports, tslib_1, Util_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.addContainerCheckCallback = addContainerCheckCallback;
    exports.addDependency = addDependency;
    exports.checkContainers = checkContainers;
    exports.checkDependencies = checkDependencies;
    exports.isHiddenByDependencies = isHiddenByDependencies;
    exports.register = register;
    exports.unregister = unregister;
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    const _dependencyHiddenNodes = new Set();
    const _fields = new Map();
    const _forms = new WeakSet();
    const _nodeDependencies = new Map();
    const _validatedFieldProperties = new WeakMap();
    let _checkingContainers = false;
    let _checkContainersAgain = true;
    /**
     * Hides the given node because of its own dependencies.
     */
    function _hide(node) {
        Util_1.default.hide(node);
        _dependencyHiddenNodes.add(node);
        // also hide tab menu entry
        if (node.classList.contains("tabMenuContent")) {
            node
                .parentNode.querySelector(".tabMenu")
                .querySelectorAll("li")
                .forEach((tabLink) => {
                if (tabLink.dataset.name === node.dataset.name) {
                    Util_1.default.hide(tabLink);
                }
            });
        }
        node.querySelectorAll("[max], [maxlength], [min], [required]").forEach((validatedField) => {
            const properties = new Map();
            const max = validatedField.getAttribute("max");
            if (max) {
                properties.set("max", max);
                validatedField.removeAttribute("max");
            }
            const maxlength = validatedField.getAttribute("maxlength");
            if (maxlength) {
                properties.set("maxlength", maxlength);
                validatedField.removeAttribute("maxlength");
            }
            const min = validatedField.getAttribute("min");
            if (min) {
                properties.set("min", min);
                validatedField.removeAttribute("min");
            }
            if (validatedField.required) {
                properties.set("required", "true");
                validatedField.removeAttribute("required");
            }
            _validatedFieldProperties.set(validatedField, properties);
        });
    }
    /**
     * Shows the given node because of its own dependencies.
     */
    function _show(node) {
        Util_1.default.show(node);
        _dependencyHiddenNodes.delete(node);
        // also show tab menu entry
        if (node.classList.contains("tabMenuContent")) {
            node
                .parentNode.querySelector(".tabMenu")
                .querySelectorAll("li")
                .forEach((tabLink) => {
                if (tabLink.dataset.name === node.dataset.name) {
                    Util_1.default.show(tabLink);
                }
            });
        }
        node.querySelectorAll("input, select").forEach((validatedField) => {
            // if a container is shown, ignore all fields that
            // have a hidden parent element within the container
            let parentNode = validatedField.parentNode;
            while (parentNode !== node && !Util_1.default.isHidden(parentNode)) {
                parentNode = parentNode.parentNode;
            }
            if (parentNode === node && _validatedFieldProperties.has(validatedField)) {
                const properties = _validatedFieldProperties.get(validatedField);
                if (properties.has("max")) {
                    validatedField.setAttribute("max", properties.get("max"));
                }
                if (properties.has("maxlength")) {
                    validatedField.setAttribute("maxlength", properties.get("maxlength"));
                }
                if (properties.has("min")) {
                    validatedField.setAttribute("min", properties.get("min"));
                }
                if (properties.has("required")) {
                    validatedField.setAttribute("required", "");
                }
                _validatedFieldProperties.delete(validatedField);
            }
        });
    }
    /**
     * Adds the given callback to the list of callbacks called when checking containers.
     */
    function addContainerCheckCallback(callback) {
        if (typeof callback !== "function") {
            throw new TypeError("Expected a valid callback for parameter 'callback'.");
        }
        EventHandler.add("com.woltlab.wcf.form.builder.dependency", "checkContainers", callback);
    }
    /**
     * Registers a new form field dependency.
     */
    function addDependency(dependency) {
        const dependentNode = dependency.getDependentNode();
        if (!_nodeDependencies.has(dependentNode.id)) {
            _nodeDependencies.set(dependentNode.id, [dependency]);
        }
        else {
            _nodeDependencies.get(dependentNode.id).push(dependency);
        }
        dependency.getFields().forEach((field) => {
            const id = Util_1.default.identify(field);
            if (!_fields.has(id)) {
                _fields.set(id, field);
                if (field.tagName === "INPUT" &&
                    (field.type === "checkbox" ||
                        field.type === "radio" ||
                        field.type === "hidden")) {
                    field.addEventListener("change", () => checkDependencies());
                }
                else {
                    field.addEventListener("input", () => checkDependencies());
                }
            }
        });
    }
    /**
     * Checks the containers for their availability.
     *
     * If this function is called while containers are currently checked, the containers
     * will be checked after the current check has been finished completely.
     */
    function checkContainers() {
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
        EventHandler.fire("com.woltlab.wcf.form.builder.dependency", "checkContainers");
        // finish checking containers and check if containters should be checked again
        _checkingContainers = false;
        if (_checkContainersAgain) {
            checkContainers();
        }
    }
    /**
     * Checks if all dependencies are met.
     */
    function checkDependencies() {
        const obsoleteNodeIds = [];
        _nodeDependencies.forEach((nodeDependencies, nodeId) => {
            const dependentNode = document.getElementById(nodeId);
            if (dependentNode === null) {
                obsoleteNodeIds.push(nodeId);
                return;
            }
            let dependenciesMet = true;
            nodeDependencies.forEach((dependency) => {
                if (!dependency.checkDependency()) {
                    _hide(dependentNode);
                    dependenciesMet = false;
                }
            });
            if (dependenciesMet) {
                _show(dependentNode);
            }
        });
        obsoleteNodeIds.forEach((id) => _nodeDependencies.delete(id));
        checkContainers();
    }
    /**
     * Returns `true` if the given node has been hidden because of its own dependencies.
     */
    function isHiddenByDependencies(node) {
        if (_dependencyHiddenNodes.has(node)) {
            return true;
        }
        let returnValue = false;
        _dependencyHiddenNodes.forEach((hiddenNode) => {
            if (hiddenNode.contains(node)) {
                returnValue = true;
            }
        });
        return returnValue;
    }
    /**
     * Registers the form with the given id with the dependency manager.
     */
    function register(formId) {
        const form = document.getElementById(formId);
        if (form === null) {
            throw new Error("Unknown element with id '" + formId + "'");
        }
        if (_forms.has(form)) {
            throw new Error("Form with id '" + formId + "' has already been registered.");
        }
        _forms.add(form);
    }
    /**
     * Unregisters the form with the given id and all of its dependencies.
     */
    function unregister(formId) {
        const form = document.getElementById(formId);
        if (form === null) {
            throw new Error("Unknown element with id '" + formId + "'");
        }
        if (!_forms.has(form)) {
            throw new Error("Form with id '" + formId + "' has not been registered.");
        }
        _forms.delete(form);
        _dependencyHiddenNodes.forEach((hiddenNode) => {
            if (form.contains(hiddenNode)) {
                _dependencyHiddenNodes.delete(hiddenNode);
            }
        });
        _nodeDependencies.forEach((dependencies, nodeId) => {
            if (form.contains(document.getElementById(nodeId))) {
                _nodeDependencies.delete(nodeId);
            }
            dependencies.forEach((dependency) => {
                dependency.getFields().forEach((field) => {
                    _fields.delete(field.id);
                    _validatedFieldProperties.delete(field);
                });
            });
        });
    }
});
