/**
 * Helper class to deal with clickable block headers using the pseudo
 * `::before` element.
 *
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/PseudoHeader
 */
define([], function () {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            getHeight: function () { }
        };
        return Fake;
    }
    return {
        /**
         * Returns the height within a click should be treated as a click
         * within the block element's title. This method expects that the
         * `::before` element is used and that removing the attribute
         * `data-title` does cause the title to collapse.
         *
         * @param       {Element}       element         block element
         * @return      {int}           clickable height spanning from the top border down to the bottom of the title
         */
        getHeight: function (element) {
            var height = ~~window.getComputedStyle(element).paddingTop.replace(/px$/, '');
            var styles = window.getComputedStyle(element, '::before');
            height += ~~styles.paddingTop.replace(/px$/, '');
            height += ~~styles.paddingBottom.replace(/px$/, '');
            var titleHeight = ~~styles.height.replace(/px$/, '');
            if (titleHeight === 0) {
                // firefox returns garbage for pseudo element height
                // https://bugzilla.mozilla.org/show_bug.cgi?id=925694
                titleHeight = element.scrollHeight;
                element.classList.add('redactorCalcHeight');
                titleHeight -= element.scrollHeight;
                element.classList.remove('redactorCalcHeight');
            }
            height += titleHeight;
            return height;
        }
    };
});
