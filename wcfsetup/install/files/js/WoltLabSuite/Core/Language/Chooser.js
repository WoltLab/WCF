/**
 * Dropdown language chooser.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Core", "../Language", "../Dom/Util", "../Ui/Dropdown/Simple"], function (require, exports, tslib_1, Core, Language, Util_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    exports.getChooser = getChooser;
    exports.getLanguageId = getLanguageId;
    exports.removeChooser = removeChooser;
    exports.setLanguageId = setLanguageId;
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    const _choosers = new Map();
    const _forms = new WeakMap();
    /**
     * Sets up DOM and event listeners for a language chooser.
     */
    function initElement(chooserId, element, languageId, languages, callback, allowEmptyValue) {
        let container;
        const parent = element.parentElement;
        if (parent.nodeName === "DD") {
            container = document.createElement("div");
            container.className = "dropdown";
            // language chooser is the first child so that descriptions and error messages
            // are always shown below the language chooser
            parent.insertAdjacentElement("afterbegin", container);
        }
        else {
            container = parent;
            container.classList.add("dropdown");
        }
        Util_1.default.hide(element);
        const dropdownToggle = document.createElement("a");
        dropdownToggle.className = "dropdownToggle boxFlag box24 inputPrefix";
        if (parent.nodeName === "DD") {
            dropdownToggle.classList.add("button");
        }
        container.appendChild(dropdownToggle);
        const dropdownMenu = document.createElement("ul");
        dropdownMenu.className = "dropdownMenu";
        container.appendChild(dropdownMenu);
        function callbackClick(event) {
            const target = event.currentTarget;
            const languageId = ~~target.dataset.languageId;
            const activeItem = dropdownMenu.querySelector(".active");
            if (activeItem !== null) {
                activeItem.classList.remove("active");
            }
            if (languageId) {
                target.classList.add("active");
            }
            select(chooserId, languageId, target);
        }
        const icon = document.createElement("fa-icon");
        icon.setIcon("caret-down", true);
        // add language dropdown items
        Object.entries(languages).forEach(([langId, language]) => {
            const listItem = document.createElement("li");
            listItem.className = "boxFlag";
            listItem.addEventListener("click", callbackClick);
            listItem.dataset.languageId = langId;
            if (language.languageCode !== undefined) {
                listItem.dataset.languageCode = language.languageCode;
            }
            dropdownMenu.appendChild(listItem);
            const link = document.createElement("a");
            link.className = "box24";
            link.href = "#";
            link.addEventListener("click", (event) => event.preventDefault());
            listItem.appendChild(link);
            const img = document.createElement("img");
            img.src = language.iconPath;
            img.alt = "";
            img.className = "iconFlag";
            img.height = 15;
            img.width = 24;
            img.loading = "eager";
            link.appendChild(img);
            const span = document.createElement("span");
            span.textContent = language.languageName;
            link.appendChild(span);
            if (+langId === languageId) {
                dropdownToggle.innerHTML = link.innerHTML;
                dropdownToggle.append(icon);
            }
        });
        // add dropdown item for "no selection"
        if (allowEmptyValue) {
            const divider = document.createElement("li");
            divider.className = "dropdownDivider";
            dropdownMenu.appendChild(divider);
            const listItem = document.createElement("li");
            listItem.dataset.languageId = "0";
            listItem.addEventListener("click", callbackClick);
            dropdownMenu.appendChild(listItem);
            const link = document.createElement("a");
            link.textContent = Language.get("wcf.global.language.noSelection");
            listItem.appendChild(link);
            if (languageId === 0) {
                dropdownToggle.innerHTML = link.innerHTML;
                dropdownToggle.append(icon);
            }
            listItem.addEventListener("click", callbackClick);
        }
        else if (languageId === 0) {
            dropdownToggle.innerHTML = "";
            const div = document.createElement("div");
            dropdownToggle.appendChild(div);
            const icon = document.createElement("fa-icon");
            icon.size = 24;
            icon.setIcon("question");
            div.appendChild(icon);
            const span = document.createElement("span");
            span.textContent = Language.get("wcf.global.language.noSelection");
            span.append(icon);
            div.appendChild(span);
        }
        Simple_1.default.init(dropdownToggle);
        _choosers.set(chooserId, {
            callback: callback,
            dropdownMenu: dropdownMenu,
            dropdownToggle: dropdownToggle,
            element: element,
        });
        // bind to submit event
        const form = element.closest("form");
        if (form !== null) {
            form.addEventListener("submit", onSubmit);
            let chooserIds = _forms.get(form);
            if (chooserIds === undefined) {
                chooserIds = [];
                _forms.set(form, chooserIds);
            }
            chooserIds.push(chooserId);
        }
    }
    /**
     * Selects a language from the dropdown list.
     */
    function select(chooserId, languageId, listItem) {
        const chooser = _choosers.get(chooserId);
        if (listItem === undefined) {
            listItem = Array.from(chooser.dropdownMenu.children).find((element) => {
                return ~~element.dataset.languageId === languageId;
            });
            if (listItem === undefined) {
                throw new Error(`The language id '${languageId}' is unknown`);
            }
        }
        chooser.element.value = languageId.toString();
        Core.triggerEvent(chooser.element, "change");
        chooser.dropdownToggle.innerHTML = listItem.children[0].innerHTML;
        const icon = document.createElement("fa-icon");
        icon.setIcon("caret-down", true);
        chooser.dropdownToggle.append(icon);
        _choosers.set(chooserId, chooser);
        // execute callback
        if (typeof chooser.callback === "function") {
            chooser.callback(listItem);
        }
    }
    /**
     * Inserts hidden fields for the language chooser value on submit.
     */
    function onSubmit(event) {
        const form = event.currentTarget;
        const elementIds = _forms.get(form);
        elementIds.forEach((elementId) => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = elementId;
            input.value = getLanguageId(elementId).toString();
            form.appendChild(input);
        });
    }
    /**
     * Initializes a language chooser.
     */
    function init(containerId, chooserId, languageId, languages, callback, allowEmptyValue) {
        if (_choosers.has(chooserId)) {
            return;
        }
        const container = document.getElementById(containerId);
        if (container === null) {
            throw new Error(`Expected a valid container id, cannot find '${chooserId}'.`);
        }
        let element = document.getElementById(chooserId);
        if (element === null) {
            element = document.createElement("input");
            element.type = "hidden";
            element.id = chooserId;
            element.name = chooserId;
            element.value = languageId.toString();
            container.appendChild(element);
        }
        initElement(chooserId, element, languageId, languages, callback, allowEmptyValue);
    }
    /**
     * Returns the chooser for an input field.
     */
    function getChooser(chooserId) {
        const chooser = _choosers.get(chooserId);
        if (chooser === undefined) {
            throw new Error(`Expected a valid language chooser input element, '${chooserId}' is not i18n input field.`);
        }
        return chooser;
    }
    /**
     * Returns the selected language for a certain chooser.
     */
    function getLanguageId(chooserId) {
        return ~~getChooser(chooserId).element.value;
    }
    /**
     * Removes the chooser with given id.
     */
    function removeChooser(chooserId) {
        _choosers.delete(chooserId);
    }
    /**
     * Sets the language for a certain chooser.
     */
    function setLanguageId(chooserId, languageId) {
        if (_choosers.get(chooserId) === undefined) {
            throw new Error(`Expected a valid  input element, '${chooserId}' is not i18n input field.`);
        }
        select(chooserId, languageId);
    }
});
