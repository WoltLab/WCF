var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
define(["require", "exports", "./Ckeditor/Mention", "./Ckeditor/Quote", "./Ckeditor/Attachment", "./Ckeditor/Media", "./Ckeditor/Autosave"], function (require, exports, Mention_1, Quote_1, Attachment_1, Media_1, Autosave_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getCkeditorById = exports.getCkeditor = exports.setupCkeditor = void 0;
    const instances = new WeakMap();
    class Ckeditor {
        #editor;
        #features;
        constructor(editor, features) {
            this.#editor = editor;
            Object.freeze(features);
            this.#features = features;
            (0, Quote_1.setup)(this);
        }
        destroy() {
            return this.#editor.destroy();
        }
        discardDraft() {
            if (this.#features.autosave) {
                (0, Autosave_1.deleteDraft)(this.#features.autosave);
            }
        }
        focus() {
            this.#editor.editing.view.focus();
        }
        getHtml() {
            return this.#editor.data.get();
        }
        insertHtml(html) {
            const viewFragment = this.#editor.data.processor.toView(html);
            const modelFragment = this.#editor.data.toModel(viewFragment);
            this.#editor.model.insertContent(modelFragment);
        }
        insertText(text) {
            const div = document.createElement("div");
            div.textContent = text;
            this.insertHtml(div.innerHTML);
        }
        isVisible() {
            return this.#editor.ui.element.clientWidth !== 0;
        }
        setHtml(html) {
            this.#editor.data.set(html);
        }
        removeAll(model, attributes) {
            this.#editor.model.change((writer) => {
                const elements = findModelForRemoval(this.#editor.model.document.getRoot(), model, attributes);
                for (const element of elements) {
                    writer.remove(element);
                }
            });
        }
        reset() {
            this.setHtml("");
            this.sourceElement.dispatchEvent(new CustomEvent("ckeditor5:reset"));
        }
        get element() {
            return this.#editor.ui.element;
        }
        get features() {
            return this.#features;
        }
        get sourceElement() {
            return this.#editor.sourceElement;
        }
    }
    function* findModelForRemoval(element, model, attributes) {
        if (element.is("element", model)) {
            let isMatch = true;
            Object.entries(attributes).forEach(([key, value]) => {
                if (!element.hasAttribute(key)) {
                    isMatch = false;
                }
                else if (element.getAttribute(key) !== value)
                    isMatch = false;
            });
            if (isMatch) {
                yield element;
                return;
            }
        }
        for (const child of element.getChildren()) {
            if (child.is("element")) {
                yield* findModelForRemoval(child, model, attributes);
            }
        }
    }
    function initializeFeatures(element, configuration, features) {
        if (features.attachment) {
            (0, Attachment_1.initializeAttachment)(element, configuration);
        }
        else if (features.media) {
            (0, Media_1.initializeMedia)(element, configuration);
        }
        if (features.mention) {
            (0, Mention_1.initializeMention)(configuration);
        }
        if (features.autosave !== "") {
            (0, Autosave_1.initializeAutosave)(features.autosave, configuration);
        }
        const bbcodes = configuration.woltlabBbcode;
        for (const { name } of bbcodes) {
            configuration.toolbar.push(`woltlabBbcode_${name}`);
        }
    }
    async function setupCkeditor(element, configuration, features) {
        if (instances.has(element)) {
            throw new TypeError(`Cannot initialize the editor for '${element.id}' twice.`);
        }
        initializeFeatures(element, configuration, features);
        const cke = await window.CKEditor5.create(element, configuration);
        const editor = new Ckeditor(cke, features);
        if (features.attachment) {
            (0, Attachment_1.setupInsertAttachment)(editor);
            (0, Attachment_1.setupRemoveAttachment)(editor);
        }
        if (features.autosave) {
            (0, Autosave_1.setupRestoreDraft)(cke, features.autosave);
        }
        if (features.media) {
            void new Promise((resolve_1, reject_1) => { require(["../Media/Manager/Editor"], resolve_1, reject_1); }).then(__importStar).then(({ MediaManagerEditor }) => {
                new MediaManagerEditor({
                    ckeditor: editor,
                });
            });
        }
        instances.set(element, editor);
        const event = new CustomEvent("ckeditor5:ready", {
            detail: editor,
        });
        element.dispatchEvent(event);
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
