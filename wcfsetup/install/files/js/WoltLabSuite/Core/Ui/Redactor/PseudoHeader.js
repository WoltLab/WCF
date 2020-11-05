/**
 * Helper class to deal with clickable block headers using the pseudo
 * `::before` element.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/PseudoHeader
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getHeight = void 0;
    /**
     * Returns the height within a click should be treated as a click
     * within the block element's title. This method expects that the
     * `::before` element is used and that removing the attribute
     * `data-title` does cause the title to collapse.
     */
    function getHeight(element) {
        let height = ~~window.getComputedStyle(element).paddingTop.replace(/px$/, "");
        const styles = window.getComputedStyle(element, "::before");
        height += ~~styles.paddingTop.replace(/px$/, "");
        height += ~~styles.paddingBottom.replace(/px$/, "");
        let titleHeight = ~~styles.height.replace(/px$/, "");
        if (titleHeight === 0) {
            // firefox returns garbage for pseudo element height
            // https://bugzilla.mozilla.org/show_bug.cgi?id=925694
            titleHeight = element.scrollHeight;
            element.classList.add("redactorCalcHeight");
            titleHeight -= element.scrollHeight;
            element.classList.remove("redactorCalcHeight");
        }
        height += titleHeight;
        return height;
    }
    exports.getHeight = getHeight;
});
