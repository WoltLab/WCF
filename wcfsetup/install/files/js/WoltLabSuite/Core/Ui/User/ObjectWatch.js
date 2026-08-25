/**
 * Handles the object watch button.
 *
 * @author	Marcel Werk
 * @copyright	2001-2022 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated 6.3 Use `WoltLabSuite/Core/Component/User/ObjectWatch` instead.
 */
define(["require", "exports", "WoltLabSuite/Core/Component/User/ObjectWatch"], function (require, exports, ObjectWatch_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setup() {
        (0, ObjectWatch_1.setup)();
    }
});
