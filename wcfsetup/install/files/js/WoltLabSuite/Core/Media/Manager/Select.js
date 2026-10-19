/**
 * Provides the media manager dialog for selecting media for input elements.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 * @deprecated 6.3 Use `WoltLabSuite/Core/Component/Media/Select` instead.
 */
define(["require", "exports", "../../Component/Media/Select"], function (require, exports, Select_1) {
    "use strict";
    class MediaManagerSelect {
        constructor(options) {
            document.querySelectorAll(`.${options.buttonClass || "jsMediaSelectButton"}`).forEach((button) => {
                if (button.dataset.store) {
                    (0, Select_1.setup)(button, {
                        imagesOnly: options.imagesOnly,
                        title: options.dialogTitle,
                    });
                }
            });
        }
    }
    return MediaManagerSelect;
});
