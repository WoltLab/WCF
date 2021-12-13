define(["require", "exports", "tslib", "focus-trap", "../../Screen", "../../CloseOverlay", "../../../Dom/Util"], function (require, exports, tslib_1, focus_trap_1, Screen_1, CloseOverlay_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.PageMenuContainer = void 0;
    CloseOverlay_1 = (0, tslib_1.__importDefault)(CloseOverlay_1);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    class PageMenuContainer {
        constructor(provider) {
            this.container = document.createElement("div");
            this.content = document.createElement("div");
            this.focusTrap = undefined;
            this.provider = provider;
            const menuId = Util_1.default.identify(this.provider.getMenuButton());
            CloseOverlay_1.default.add(`WoltLabSuite/Core/Ui/PageMenu/Container-${menuId}`, () => {
                if (!this.container.hidden) {
                    this.close();
                }
            });
        }
        open() {
            CloseOverlay_1.default.execute();
            this.buildElements();
            this.content.innerHTML = "";
            this.content.append(this.provider.getContent());
            this.provider.getMenuButton().setAttribute("aria-expanded", "true");
            (0, Screen_1.pageOverlayOpen)();
            (0, Screen_1.scrollDisable)();
            this.container.hidden = false;
            this.provider.refresh();
            this.getFocusTrap().activate();
        }
        close() {
            this.provider.getMenuButton().setAttribute("aria-expanded", "false");
            (0, Screen_1.pageOverlayClose)();
            (0, Screen_1.scrollEnable)();
            this.container.hidden = true;
            this.getFocusTrap().deactivate();
        }
        toggle() {
            if (this.container.hidden) {
                this.open();
            }
            else {
                this.close();
            }
        }
        getContent() {
            return this.content;
        }
        buildElements() {
            if (this.container.classList.contains("pageMenuContainer")) {
                return;
            }
            this.container.classList.add("pageMenuContainer");
            this.container.hidden = true;
            this.container.addEventListener("click", (event) => {
                if (event.target === this.container) {
                    this.close();
                }
            });
            this.content.classList.add("pageMenuContent");
            this.content.addEventListener("click", (event) => {
                event.stopPropagation();
            });
            this.container.append(this.content);
            document.body.append(this.container);
        }
        getFocusTrap() {
            if (this.focusTrap === undefined) {
                this.focusTrap = (0, focus_trap_1.createFocusTrap)(this.content, {
                    allowOutsideClick: true,
                });
            }
            return this.focusTrap;
        }
    }
    exports.PageMenuContainer = PageMenuContainer;
    exports.default = PageMenuContainer;
});
