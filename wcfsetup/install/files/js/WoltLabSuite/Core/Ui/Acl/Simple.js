define(['Language', 'StringUtil', 'Dom/ChangeListener', 'WoltLabSuite/Core/Ui/User/Search/Input'], function (Language, StringUtil, DomChangeListener, UiUserSearchInput) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            init: function () { },
            _build: function () { },
            _select: function () { },
            _removeItem: function () { }
        };
        return Fake;
    }
    function UiAclSimple(prefix, inputName) { this.init(prefix, inputName); }
    UiAclSimple.prototype = {
        init: function (prefix, inputName) {
            this._prefix = prefix || '';
            this._inputName = inputName || 'aclValues';
            this._build();
        },
        _build: function () {
            var container = elById(this._prefix + 'aclInputContainer');
            elById(this._prefix + 'aclAllowAll').addEventListener('change', (function () {
                elHide(container);
            }));
            elById(this._prefix + 'aclAllowAll_no').addEventListener('change', (function () {
                elShow(container);
            }));
            this._list = elById(this._prefix + 'aclAccessList');
            this._list.addEventListener(WCF_CLICK_EVENT, this._removeItem.bind(this));
            var excludedSearchValues = [];
            elBySelAll('.aclLabel', this._list, function (label) {
                excludedSearchValues.push(label.textContent);
            });
            this._searchInput = new UiUserSearchInput(elById(this._prefix + 'aclSearchInput'), {
                callbackSelect: this._select.bind(this),
                includeUserGroups: true,
                excludedSearchValues: excludedSearchValues,
                preventSubmit: true,
            });
            this._aclListContainer = elById(this._prefix + 'aclListContainer');
            DomChangeListener.trigger();
        },
        _select: function (listItem) {
            var type = elData(listItem, 'type');
            var label = elData(listItem, 'label');
            var html = '<span class="icon icon16 fa-' + (type === 'group' ? 'users' : 'user') + '"></span>';
            html += '<span class="aclLabel">' + StringUtil.escapeHTML(label) + '</span>';
            html += '<span class="icon icon16 fa-times pointer jsTooltip" title="' + Language.get('wcf.global.button.delete') + '"></span>';
            html += '<input type="hidden" name="' + this._inputName + '[' + type + '][]" value="' + elData(listItem, 'object-id') + '">';
            var item = elCreate('li');
            item.innerHTML = html;
            var firstUser = elBySel('.fa-user', this._list);
            if (firstUser === null) {
                this._list.appendChild(item);
            }
            else {
                this._list.insertBefore(item, firstUser.parentNode);
            }
            elShow(this._aclListContainer);
            this._searchInput.addExcludedSearchValues(label);
            DomChangeListener.trigger();
            return false;
        },
        _removeItem: function (event) {
            if (event.target.classList.contains('fa-times')) {
                var label = elBySel('.aclLabel', event.target.parentNode);
                this._searchInput.removeExcludedSearchValues(label.textContent);
                elRemove(event.target.parentNode);
                if (this._list.childElementCount === 0) {
                    elHide(this._aclListContainer);
                }
            }
        }
    };
    return UiAclSimple;
});
