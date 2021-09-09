/**
 * Provides the trophy icon designer.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Trophy/Badge
 */
define(["require", "exports", "tslib", "../../../Language", "../../../Ui/Dialog", "../../../Ui/Style/FontAwesome"], function (require, exports, tslib_1, Language, Dialog_1, UiStyleFontAwesome) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Language = (0, tslib_1.__importStar)(Language);
    Dialog_1 = (0, tslib_1.__importDefault)(Dialog_1);
    UiStyleFontAwesome = (0, tslib_1.__importStar)(UiStyleFontAwesome);
    /**
     * @exports     WoltLabSuite/Core/Acp/Ui/Trophy/Badge
     */
    class AcpUiTrophyBadge {
        /**
         * Initializes the badge designer.
         */
        constructor() {
            this.badgeColor = undefined;
            this.dialogContent = undefined;
            this.icon = undefined;
            this.iconColor = undefined;
            const iconContainer = document.getElementById("badgeContainer");
            const button = iconContainer.querySelector(".button");
            button.addEventListener("click", (ev) => this.click(ev));
            this.iconNameInput = iconContainer.querySelector('input[name="iconName"]');
            this.iconColorInput = iconContainer.querySelector('input[name="iconColor"]');
            this.badgeColorInput = iconContainer.querySelector('input[name="badgeColor"]');
        }
        /**
         * Opens the icon designer.
         */
        click(event) {
            event.preventDefault();
            Dialog_1.default.open(this);
        }
        /**
         * Sets the icon name.
         */
        setIcon(iconName) {
            this.icon.textContent = iconName;
            this.renderIcon();
        }
        /**
         * Sets the icon color, can be either a string or an object holding the
         * individual r, g, b and a values.
         */
        setIconColor(color) {
            if (typeof color !== "string") {
                color = `rgba(${color.r}, ${color.g}, ${color.b}, ${color.a})`;
            }
            this.iconColor.dataset.color = color;
            this.iconColor.style.setProperty("background-color", color, "");
            this.renderIcon();
        }
        /**
         * Sets the badge color, can be either a string or an object holding the
         * individual r, g, b and a values.
         */
        setBadgeColor(color) {
            if (typeof color !== "string") {
                color = `rgba(${color.r}, ${color.g}, ${color.b}, ${color.a})`;
            }
            this.badgeColor.dataset.color = color;
            this.badgeColor.style.setProperty("background-color", color, "");
            this.renderIcon();
        }
        /**
         * Renders the custom icon preview.
         */
        renderIcon() {
            const iconColor = this.iconColor.style.getPropertyValue("background-color");
            const badgeColor = this.badgeColor.style.getPropertyValue("background-color");
            const icon = this.dialogContent.querySelector(".jsTrophyIcon");
            // set icon
            icon.className = icon.className.replace(/\b(fa-[a-z0-9-]+)\b/, "");
            icon.classList.add(`fa-${this.icon.textContent}`);
            icon.style.setProperty("color", iconColor, "");
            icon.style.setProperty("background-color", badgeColor, "");
        }
        /**
         * Saves the custom icon design.
         */
        save(event) {
            event.preventDefault();
            const iconColor = this.iconColor.style.getPropertyValue("background-color");
            const badgeColor = this.badgeColor.style.getPropertyValue("background-color");
            const icon = this.icon.textContent;
            this.iconNameInput.value = icon;
            this.badgeColorInput.value = badgeColor;
            this.iconColorInput.value = iconColor;
            const badgeContainer = document.getElementById("badgeContainer");
            const previewIcon = badgeContainer.querySelector(".jsTrophyIcon");
            // set icon
            previewIcon.className = previewIcon.className.replace(/\b(fa-[a-z0-9-]+)\b/, "");
            previewIcon.classList.add("fa-" + icon);
            previewIcon.style.setProperty("color", iconColor, "");
            previewIcon.style.setProperty("background-color", badgeColor, "");
            Dialog_1.default.close(this);
        }
        _dialogSetup() {
            return {
                id: "trophyIconEditor",
                options: {
                    onSetup: (context) => {
                        this.dialogContent = context;
                        this.iconColor = context.querySelector("#jsIconColorContainer .colorBoxValue");
                        this.badgeColor = context.querySelector("#jsBadgeColorContainer .colorBoxValue");
                        this.icon = context.querySelector(".jsTrophyIconName");
                        const buttonIconPicker = context.querySelector(".jsTrophyIconName + .button");
                        buttonIconPicker.addEventListener("click", (event) => {
                            event.preventDefault();
                            UiStyleFontAwesome.open((iconName) => this.setIcon(iconName));
                        });
                        const iconColorContainer = document.getElementById("jsIconColorContainer");
                        const iconColorPicker = iconColorContainer.querySelector(".jsButtonIconColorPicker");
                        iconColorPicker.addEventListener("click", (event) => {
                            event.preventDefault();
                            const picker = iconColorContainer.querySelector(".jsColorPicker");
                            picker.click();
                        });
                        const badgeColorContainer = document.getElementById("jsBadgeColorContainer");
                        const badgeColorPicker = badgeColorContainer.querySelector(".jsButtonBadgeColorPicker");
                        badgeColorPicker.addEventListener("click", (event) => {
                            event.preventDefault();
                            const picker = badgeColorContainer.querySelector(".jsColorPicker");
                            picker.click();
                        });
                        const colorPicker = new window.WCF.ColorPicker(".jsColorPicker");
                        colorPicker.setCallbackSubmit(() => this.renderIcon());
                        const submitButton = context.querySelector(".formSubmit > .buttonPrimary");
                        submitButton.addEventListener("click", (ev) => this.save(ev));
                    },
                    onShow: () => {
                        this.setIcon(this.iconNameInput.value);
                        this.setIconColor(this.iconColorInput.value);
                        this.setBadgeColor(this.badgeColorInput.value);
                    },
                    title: Language.get("wcf.acp.trophy.badge.edit"),
                },
            };
        }
    }
    let acpUiTrophyBadge;
    /**
     * Initializes the badge designer.
     */
    function init() {
        if (!acpUiTrophyBadge) {
            acpUiTrophyBadge = new AcpUiTrophyBadge();
        }
    }
    exports.init = init;
});
