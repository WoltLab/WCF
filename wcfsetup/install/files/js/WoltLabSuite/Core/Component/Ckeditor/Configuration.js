/**
 * Helper class to construct the CKEditor configuration.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "../../Language"], function (require, exports, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.createConfigurationFor = void 0;
    class ConfigurationBuilder {
        #features;
        #divider = "|";
        #removePlugins = [];
        #toolbar = [];
        #toolbarGroups = {};
        constructor(features) {
            this.#features = features;
        }
        #setupUndo() {
            if (this.#features.undo) {
                this.#toolbar.push("undo", "redo");
            }
            else {
                this.#removePlugins.push("Undo");
            }
        }
        #setupBasicFormat() {
            this.#toolbar.push("bold", "italic");
        }
        #setupTextFormat() {
            const items = [];
            if (this.#features.heading) {
                items.push("heading");
            }
            else {
                this.#removePlugins.push("Heading");
            }
            if (this.#features.underline) {
                items.push("underline");
            }
            else {
                this.#removePlugins.push("Underline");
            }
            if (this.#features.strikethrough) {
                items.push("strikethrough");
            }
            else {
                this.#removePlugins.push("Strikethrough");
            }
            items.push(this.#divider);
            if (this.#features.mark) {
                items.push("highlight");
            }
            else {
                this.#removePlugins.push("Highlight");
            }
            if (this.#features.fontColor) {
                items.push("fontColor");
            }
            else {
                this.#removePlugins.push("FontColor");
            }
            if (this.#features.fontFamily) {
                items.push("fontFamily");
            }
            else {
                this.#removePlugins.push("FontFamily");
            }
            if (this.#features.fontSize) {
                items.push("fontSize");
            }
            else {
                this.#removePlugins.push("FontSize");
            }
            items.push(this.#divider);
            if (this.#features.subscript) {
                items.push("subscript");
            }
            else {
                this.#removePlugins.push("Subscript");
            }
            if (this.#features.superscript) {
                items.push("superscript");
            }
            else {
                this.#removePlugins.push("Superscript");
            }
            if (items.length !== 0) {
                items.push(this.#divider, "removeFormat");
            }
            if (items.length > 0) {
                this.#toolbar.push({
                    label: "woltlabToolbarGroup_format",
                    items,
                });
                this.#toolbarGroups["format"] = {
                    icon: "ellipsis;false",
                    label: (0, Language_1.getPhrase)("wcf.editor.button.group.format"),
                };
            }
        }
        #setupList() {
            if (this.#features.list) {
                this.#toolbar.push({
                    label: "woltlabToolbarGroup_list",
                    items: ["bulletedList", "numberedList", "outdent", "indent"],
                });
                this.#toolbarGroups["list"] = {
                    icon: "list;false",
                    label: (0, Language_1.getPhrase)("wcf.editor.button.group.list"),
                };
            }
            else {
                this.#removePlugins.push("List");
            }
        }
        #setupAlignment() {
            if (this.#features.alignment) {
                this.#toolbar.push("alignment");
            }
            else {
                this.#removePlugins.push("Alignment");
            }
        }
        #setupLink() {
            if (this.#features.link) {
                this.#toolbar.push("link");
            }
            else {
                this.#removePlugins.push("Link", "LinkImage");
            }
        }
        #setupImage() {
            if (this.#features.image) {
                this.#toolbar.push("insertImage");
                if (!this.#features.attachment) {
                    this.#removePlugins.push("ImageUpload", "ImageUploadUI", "WoltlabAttachment");
                }
            }
            else {
                this.#removePlugins.push("ImageInsertUI");
                if (this.#features.link) {
                    this.#removePlugins.push("LinkImage");
                }
            }
        }
        #setupCodeFormat() {
            if (this.#features.codeBlock) {
                this.#toolbar.push("codeBlock");
            }
            else {
                this.#removePlugins.push("CodeBlock", "WoltlabCodeBlock");
            }
            if (this.#features.code) {
                this.#toolbar.push("code");
            }
            else {
                this.#removePlugins.push("Code", "WoltlabCode");
            }
        }
        #setupBlocks() {
            if (this.#features.table) {
                this.#toolbar.push("insertTable");
            }
            else {
                this.#removePlugins.push("Table", "TableToolbar");
            }
            if (this.#features.quoteBlock) {
                this.#toolbar.push("blockQuote");
            }
            else {
                this.#removePlugins.push("BlockQuote", "WoltlabBlockQuote");
            }
            if (this.#features.spoiler) {
                this.#toolbar.push("spoiler");
            }
            else {
                this.#removePlugins.push("WoltlabSpoiler");
            }
            if (this.#features.html) {
                this.#toolbar.push("htmlEmbed");
            }
            else {
                this.#removePlugins.push("HtmlEmbed", "WoltlabHtmlEmbed");
            }
        }
        #insertDivider() {
            this.#toolbar.push(this.#divider);
        }
        #setupMedia() {
            if (!this.#features.media) {
                this.#removePlugins.push("WoltlabMedia");
            }
        }
        #setupMention() {
            if (!this.#features.mention) {
                this.#removePlugins.push("Mention", "WoltlabMention");
            }
        }
        #getToolbar() {
            let allowDivider = false;
            const toolbar = this.#toolbar.filter((item) => {
                if (typeof item === "string" && item === this.#divider) {
                    if (!allowDivider) {
                        return false;
                    }
                    allowDivider = false;
                    return true;
                }
                allowDivider = true;
                return true;
            });
            return toolbar;
        }
        build() {
            if (this.#removePlugins.length > 0 || this.#toolbar.length > 0) {
                throw new Error("Cannot build the configuration twice.");
            }
            this.#setupUndo();
            this.#insertDivider();
            this.#insertDivider();
            this.#setupBasicFormat();
            this.#setupLink();
            this.#setupTextFormat();
            this.#insertDivider();
            this.#setupList();
            this.#setupAlignment();
            this.#insertDivider();
            this.#setupImage();
            this.#setupCodeFormat();
            this.#setupBlocks();
            this.#insertDivider();
            this.#setupMedia();
            this.#setupMention();
        }
        toConfig() {
            const language = Object.keys(window.CKEDITOR_TRANSLATIONS).find((language) => language !== "en");
            const key = language ? language : "en";
            const { dictionary } = window.CKEDITOR_TRANSLATIONS[key];
            dictionary["Author"] = (0, Language_1.getPhrase)("wcf.ckeditor.quote.author");
            dictionary["Filename"] = (0, Language_1.getPhrase)("wcf.ckeditor.code.fileName");
            dictionary["Line number"] = (0, Language_1.getPhrase)("wcf.ckeditor.code.lineNumber");
            dictionary["Quote"] = (0, Language_1.getPhrase)("wcf.ckeditor.quote");
            dictionary["Quote from %0"] = (0, Language_1.getPhrase)("wcf.ckeditor.quoteFrom");
            dictionary["Spoiler"] = (0, Language_1.getPhrase)("wcf.editor.button.spoiler");
            // TODO: The typings are both incompleted and outdated.
            return {
                alignment: {
                    options: [
                        { name: "left", className: "text-left" },
                        { name: "center", className: "text-center" },
                        { name: "right", className: "text-right" },
                        { name: "justify", className: "text-justify" },
                    ],
                },
                highlight: {
                    options: [
                        {
                            model: "markerWarning",
                            class: "marker-warning",
                            title: (0, Language_1.getPhrase)("wcf.ckeditor.marker.warning"),
                            color: "var(--marker-warning)",
                            type: "marker",
                        },
                        {
                            model: "markerError",
                            class: "marker-error",
                            title: (0, Language_1.getPhrase)("wcf.ckeditor.marker.error"),
                            color: "var(--marker-error)",
                            type: "marker",
                        },
                        {
                            model: "markerInfo",
                            class: "marker-info",
                            title: (0, Language_1.getPhrase)("wcf.ckeditor.marker.info"),
                            color: "var(--marker-info)",
                            type: "marker",
                        },
                        {
                            model: "markerSuccess",
                            class: "marker-success",
                            title: (0, Language_1.getPhrase)("wcf.ckeditor.marker.success"),
                            color: "var(--marker-success)",
                            type: "marker",
                        },
                    ],
                },
                language,
                link: {
                    defaultProtocol: "https://",
                },
                removePlugins: this.#removePlugins,
                fontFamily: {
                    options: [
                        "default",
                        "Arial, Helvetica, sans-serif",
                        "Comic Sans MS, Marker Felt, cursive",
                        "Consolas, Courier New, Courier, monospace",
                        "Georgia, serif",
                        "Lucida Sans Unicode, Lucida Grande, sans-serif",
                        "Tahoma, Geneva, sans-serif",
                        "Times New Roman, Times, serif",
                        'Trebuchet MS", Helvetica, sans-serif',
                        "Verdana, Geneva, sans-serif",
                    ],
                },
                fontSize: {
                    options: [11, 13, "default", 24, 32, 48],
                },
                toolbar: this.#getToolbar(),
                ui: {
                    poweredBy: {
                        label: null,
                    },
                    viewportOffset: {
                        top: 50,
                    },
                },
                woltlabToolbarGroup: this.#toolbarGroups,
                extraPlugins: [],
            };
        }
    }
    function createConfigurationFor(features) {
        const configuration = new ConfigurationBuilder(features);
        configuration.build();
        return configuration.toConfig();
    }
    exports.createConfigurationFor = createConfigurationFor;
});
