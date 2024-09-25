/**
 * Bootstraps WCF's JavaScript with additions for the ACP usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Core", "../Bootstrap", "./Ui/Page/Menu", "./Ui/Page/Menu/Main/Backend"], function (require, exports, tslib_1, Core, Bootstrap_1, UiPageMenu, Backend_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Core = tslib_1.__importStar(Core);
    UiPageMenu = tslib_1.__importStar(UiPageMenu);
    Backend_1 = tslib_1.__importDefault(Backend_1);
    /**
     * Bootstraps general modules and frontend exclusive ones.
     *
     * @param  {Object=}  options    bootstrap options
     */
    function setup(options) {
        options = Core.extend({
            bootstrap: {
                enableMobileMenu: true,
                pageMenuMainProvider: new Backend_1.default(),
            },
        }, options);
        (0, Bootstrap_1.setup)(options.bootstrap);
        UiPageMenu.init();
    }
});
