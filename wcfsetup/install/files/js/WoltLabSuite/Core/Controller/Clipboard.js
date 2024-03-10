/**
 * Clipboard API Handler.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../Ajax", "../Core", "../Dom/Change/Listener", "../Dom/Util", "../Event/Handler", "../Language", "../Ui/Confirmation", "../Ui/Dropdown/Simple", "../Ui/Page/Action", "../Ui/Screen"], function (require, exports, tslib_1, Ajax, Core, Listener_1, Util_1, EventHandler, Language, UiConfirmation, Simple_1, UiPageAction, UiScreen) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.unmark = exports.showEditor = exports.hideEditor = exports.reload = exports.setup = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    UiPageAction = tslib_1.__importStar(UiPageAction);
    UiScreen = tslib_1.__importStar(UiScreen);
    class ControllerClipboard {
        containers = new Map();
        editors = new Map();
        editorDropdowns = new Map();
        itemData = new WeakMap();
        knownCheckboxes = new WeakSet();
        pageClassNames = [];
        pageObjectId = 0;
        reloadPageOnSuccess = new Map();
        /**
         * Initializes the clipboard API handler.
         */
        setup(options) {
            if (!options.pageClassName) {
                throw new Error("Expected a non-empty string for parameter 'pageClassName'.");
            }
            let hasMarkedItems = false;
            if (this.pageClassNames.length === 0) {
                hasMarkedItems = options.hasMarkedItems;
                this.pageObjectId = options.pageObjectId;
            }
            this.pageClassNames.push(options.pageClassName);
            this.initContainers();
            if (hasMarkedItems && this.containers.size) {
                this.loadMarkedItems();
            }
            Listener_1.default.add("WoltLabSuite/Core/Controller/Clipboard", () => this.initContainers());
        }
        /**
         * Reloads the clipboard data.
         */
        reload() {
            if (this.containers.size) {
                this.loadMarkedItems();
            }
        }
        /**
         * Initializes clipboard containers.
         */
        initContainers() {
            document.querySelectorAll(".jsClipboardContainer").forEach((container) => {
                const containerId = Util_1.default.identify(container);
                let containerData = this.containers.get(containerId);
                if (containerData === undefined) {
                    const markAll = container.querySelector(".jsClipboardMarkAll");
                    if (markAll !== null) {
                        markAll.dataset.containerId = containerId;
                        markAll.addEventListener("click", (ev) => this.markAll(ev));
                    }
                    containerData = {
                        checkboxes: container.getElementsByClassName("jsClipboardItem"),
                        element: container,
                        markAll: markAll,
                        markedObjectIds: new Set(),
                    };
                    this.containers.set(containerId, containerData);
                }
                Array.from(containerData.checkboxes).forEach((checkbox) => {
                    if (this.knownCheckboxes.has(checkbox)) {
                        return;
                    }
                    checkbox.dataset.containerId = containerId;
                    const link = checkbox.closest("a");
                    if (link === null) {
                        checkbox.addEventListener("click", (ev) => this.mark(ev));
                    }
                    else {
                        // Firefox will always trigger the link if the checkbox is
                        // inside of one. Since 2000. Thanks Firefox.
                        checkbox.addEventListener("click", (event) => {
                            event.preventDefault();
                            window.setTimeout(() => {
                                checkbox.checked = !checkbox.checked;
                                this.mark(checkbox);
                            }, 10);
                        });
                    }
                    this.knownCheckboxes.add(checkbox);
                });
            });
        }
        /**
         * Loads marked items from clipboard.
         */
        loadMarkedItems() {
            Ajax.api(this, {
                actionName: "getMarkedItems",
                parameters: {
                    pageClassNames: this.pageClassNames,
                    pageObjectID: this.pageObjectId,
                },
            });
        }
        /**
         * Marks or unmarks all visible items at once.
         */
        markAll(event) {
            const checkbox = event.currentTarget;
            const isMarked = checkbox.nodeName !== "INPUT" || checkbox.checked;
            this.setParentAsMarked(checkbox, isMarked);
            const objectIds = [];
            const containerId = checkbox.dataset.containerId;
            const data = this.containers.get(containerId);
            const type = data.element.dataset.type;
            Array.from(data.checkboxes).forEach((item) => {
                const objectId = ~~item.dataset.objectId;
                if (isMarked) {
                    if (!item.checked) {
                        item.checked = true;
                        data.markedObjectIds.add(objectId);
                        objectIds.push(objectId);
                    }
                }
                else {
                    if (item.checked) {
                        item.checked = false;
                        data.markedObjectIds["delete"](objectId);
                        objectIds.push(objectId);
                    }
                }
                this.setParentAsMarked(item, isMarked);
                const clipboardObject = checkbox.closest(".jsClipboardObject");
                if (clipboardObject !== null) {
                    if (isMarked) {
                        clipboardObject.classList.add("jsMarked");
                    }
                    else {
                        clipboardObject.classList.remove("jsMarked");
                    }
                }
            });
            this.saveState(type, objectIds, isMarked);
        }
        /**
         * Marks or unmarks an individual item.
         *
         */
        mark(event) {
            const checkbox = event instanceof Event ? event.currentTarget : event;
            const objectId = ~~checkbox.dataset.objectId;
            const isMarked = checkbox.checked;
            const containerId = checkbox.dataset.containerId;
            const data = this.containers.get(containerId);
            const type = data.element.dataset.type;
            const clipboardObject = checkbox.closest(".jsClipboardObject");
            if (isMarked) {
                data.markedObjectIds.add(objectId);
                clipboardObject.classList.add("jsMarked");
            }
            else {
                data.markedObjectIds.delete(objectId);
                clipboardObject.classList.remove("jsMarked");
            }
            if (data.markAll !== null) {
                data.markAll.checked = !Array.from(data.checkboxes).some((item) => !item.checked);
                this.setParentAsMarked(data.markAll, isMarked);
            }
            this.setParentAsMarked(checkbox, checkbox.checked);
            this.saveState(type, [objectId], isMarked);
        }
        /**
         * Saves the state for given item object ids.
         */
        saveState(objectType, objectIds, isMarked) {
            Ajax.api(this, {
                actionName: isMarked ? "mark" : "unmark",
                parameters: {
                    pageClassNames: this.pageClassNames,
                    pageObjectID: this.pageObjectId,
                    objectIDs: objectIds,
                    objectType,
                },
            });
        }
        /**
         * Executes an editor action.
         */
        executeAction(event) {
            const listItem = event.currentTarget;
            const data = this.itemData.get(listItem);
            if (data.url) {
                window.location.href = data.url;
                return;
            }
            function triggerEvent() {
                const type = listItem.dataset.type;
                EventHandler.fire("com.woltlab.wcf.clipboard", type, {
                    data,
                    listItem,
                    responseData: null,
                });
            }
            const message = typeof data.internalData.confirmMessage === "string" ? data.internalData.confirmMessage : "";
            let fireEvent = true;
            if (Core.isPlainObject(data.parameters) && data.parameters.actionName && data.parameters.className) {
                if (data.parameters.actionName === "unmarkAll" || Array.isArray(data.parameters.objectIDs)) {
                    if (message.length) {
                        const template = typeof data.internalData.template === "string" ? data.internalData.template : "";
                        UiConfirmation.show({
                            confirm: () => {
                                const formData = {};
                                if (template.length) {
                                    UiConfirmation.getContentElement()
                                        .querySelectorAll("input, select, textarea")
                                        .forEach((item) => {
                                        const name = item.name;
                                        switch (item.nodeName) {
                                            case "INPUT":
                                                if ((item.type !== "checkbox" && item.type !== "radio") || item.checked) {
                                                    formData[name] = item.value;
                                                }
                                                break;
                                            case "SELECT":
                                                formData[name] = item.value;
                                                break;
                                            case "TEXTAREA":
                                                formData[name] = item.value.trim();
                                                break;
                                        }
                                    });
                                }
                                this.executeProxyAction(listItem, data, formData);
                            },
                            message,
                            template,
                        });
                    }
                    else {
                        this.executeProxyAction(listItem, data);
                    }
                }
            }
            else if (message.length) {
                fireEvent = false;
                UiConfirmation.show({
                    confirm: triggerEvent,
                    message,
                });
            }
            if (fireEvent) {
                triggerEvent();
            }
        }
        /**
         * Forwards clipboard actions to an individual handler.
         */
        executeProxyAction(listItem, data, formData = {}) {
            const objectIds = data.parameters.actionName !== "unmarkAll" ? data.parameters.objectIDs : [];
            const parameters = { data: formData };
            if (Core.isPlainObject(data.internalData.parameters)) {
                Object.entries(data.internalData.parameters).forEach(([key, value]) => {
                    parameters[key] = value;
                });
            }
            Ajax.api(this, {
                actionName: data.parameters.actionName,
                className: data.parameters.className,
                objectIDs: objectIds,
                parameters,
            }, (responseData) => {
                if (data.actionName !== "unmarkAll") {
                    const type = listItem.dataset.type;
                    EventHandler.fire("com.woltlab.wcf.clipboard", type, {
                        data,
                        listItem,
                        responseData,
                    });
                    const reloadPageOnSuccess = this.reloadPageOnSuccess.get(type);
                    if (reloadPageOnSuccess && reloadPageOnSuccess.includes(responseData.actionName)) {
                        window.location.reload();
                        return;
                    }
                }
                this.loadMarkedItems();
            });
        }
        /**
         * Unmarks all clipboard items for an object type.
         */
        unmarkAll(event) {
            const listItem = event.currentTarget;
            Ajax.api(this, {
                actionName: "unmarkAll",
                parameters: {
                    objectType: listItem.dataset.type,
                },
            });
        }
        /**
         * Sets up ajax request object.
         */
        _ajaxSetup() {
            return {
                data: {
                    className: "wcf\\data\\clipboard\\item\\ClipboardItemAction",
                },
            };
        }
        /**
         * Handles successful AJAX requests.
         */
        _ajaxSuccess(data) {
            if (data.actionName === "unmarkAll") {
                const objectType = data.returnValues.objectType;
                this.containers.forEach((containerData) => {
                    if (containerData.element.dataset.type !== objectType) {
                        return;
                    }
                    containerData.element.querySelectorAll(".jsMarked").forEach((element) => element.classList.remove("jsMarked"));
                    if (containerData.markAll !== null) {
                        containerData.markAll.checked = false;
                        this.setParentAsMarked(containerData.markAll, false);
                    }
                    Array.from(containerData.checkboxes).forEach((checkbox) => {
                        checkbox.checked = false;
                        this.setParentAsMarked(checkbox, false);
                    });
                    UiPageAction.remove(`wcfClipboard-${objectType}`);
                });
                return;
            }
            this.itemData = new WeakMap();
            this.reloadPageOnSuccess.clear();
            // rebuild markings
            const markings = Core.isPlainObject(data.returnValues.markedItems) ? data.returnValues.markedItems : {};
            this.containers.forEach((containerData) => {
                const typeName = containerData.element.dataset.type;
                const objectIds = Array.isArray(markings[typeName]) ? markings[typeName] : [];
                this.rebuildMarkings(containerData, objectIds);
            });
            const keepEditors = Object.keys(data.returnValues.items || {});
            // clear editors
            this.editors.forEach((editor, typeName) => {
                if (!keepEditors.includes(typeName)) {
                    UiPageAction.remove(`wcfClipboard-${typeName}`);
                    this.editorDropdowns.get(typeName).innerHTML = "";
                }
            });
            // no items
            if (!data.returnValues.items) {
                return;
            }
            // rebuild editors
            Object.entries(data.returnValues.items).forEach(([typeName, typeData]) => {
                this.reloadPageOnSuccess.set(typeName, typeData.reloadPageOnSuccess);
                let created = false;
                let editor = this.editors.get(typeName);
                let dropdown = this.editorDropdowns.get(typeName);
                if (editor === undefined) {
                    created = true;
                    editor = document.createElement("a");
                    editor.className = "dropdownToggle";
                    editor.textContent = typeData.label;
                    this.editors.set(typeName, editor);
                    dropdown = document.createElement("ol");
                    dropdown.className = "dropdownMenu";
                    this.editorDropdowns.set(typeName, dropdown);
                }
                else {
                    editor.textContent = typeData.label;
                    dropdown.innerHTML = "";
                }
                // create editor items
                Object.values(typeData.items).forEach((itemData) => {
                    const item = document.createElement("li");
                    const label = document.createElement("span");
                    label.textContent = itemData.label;
                    item.appendChild(label);
                    dropdown.appendChild(item);
                    item.dataset.type = typeName;
                    item.addEventListener("click", (ev) => this.executeAction(ev));
                    this.itemData.set(item, itemData);
                });
                const divider = document.createElement("li");
                divider.classList.add("dropdownDivider");
                dropdown.appendChild(divider);
                // add 'unmark all'
                const unmarkAll = document.createElement("li");
                unmarkAll.dataset.type = typeName;
                const label = document.createElement("span");
                label.textContent = Language.get("wcf.clipboard.item.unmarkAll");
                unmarkAll.appendChild(label);
                unmarkAll.addEventListener("click", (ev) => this.unmarkAll(ev));
                dropdown.appendChild(unmarkAll);
                if (keepEditors.indexOf(typeName) !== -1) {
                    const actionName = `wcfClipboard-${typeName}`;
                    if (UiPageAction.has(actionName)) {
                        UiPageAction.show(actionName);
                    }
                    else {
                        UiPageAction.add(actionName, editor);
                        created = true;
                    }
                }
                if (created) {
                    const parent = editor.parentElement;
                    parent.classList.add("dropdown");
                    parent.appendChild(dropdown);
                    Simple_1.default.init(editor);
                }
            });
        }
        /**
         * Rebuilds the mark state for each item.
         */
        rebuildMarkings(data, objectIds) {
            let markAll = true;
            Array.from(data.checkboxes).forEach((checkbox) => {
                const clipboardObject = checkbox.closest(".jsClipboardObject");
                const isMarked = objectIds.includes(~~checkbox.dataset.objectId);
                if (!isMarked) {
                    markAll = false;
                }
                checkbox.checked = isMarked;
                if (isMarked) {
                    clipboardObject.classList.add("jsMarked");
                }
                else {
                    clipboardObject.classList.remove("jsMarked");
                }
                this.setParentAsMarked(checkbox, isMarked);
            });
            if (data.markAll !== null) {
                data.markAll.checked = markAll;
                this.setParentAsMarked(data.markAll, markAll);
                const parent = data.markAll.closest(".columnMark")?.parentNode;
                if (parent) {
                    if (markAll) {
                        parent.classList.add("jsMarked");
                    }
                    else {
                        parent.classList.remove("jsMarked");
                    }
                }
            }
        }
        setParentAsMarked(element, isMarked) {
            const parent = element.parentElement;
            if (parent.getAttribute("role") === "checkbox") {
                parent.setAttribute("aria-checked", isMarked ? "true" : "false");
            }
        }
        /**
         * Hides the clipboard editor for the given object type.
         */
        hideEditor(objectType) {
            UiPageAction.remove("wcfClipboard-" + objectType);
            UiScreen.pageOverlayOpen();
        }
        /**
         * Shows the clipboard editor.
         */
        showEditor() {
            this.loadMarkedItems();
            UiScreen.pageOverlayClose();
        }
        /**
         * Unmarks the objects with given clipboard object type and ids.
         */
        unmark(objectType, objectIds) {
            this.saveState(objectType, objectIds, false);
        }
    }
    let controllerClipboard;
    function getControllerClipboard() {
        if (!controllerClipboard) {
            controllerClipboard = new ControllerClipboard();
        }
        return controllerClipboard;
    }
    /**
     * Initializes the clipboard API handler.
     */
    function setup(options) {
        getControllerClipboard().setup(options);
    }
    exports.setup = setup;
    /**
     * Reloads the clipboard data.
     */
    function reload() {
        getControllerClipboard().reload();
    }
    exports.reload = reload;
    /**
     * Hides the clipboard editor for the given object type.
     */
    function hideEditor(objectType) {
        getControllerClipboard().hideEditor(objectType);
    }
    exports.hideEditor = hideEditor;
    /**
     * Shows the clipboard editor.
     */
    function showEditor() {
        getControllerClipboard().showEditor();
    }
    exports.showEditor = showEditor;
    /**
     * Unmarks the objects with given clipboard object type and ids.
     */
    function unmark(objectType, objectIds) {
        getControllerClipboard().unmark(objectType, objectIds);
    }
    exports.unmark = unmark;
});
