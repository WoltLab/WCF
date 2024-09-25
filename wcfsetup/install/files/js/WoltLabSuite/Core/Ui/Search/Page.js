define(["require", "exports", "tslib", "../../Core", "../../Dom/Util", "../Dropdown/Simple", "../Screen", "./Input"], function (require, exports, tslib_1, Core, Util_1, Simple_1, UiScreen, Input_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    UiScreen = tslib_1.__importStar(UiScreen);
    Input_1 = tslib_1.__importDefault(Input_1);
    const parameters = new Map();
    function click(event) {
        event.preventDefault();
        const target = event.currentTarget;
        const objectType = target.dataset.objectType;
        const extendedLink = target.dataset.extendedLink;
        if (extendedLink) {
            const link = document.querySelector(".pageHeaderSearchExtendedLink");
            link.href = extendedLink;
        }
        parameters.clear();
        try {
            const data = JSON.parse(target.dataset.parameters || "");
            if (Core.isPlainObject(data)) {
                Object.keys(data).forEach((key) => {
                    parameters.set(key, data[key]);
                });
            }
        }
        catch (e) {
            // Ignore JSON parsing failure.
        }
        if (objectType && objectType !== "everywhere") {
            parameters.set("type", objectType);
        }
        // update label
        const inputContainer = document.getElementById("pageHeaderSearchInputContainer");
        const button = inputContainer.querySelector(".pageHeaderSearchType > .button > .pageHeaderSearchTypeLabel");
        button.textContent = target.textContent;
    }
    function init(objectType) {
        const searchInput = document.getElementById("pageHeaderSearchInput");
        const form = searchInput.form;
        new Input_1.default(searchInput, {
            ajax: {
                className: "wcf\\data\\search\\keyword\\SearchKeywordAction",
            },
            autoFocus: false,
            callbackDropdownInit(dropdownMenu) {
                dropdownMenu.classList.add("dropdownMenuPageSearch");
                if (UiScreen.is("screen-lg")) {
                    dropdownMenu.dataset.dropdownAlignmentHorizontal = "right";
                    const minWidth = searchInput.clientWidth;
                    dropdownMenu.style.setProperty("min-width", `${minWidth}px`, "");
                    // calculate offset to ignore the width caused by the submit button
                    const parent = searchInput.parentElement;
                    const offsetRight = Util_1.default.offset(parent).left + parent.clientWidth - (Util_1.default.offset(searchInput).left + minWidth);
                    const offsetTop = Util_1.default.styleAsInt(window.getComputedStyle(parent), "padding-bottom");
                    dropdownMenu.style.setProperty("transform", `translateX(-${Math.ceil(offsetRight)}px) translateY(-${offsetTop}px)`, "");
                }
            },
            callbackSelect() {
                setTimeout(() => {
                    // Do not use `form.submit()`, it does not trigger the `submit` event.
                    // See https://developer.mozilla.org/en-US/docs/Web/API/HTMLFormElement/submit
                    submit(form, searchInput);
                }, 1);
                return true;
            },
            suppressErrors: true,
        });
        const searchType = document.querySelector(".pageHeaderSearchType");
        const dropdownMenu = Simple_1.default.getDropdownMenu(Util_1.default.identify(searchType));
        dropdownMenu.querySelectorAll("a[data-object-type]").forEach((link) => {
            link.addEventListener("click", click);
        });
        // trigger click on init
        const link = dropdownMenu.querySelector('a[data-object-type="' + objectType + '"]');
        link.click();
        form.addEventListener("submit", (event) => {
            event.preventDefault();
            submit(form, searchInput);
        });
    }
    function submit(form, input) {
        const url = new URL(form.action);
        url.search += url.search !== "" ? "&" : "?";
        url.search += new URLSearchParams([["q", input.value.trim()], ...Array.from(parameters)]).toString();
        window.location.href = url.toString();
    }
});
