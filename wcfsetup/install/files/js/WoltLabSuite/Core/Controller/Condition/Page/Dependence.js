/**
 * Shows and hides an element that depends on certain selected pages when setting up conditions.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../../Dom/Util", "../../../Event/Handler"], function (require, exports, tslib_1, Util_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.register = register;
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    const _pages = Array.from(document.querySelectorAll('input[name="pageIDs[]"]'));
    const _dependentElements = [];
    const _pageIds = new WeakMap();
    const _hiddenElements = new WeakMap();
    let _didInit = false;
    /**
     * Checks if only relevant pages are selected. If that is the case, the dependent
     * element is shown, otherwise it is hidden.
     */
    function checkVisibility() {
        _dependentElements.forEach((dependentElement) => {
            const pageIds = _pageIds.get(dependentElement);
            const checkedPageIds = [];
            _pages.forEach((page) => {
                if (page.checked) {
                    checkedPageIds.push(~~page.value);
                }
            });
            const irrelevantPageIds = checkedPageIds.filter((pageId) => !pageIds.includes(pageId));
            if (!checkedPageIds.length || irrelevantPageIds.length) {
                hideDependentElement(dependentElement);
            }
            else {
                showDependentElement(dependentElement);
            }
        });
        EventHandler.fire("com.woltlab.wcf.pageConditionDependence", "checkVisivility");
    }
    /**
     * Hides all elements that depend on the given element.
     */
    function hideDependentElement(dependentElement) {
        Util_1.default.hide(dependentElement);
        const hiddenElements = _hiddenElements.get(dependentElement);
        hiddenElements.forEach((hiddenElement) => Util_1.default.hide(hiddenElement));
        _hiddenElements.set(dependentElement, []);
    }
    /**
     * Shows all elements that depend on the given element.
     */
    function showDependentElement(dependentElement) {
        Util_1.default.show(dependentElement);
        // make sure that all parent elements are also visible
        let parentElement = dependentElement;
        while ((parentElement = parentElement.parentElement) && parentElement) {
            if (Util_1.default.isHidden(parentElement)) {
                _hiddenElements.get(dependentElement).push(parentElement);
            }
            Util_1.default.show(parentElement);
        }
    }
    function register(dependentElement, pageIds) {
        _dependentElements.push(dependentElement);
        _pageIds.set(dependentElement, pageIds);
        _hiddenElements.set(dependentElement, []);
        if (!_didInit) {
            _pages.forEach((page) => {
                page.addEventListener("change", () => checkVisibility());
            });
            _didInit = true;
        }
        // remove the dependent element before submit if it is hidden
        dependentElement.closest("form").addEventListener("submit", () => {
            if (Util_1.default.isHidden(dependentElement)) {
                dependentElement.remove();
            }
        });
        checkVisibility();
    }
});
