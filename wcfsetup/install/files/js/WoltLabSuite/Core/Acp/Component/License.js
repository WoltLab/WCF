define(["require", "exports", "tslib", "../Ui/Package/PrepareInstallation"], function (require, exports, tslib_1, PrepareInstallation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    PrepareInstallation_1 = tslib_1.__importDefault(PrepareInstallation_1);
    function installPackage(button) {
        const installation = new PrepareInstallation_1.default();
        installation.start(button.dataset.package, button.dataset.packageVersion);
    }
    function setup() {
        document.querySelectorAll(".jsInstallPackage").forEach((button) => {
            button.addEventListener("click", () => {
                installPackage(button);
            });
        });
    }
    exports.setup = setup;
});
