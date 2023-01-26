define(["require", "exports", "./Ckeditor/Mention", "./Ckeditor/Quote", "./Ckeditor/Attachment", "../Dom/Util", "./Ckeditor/Media"], function (require, exports, Mention_1, Quote_1, Attachment_1, Util_1, Media_1) {
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
        setHtml(html) {
            this.#editor.data.set(html);
        }
        get features() {
            return this.#features;
        }
        get sourceElement() {
            return this.#editor.sourceElement;
        }
    }
    function enableAttachments(element, configuration) {
        // TODO: The typings do not include our custom plugins yet.
        configuration.woltlabUpload = {
            upload: (file, abortController) => (0, Attachment_1.uploadAttachment)(element.id, file, abortController),
        };
    }
    function enableMedia(element, configuration) {
        // TODO: The typings do not include our custom plugins yet.
        configuration.woltlabUpload = {
            upload: (file, abortController) => (0, Media_1.uploadMedia)(element.id, file, abortController),
        };
    }
    function enableMentions(configuration) {
        configuration.mention = {
            feeds: [
                {
                    feed: (query) => {
                        // TODO: The typings are outdated, cast the result to `any`.
                        return (0, Mention_1.getPossibleMentions)(query);
                    },
                    itemRenderer: (item) => {
                        // TODO: This is ugly.
                        return (0, Util_1.createFragmentFromHtml)(`
            <span>${item.icon} ${item.text}</span>
          `).firstElementChild;
                    },
                    marker: "@",
                    minimumCharacters: 3,
                },
            ],
        };
    }
    async function setupCkeditor(element, configuration, features) {
        let editor = instances.get(element);
        if (editor === undefined) {
            if (features.attachment) {
                enableAttachments(element, configuration);
            }
            else if (features.media) {
                enableMedia(element, configuration);
            }
            if (features.mention) {
                enableMentions(configuration);
            }
            const cke = await window.CKEditor5.create(element, configuration);
            editor = new Ckeditor(cke, features);
            if (features.attachment || features.media) {
                editor.sourceElement.addEventListener("woltlabUpload", (event) => {
                    event.preventDefault();
                    for (const file of event.detail.files) {
                        if (file === null) {
                            continue;
                        }
                        if (features.attachment) {
                            void (0, Attachment_1.uploadAttachment)(element.id, file);
                        }
                        else if (features.media) {
                            void (0, Media_1.uploadMedia)(element.id, file);
                        }
                    }
                });
            }
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
