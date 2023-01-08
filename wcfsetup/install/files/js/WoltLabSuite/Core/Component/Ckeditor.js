define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getCkeditorById = exports.getCkeditor = exports.setupCkeditor = void 0;
    const instances = new WeakMap();
    class Ckeditor {
        #editor;
        constructor(editor) {
            this.#editor = editor;
        }
        focus() {
            this.#editor.editing.view.focus();
        }
        getHtml() {
            return this.#editor.data.get();
        }
        setHtml(html) {
            this.#editor.data.set(html);
        }
    }
    async function setupCkeditor(element) {
        let editor = instances.get(element);
        if (editor === undefined) {
            const cke = await window.CKEditor5.create(element);
            editor = new Ckeditor(cke);
            instances.set(element, editor);
        }
        return editor;
    }
    exports.setupCkeditor = setupCkeditor;
    function getCkeditor(element) {
        return instances.get(element);
    }
    exports.getCkeditor = getCkeditor;
    function getCkeditorById(id) {
        const element = document.getElementById(id);
        if (element === null) {
            throw new Error(`Unable to find an element with the id '${id}'.`);
        }
        return getCkeditor(element);
    }
    exports.getCkeditorById = getCkeditorById;
});
