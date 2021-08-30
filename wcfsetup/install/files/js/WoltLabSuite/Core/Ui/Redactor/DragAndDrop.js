/**
 * Drag and Drop file uploads.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/DragAndDrop
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Event/Handler", "../../Language"], function (require, exports, tslib_1, EventHandler, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    EventHandler = (0, tslib_1.__importStar)(EventHandler);
    Language = (0, tslib_1.__importStar)(Language);
    let _didInit = false;
    const _dragArea = new Map();
    let _isDragging = false;
    let _isFile = false;
    let _timerLeave = null;
    /**
     * Handles items dragged into the browser window.
     */
    function _dragOver(event) {
        event.preventDefault();
        if (!event.dataTransfer || !event.dataTransfer.types) {
            return;
        }
        const isFirefox = Object.keys(event.dataTransfer).some((property) => property.startsWith("moz"));
        // IE and WebKit set 'Files', Firefox sets 'application/x-moz-file' for files being dragged
        // and Safari just provides 'Files' along with a huge list of garbage
        _isFile = false;
        if (isFirefox) {
            // Firefox sets the 'Files' type even if the user is just dragging an on-page element
            if (event.dataTransfer.types[0] === "application/x-moz-file") {
                _isFile = true;
            }
        }
        else {
            _isFile = event.dataTransfer.types.some((type) => type === "Files");
        }
        if (!_isFile) {
            // user is just dragging around some garbage, ignore it
            return;
        }
        if (_isDragging) {
            // user is still dragging the file around
            return;
        }
        _isDragging = true;
        _dragArea.forEach((data, uuid) => {
            const editor = data.editor.$editor[0];
            if (!editor.parentElement) {
                _dragArea.delete(uuid);
                return;
            }
            let element = data.element;
            if (element === null) {
                element = document.createElement("div");
                element.className = "redactorDropArea";
                element.dataset.elementId = data.editor.$element[0].id;
                element.dataset.dropHere = Language.get("wcf.attachment.dragAndDrop.dropHere");
                element.dataset.dropNow = Language.get("wcf.attachment.dragAndDrop.dropNow");
                element.addEventListener("dragover", () => {
                    element.classList.add("active");
                });
                element.addEventListener("dragleave", () => {
                    element.classList.remove("active");
                });
                element.addEventListener("drop", (ev) => drop(ev));
                data.element = element;
            }
            editor.parentElement.insertBefore(element, editor);
            element.style.setProperty("top", `${editor.offsetTop}px`, "");
        });
    }
    /**
     * Handles items dropped onto an editor's drop area
     */
    function drop(event) {
        if (!_isFile) {
            return;
        }
        if (!event.dataTransfer || !event.dataTransfer.files.length) {
            return;
        }
        event.preventDefault();
        const target = event.currentTarget;
        const elementId = target.dataset.elementId;
        Array.from(event.dataTransfer.files).forEach((file) => {
            const eventData = { file };
            EventHandler.fire("com.woltlab.wcf.redactor2", `dragAndDrop_${elementId}`, eventData);
        });
        // this will reset all drop areas
        dragLeave();
    }
    /**
     * Invoked whenever the item is no longer dragged or was dropped.
     *
     * @protected
     */
    function dragLeave() {
        if (!_isDragging || !_isFile) {
            return;
        }
        if (_timerLeave !== null) {
            window.clearTimeout(_timerLeave);
        }
        _timerLeave = window.setTimeout(() => {
            if (!_isDragging) {
                _dragArea.forEach((data) => {
                    if (data.element && data.element.parentElement) {
                        data.element.classList.remove("active");
                        data.element.remove();
                    }
                });
            }
            _timerLeave = null;
        }, 100);
        _isDragging = false;
    }
    /**
     * Handles the global drop event.
     */
    function globalDrop(event) {
        const target = event.target;
        if (target.closest(".redactor-layer") === null) {
            const eventData = { cancelDrop: true, event: event };
            _dragArea.forEach((data) => {
                EventHandler.fire("com.woltlab.wcf.redactor2", `dragAndDrop_globalDrop_${data.editor.$element[0].id}`, eventData);
            });
            if (eventData.cancelDrop) {
                event.preventDefault();
            }
        }
        dragLeave();
    }
    /**
     * Binds listeners to global events.
     *
     * @protected
     */
    function setup() {
        // discard garbage event
        window.addEventListener("dragend", (ev) => ev.preventDefault());
        window.addEventListener("dragover", (ev) => _dragOver(ev));
        window.addEventListener("dragleave", () => dragLeave());
        window.addEventListener("drop", (ev) => globalDrop(ev));
        _didInit = true;
    }
    /**
     * Initializes drag and drop support for provided editor instance.
     */
    function init(editor) {
        if (!_didInit) {
            setup();
        }
        _dragArea.set(editor.uuid, {
            editor: editor,
            element: null,
        });
    }
    exports.init = init;
});
