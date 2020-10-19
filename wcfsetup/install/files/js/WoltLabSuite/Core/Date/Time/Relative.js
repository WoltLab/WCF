/**
 * Transforms <time> elements to display the elapsed time relative to the current time.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Date/Time/Relative
 */
define(['Dom/ChangeListener', 'Language', 'WoltLabSuite/Core/Date/Util', 'WoltLabSuite/Core/Timer/Repeating'], function (DomChangeListener, Language, DateUtil, Repeating) {
    "use strict";
    var _elements = elByTag('time');
    var _isActive = true;
    var _isPending = false;
    var _offset = null;
    /**
     * @exports	WoltLabSuite/Core/Date/Time/Relative
     */
    return {
        /**
         * Transforms <time> elements on init and binds event listeners.
         */
        setup: function () {
            new Repeating(this._refresh.bind(this), 60000);
            DomChangeListener.add('WoltLabSuite/Core/Date/Time/Relative', this._refresh.bind(this));
            document.addEventListener('visibilitychange', this._onVisibilityChange.bind(this));
        },
        _onVisibilityChange: function () {
            if (document.hidden) {
                _isActive = false;
                _isPending = false;
            }
            else {
                _isActive = true;
                // force immediate refresh
                if (_isPending) {
                    this._refresh();
                    _isPending = false;
                }
            }
        },
        _refresh: function () {
            // activity is suspended while the tab is hidden, but force an
            // immediate refresh once the page is active again
            if (!_isActive) {
                if (!_isPending)
                    _isPending = true;
                return;
            }
            var date = new Date();
            var timestamp = (date.getTime() - date.getMilliseconds()) / 1000;
            if (_offset === null)
                _offset = timestamp - window.TIME_NOW;
            for (var i = 0, length = _elements.length; i < length; i++) {
                var element = _elements[i];
                if (!element.classList.contains('datetime') || elData(element, 'is-future-date'))
                    continue;
                var elTimestamp = ~~elData(element, 'timestamp') + _offset;
                var elDate = elData(element, 'date');
                var elTime = elData(element, 'time');
                var elOffset = elData(element, 'offset');
                if (!elAttr(element, 'title')) {
                    elAttr(element, 'title', Language.get('wcf.date.dateTimeFormat').replace(/%date%/, elDate).replace(/%time%/, elTime));
                }
                // timestamp is less than 60 seconds ago
                if (elTimestamp >= timestamp || timestamp < (elTimestamp + 60)) {
                    element.textContent = Language.get('wcf.date.relative.now');
                }
                // timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
                else if (timestamp < (elTimestamp + 3540)) {
                    var minutes = Math.max(Math.round((timestamp - elTimestamp) / 60), 1);
                    element.textContent = Language.get('wcf.date.relative.minutes', { minutes: minutes });
                }
                // timestamp is less than 24 hours ago
                else if (timestamp < (elTimestamp + 86400)) {
                    var hours = Math.round((timestamp - elTimestamp) / 3600);
                    element.textContent = Language.get('wcf.date.relative.hours', { hours: hours });
                }
                // timestamp is less than 6 days ago
                else if (timestamp < (elTimestamp + 518400)) {
                    var midnight = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                    var days = Math.ceil((midnight / 1000 - elTimestamp) / 86400);
                    // get day of week
                    var dateObj = DateUtil.getTimezoneDate((elTimestamp * 1000), elOffset * 1000);
                    var dow = dateObj.getDay();
                    var day = Language.get('__days')[dow];
                    element.textContent = Language.get('wcf.date.relative.pastDays', { days: days, day: day, time: elTime });
                }
                // timestamp is between ~700 million years BC and last week
                else {
                    element.textContent = Language.get('wcf.date.shortDateTimeFormat').replace(/%date%/, elDate).replace(/%time%/, elTime);
                }
            }
        }
    };
});
