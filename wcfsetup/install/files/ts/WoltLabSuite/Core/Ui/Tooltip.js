/**
 * Provides enhanced tooltips.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Tooltip
 */
define(['Environment', 'Dom/ChangeListener', 'Ui/Alignment'], function (Environment, DomChangeListener, UiAlignment) {
    "use strict";
    var _callbackMouseEnter = null;
    var _callbackMouseLeave = null;
    var _elements = null;
    var _pointer = null;
    var _text = null;
    var _tooltip = null;
    /**
     * @exports	WoltLabSuite/Core/Ui/Tooltip
     */
    return {
        /**
         * Initializes the tooltip element and binds event listener.
         */
        setup: function () {
            if (Environment.platform() !== 'desktop')
                return;
            _tooltip = elCreate('div');
            elAttr(_tooltip, 'id', 'balloonTooltip');
            _tooltip.classList.add('balloonTooltip');
            _tooltip.addEventListener('transitionend', function () {
                if (!_tooltip.classList.contains('active')) {
                    // reset back to the upper left corner, prevent it from staying outside
                    // the viewport if the body overflow was previously hidden
                    ['bottom', 'left', 'right', 'top'].forEach(function (property) {
                        _tooltip.style.removeProperty(property);
                    });
                }
            });
            _text = elCreate('span');
            elAttr(_text, 'id', 'balloonTooltipText');
            _tooltip.appendChild(_text);
            _pointer = elCreate('span');
            _pointer.classList.add('elementPointer');
            _pointer.appendChild(elCreate('span'));
            _tooltip.appendChild(_pointer);
            document.body.appendChild(_tooltip);
            _elements = elByClass('jsTooltip');
            _callbackMouseEnter = this._mouseEnter.bind(this);
            _callbackMouseLeave = this._mouseLeave.bind(this);
            this.init();
            DomChangeListener.add('WoltLabSuite/Core/Ui/Tooltip', this.init.bind(this));
            window.addEventListener('scroll', this._mouseLeave.bind(this));
        },
        /**
         * Initializes tooltip elements.
         */
        init: function () {
            if (_elements.length === 0) {
                return;
            }
            elBySelAll('.jsTooltip', undefined, function (element) {
                element.classList.remove('jsTooltip');
                var title = elAttr(element, 'title').trim();
                if (title.length) {
                    elData(element, 'tooltip', title);
                    element.removeAttribute('title');
                    elAttr(element, 'aria-label', title);
                    element.addEventListener('mouseenter', _callbackMouseEnter);
                    element.addEventListener('mouseleave', _callbackMouseLeave);
                    element.addEventListener(WCF_CLICK_EVENT, _callbackMouseLeave);
                }
            });
        },
        /**
         * Displays the tooltip on mouse enter.
         *
         * @param	{Event}         event	event object
         */
        _mouseEnter: function (event) {
            var element = event.currentTarget;
            var title = elAttr(element, 'title');
            title = (typeof title === 'string') ? title.trim() : '';
            if (title !== '') {
                elData(element, 'tooltip', title);
                elAttr(element, 'aria-label', title);
                element.removeAttribute('title');
            }
            title = elData(element, 'tooltip');
            // reset tooltip position
            _tooltip.style.removeProperty('top');
            _tooltip.style.removeProperty('left');
            // ignore empty tooltip
            if (!title.length) {
                _tooltip.classList.remove('active');
                return;
            }
            else {
                _tooltip.classList.add('active');
            }
            _text.textContent = title;
            UiAlignment.set(_tooltip, element, {
                horizontal: 'center',
                verticalOffset: 4,
                pointer: true,
                pointerClassNames: ['inverse'],
                vertical: 'top'
            });
        },
        /**
         * Hides the tooltip once the mouse leaves the element.
         */
        _mouseLeave: function () {
            _tooltip.classList.remove('active');
        }
    };
});
