/**
 * Helper class to construct the CKEditor configuration.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
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
        #setupHeading() {
            if (this.#features.heading) {
                this.#toolbar.push("heading");
            }
            else {
                this.#removePlugins.push("Heading");
            }
        }
        #setupBasicFormat() {
            this.#toolbar.push("bold", "italic");
        }
        #setupTextFormat() {
            const items = [];
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
            if (this.#features.code) {
                items.push("code");
            }
            else {
                this.#removePlugins.push("Code");
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
                    items: ["bulletedList", "numberedList"],
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
            }
            else {
                this.#removePlugins.push("Image", "ImageInsertUI", "ImageToolbar", "ImageStyle", "ImageUpload", "ImageUploadUI");
                if (this.#features.link) {
                    this.#removePlugins.push("LinkImage");
                }
                // Disable built-in plugins that rely on the image plugin.
                this.#removePlugins.push("WoltlabAttachment");
                this.#removePlugins.push("WoltlabSmiley");
            }
        }
        #setupBlocks() {
            const items = [];
            if (this.#features.table) {
                items.push("insertTable");
            }
            else {
                this.#removePlugins.push("Table", "TableToolbar");
            }
            if (this.#features.quoteBlock) {
                items.push("blockQuote");
            }
            else {
                this.#removePlugins.push("BlockQuote", "WoltlabBlockQuote");
            }
            if (this.#features.codeBlock) {
                items.push("codeBlock");
            }
            else {
                this.#removePlugins.push("CodeBlock", "WoltlabCodeBlock");
            }
            if (this.#features.spoiler) {
                items.push("spoiler");
            }
            else {
                this.#removePlugins.push("WoltlabSpoiler");
            }
            if (this.#features.html) {
                items.push("htmlEmbed");
            }
            else {
                this.#removePlugins.push("HtmlEmbed");
            }
            if (this.#features.media) {
                items.push("woltlabBbcode_media");
            }
            else {
                this.#removePlugins.push("WoltlabMedia");
            }
            if (items.length > 0) {
                this.#toolbar.push({
                    label: (0, Language_1.getPhrase)("wcf.editor.button.group.block"),
                    icon: "plus",
                    items,
                });
            }
        }
        #insertDivider() {
            this.#toolbar.push(this.#divider);
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
            this.#setupHeading();
            this.#insertDivider();
            this.#setupBasicFormat();
            this.#setupTextFormat();
            this.#insertDivider();
            this.#setupList();
            this.#setupAlignment();
            this.#setupLink();
            this.#setupImage();
            this.#setupBlocks();
            this.#insertDivider();
        }
        toConfig() {
            // TODO: The typings are both incompleted and outdated.
            return {
                removePlugins: this.#removePlugins,
                toolbar: this.#getToolbar(),
                woltlabToolbarGroup: this.#toolbarGroups,
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
