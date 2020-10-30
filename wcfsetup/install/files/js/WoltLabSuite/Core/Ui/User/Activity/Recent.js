define(['Ajax', 'Language', 'Dom/Util'], function (Ajax, Language, DomUtil) {
    "use strict";
    function UiUserActivityRecent(containerId) { this.init(containerId); }
    UiUserActivityRecent.prototype = {
        init: function (containerId) {
            this._containerId = containerId;
            var container = elById(this._containerId);
            this._list = elBySel('.recentActivityList', container);
            var showMoreItem = elCreate('li');
            showMoreItem.className = 'showMore';
            if (this._list.childElementCount) {
                showMoreItem.innerHTML = '<button class="small">' + Language.get('wcf.user.recentActivity.more') + '</button>';
                showMoreItem.children[0].addEventListener('click', this._showMore.bind(this));
            }
            else {
                showMoreItem.innerHTML = '<small>' + Language.get('wcf.user.recentActivity.noMoreEntries') + '</small>';
            }
            this._list.appendChild(showMoreItem);
            this._showMoreItem = showMoreItem;
            elBySelAll('.jsRecentActivitySwitchContext .button', container, (function (button) {
                button.addEventListener('click', (function (event) {
                    event.preventDefault();
                    if (!button.classList.contains('active')) {
                        this._switchContext();
                    }
                }).bind(this));
            }).bind(this));
        },
        _showMore: function (event) {
            event.preventDefault();
            this._showMoreItem.children[0].disabled = true;
            Ajax.api(this, {
                actionName: 'load',
                parameters: {
                    boxID: ~~elData(this._list, 'box-id'),
                    filteredByFollowedUsers: elDataBool(this._list, 'filtered-by-followed-users'),
                    lastEventId: elData(this._list, 'last-event-id'),
                    lastEventTime: elData(this._list, 'last-event-time'),
                    userID: ~~elData(this._list, 'user-id')
                }
            });
        },
        _switchContext: function () {
            Ajax.api(this, {
                actionName: 'switchContext'
            }, (function () {
                window.location.hash = '#' + this._containerId;
                window.location.reload();
            }).bind(this));
        },
        _ajaxSuccess: function (data) {
            if (data.returnValues.template) {
                DomUtil.insertHtml(data.returnValues.template, this._showMoreItem, 'before');
                elData(this._list, 'last-event-time', data.returnValues.lastEventTime);
                elData(this._list, 'last-event-id', data.returnValues.lastEventID);
                this._showMoreItem.children[0].disabled = false;
            }
            else {
                this._showMoreItem.innerHTML = '<small>' + Language.get('wcf.user.recentActivity.noMoreEntries') + '</small>';
            }
        },
        _ajaxSetup: function () {
            return {
                data: {
                    className: 'wcf\\data\\user\\activity\\event\\UserActivityEventAction'
                }
            };
        }
    };
    return UiUserActivityRecent;
});
