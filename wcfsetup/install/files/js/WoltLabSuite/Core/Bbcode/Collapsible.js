/**
 * Generic handler for collapsible bbcode boxes.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Bbcode/Collapsible
 */
define([], function () {
    "use strict";
    var _containers = elByClass('jsCollapsibleBbcode');
    /**
     * @exports	WoltLabSuite/Core/Bbcode/Collapsible
     */
    return {
        observe: function () {
            var container, toggleButtons, overflowContainer;
            while (_containers.length) {
                container = _containers[0];
                // find the matching toggle button
                toggleButtons = [];
                elBySelAll('.toggleButton:not(.jsToggleButtonEnabled)', container, function (button) {
                    //noinspection JSReferencingMutableVariableFromClosure
                    if (button.closest('.jsCollapsibleBbcode') === container) {
                        toggleButtons.push(button);
                    }
                });
                overflowContainer = elBySel('.collapsibleBbcodeOverflow', container) || container;
                if (toggleButtons.length > 0) {
                    (function (container, toggleButtons) {
                        var toggle = function (event) {
                            if (container.classList.toggle('collapsed')) {
                                toggleButtons.forEach(function (toggleButton) {
                                    if (toggleButton.classList.contains('icon')) {
                                        toggleButton.classList.remove('fa-compress');
                                        toggleButton.classList.add('fa-expand');
                                        toggleButton.title = elData(toggleButton, 'title-expand');
                                    }
                                    else {
                                        toggleButton.textContent = elData(toggleButton, 'title-expand');
                                    }
                                });
                                if (event instanceof Event) {
                                    // negative top value means the upper boundary is not within the viewport
                                    var top = container.getBoundingClientRect().top;
                                    if (top < 0) {
                                        var y = window.pageYOffset + (top - 100);
                                        if (y < 0)
                                            y = 0;
                                        window.scrollTo(window.pageXOffset, y);
                                    }
                                }
                            }
                            else {
                                toggleButtons.forEach(function (toggleButton) {
                                    if (toggleButton.classList.contains('icon')) {
                                        toggleButton.classList.add('fa-compress');
                                        toggleButton.classList.remove('fa-expand');
                                        toggleButton.title = elData(toggleButton, 'title-collapse');
                                    }
                                    else {
                                        toggleButton.textContent = elData(toggleButton, 'title-collapse');
                                    }
                                });
                            }
                        };
                        toggleButtons.forEach(function (toggleButton) {
                            toggleButton.classList.add('jsToggleButtonEnabled');
                            toggleButton.addEventListener(WCF_CLICK_EVENT, toggle);
                        });
                        // expand boxes that are initially scrolled
                        if (overflowContainer.scrollTop !== 0) {
                            overflowContainer.scrollTop = 0;
                            toggle();
                        }
                        overflowContainer.addEventListener('scroll', function () {
                            overflowContainer.scrollTop = 0;
                            if (container.classList.contains('collapsed')) {
                                toggle();
                            }
                        });
                    })(container, toggleButtons);
                }
                container.classList.remove('jsCollapsibleBbcode');
            }
        }
    };
});
