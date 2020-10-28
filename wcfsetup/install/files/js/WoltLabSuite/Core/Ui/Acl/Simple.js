define(["require", "exports", "tslib", "../../Language", "../../StringUtil", "../../Dom/Change/Listener", "../../Dom/Util", "../User/Search/Input"], function (require, exports, tslib_1, Language, StringUtil, Listener_1, Util_1, Input_1) {
    "use strict";
    Language = tslib_1.__importStar(Language);
    StringUtil = tslib_1.__importStar(StringUtil);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Input_1 = tslib_1.__importDefault(Input_1);
    class UiAclSimple {
        constructor(prefix, inputName) {
            this.prefix = prefix || '';
            this.inputName = inputName || 'aclValues';
            const container = document.getElementById(this.prefix + 'aclInputContainer');
            const allowAll = document.getElementById(this.prefix + 'aclAllowAll');
            allowAll.addEventListener('change', () => {
                Util_1.default.hide(container);
            });
            const denyAll = document.getElementById(this.prefix + 'aclAllowAll_no');
            denyAll.addEventListener('change', () => {
                Util_1.default.show(container);
            });
            this.list = document.getElementById(this.prefix + 'aclAccessList');
            this.list.addEventListener('click', this.removeItem.bind(this));
            const excludedSearchValues = [];
            this.list.querySelectorAll('.aclLabel').forEach(label => {
                excludedSearchValues.push(label.textContent);
            });
            this.searchInput = new Input_1.default(document.getElementById(this.prefix + 'aclSearchInput'), {
                callbackSelect: this.select.bind(this),
                includeUserGroups: true,
                excludedSearchValues: excludedSearchValues,
                preventSubmit: true,
            });
            this.aclListContainer = document.getElementById(this.prefix + 'aclListContainer');
            Listener_1.default.trigger();
        }
        select(listItem) {
            const type = listItem.dataset.type;
            const label = listItem.dataset.label;
            const objectId = listItem.dataset.objectId;
            const iconName = type === 'group' ? 'users' : 'user';
            const html = `<span class="icon icon16 fa-${iconName}"></span>
      <span class="aclLabel">${StringUtil.escapeHTML(label)}</span>
      <span class="icon icon16 fa-times pointer jsTooltip" title="${Language.get('wcf.global.button.delete')}"></span>
      <input type="hidden" name="${this.inputName}[${type}][]" value="${objectId}">`;
            const item = document.createElement('li');
            item.innerHTML = html;
            const firstUser = this.list.querySelector('.fa-user');
            if (firstUser === null) {
                this.list.appendChild(item);
            }
            else {
                this.list.insertBefore(item, firstUser.parentNode);
            }
            Util_1.default.show(this.aclListContainer);
            this.searchInput.addExcludedSearchValues(label);
            Listener_1.default.trigger();
            return false;
        }
        removeItem(event) {
            const target = event.target;
            if (target.classList.contains('fa-times')) {
                const parent = target.parentElement;
                const label = parent.querySelector('.aclLabel');
                this.searchInput.removeExcludedSearchValues(label.textContent);
                parent.remove();
                if (this.list.childElementCount === 0) {
                    Util_1.default.hide(this.aclListContainer);
                }
            }
        }
    }
    return UiAclSimple;
});
