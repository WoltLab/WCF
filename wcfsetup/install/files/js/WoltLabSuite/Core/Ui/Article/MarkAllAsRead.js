/**
 * Handles the 'mark as read' action for articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Article/MarkAllAsRead
 */
define(['Ajax'], function (Ajax) {
    "use strict";
    return {
        init: function () {
            elBySelAll('.markAllAsReadButton', undefined, (function (button) {
                button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
            }).bind(this));
        },
        _click: function (event) {
            event.preventDefault();
            Ajax.api(this);
        },
        _ajaxSuccess: function () {
            /* remove obsolete badges */
            // main menu
            var badge = elBySel('.mainMenu .active .badge');
            if (badge)
                elRemove(badge);
            // article list
            elBySelAll('.articleList .newMessageBadge', undefined, elRemove);
        },
        _ajaxSetup: function () {
            return {
                data: {
                    actionName: 'markAllAsRead',
                    className: 'wcf\\data\\article\\ArticleAction'
                }
            };
        }
    };
});
