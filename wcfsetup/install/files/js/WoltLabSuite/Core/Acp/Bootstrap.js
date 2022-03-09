/**
 * Bootstraps WCF's JavaScript with additions for the ACP usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Bootstrap
 */
define(["require", "exports", "tslib", "../Core", "../Bootstrap", "./Ui/Page/Menu"], function (require, exports, tslib_1, Core, Bootstrap_1, UiPageMenu) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Core = tslib_1.__importStar(Core);
    UiPageMenu = tslib_1.__importStar(UiPageMenu);
    /**
     * Bootstraps general modules and frontend exclusive ones.
     *
     * @param  {Object=}  options    bootstrap options
     */
    function setup(options) {
        options = Core.extend({
            bootstrap: {
                enableMobileMenu: true,
            },
        }, options);
        (0, Bootstrap_1.setup)(options.bootstrap);
        UiPageMenu.init();
    }
    exports.setup = setup;
});
