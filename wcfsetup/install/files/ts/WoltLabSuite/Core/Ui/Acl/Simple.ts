import * as Language from "../../Language";
import * as StringUtil from "../../StringUtil";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import UiUserSearchInput from "../User/Search/Input";

class UiAclSimple {
  private readonly aclListContainer: HTMLElement;
  private readonly list: HTMLUListElement;
  private readonly prefix: string;
  private readonly inputName: string;
  private readonly searchInput: UiUserSearchInput;

  constructor(prefix?: string, inputName?: string) {
    this.prefix = prefix || "";
    this.inputName = inputName || "aclValues";

    const container = document.getElementById(this.prefix + "aclInputContainer")!;

    const allowAll = document.getElementById(this.prefix + "aclAllowAll") as HTMLInputElement;
    allowAll.addEventListener("change", () => {
      DomUtil.hide(container);
    });

    const denyAll = document.getElementById(this.prefix + "aclAllowAll_no")!;
    denyAll.addEventListener("change", () => {
      DomUtil.show(container);
    });

    this.list = document.getElementById(this.prefix + "aclAccessList") as HTMLUListElement;
    this.list.addEventListener("click", this.removeItem.bind(this));

    const excludedSearchValues: string[] = [];
    this.list.querySelectorAll(".aclLabel").forEach((label) => {
      excludedSearchValues.push(label.textContent!);
    });

    this.searchInput = new UiUserSearchInput(document.getElementById(this.prefix + "aclSearchInput"), {
      callbackSelect: this.select.bind(this),
      includeUserGroups: true,
      excludedSearchValues: excludedSearchValues,
      preventSubmit: true,
    });

    this.aclListContainer = document.getElementById(this.prefix + "aclListContainer")!;

    DomChangeListener.trigger();
  }

  private select(listItem: HTMLLIElement): boolean {
    const type = listItem.dataset.type!;
    const label = listItem.dataset.label!;
    const objectId = listItem.dataset.objectId!;

    const iconName = type === "group" ? "users" : "user";
    const html = `<span class="icon icon16 fa-${iconName}"></span>
      <span class="aclLabel">${StringUtil.escapeHTML(label)}</span>
      <span class="icon icon16 fa-times pointer jsTooltip" title="${Language.get("wcf.global.button.delete")}"></span>
      <input type="hidden" name="${this.inputName}[${type}][]" value="${objectId}">`;

    const item = document.createElement("li");
    item.innerHTML = html;

    const firstUser = this.list.querySelector(".fa-user");
    if (firstUser === null) {
      this.list.appendChild(item);
    } else {
      this.list.insertBefore(item, firstUser.parentNode);
    }

    DomUtil.show(this.aclListContainer);

    this.searchInput.addExcludedSearchValues(label);

    DomChangeListener.trigger();

    return false;
  }

  private removeItem(event: MouseEvent): void {
    const target = event.target as HTMLElement;
    if (target.classList.contains("fa-times")) {
      const parent = target.parentElement!;
      const label = parent.querySelector(".aclLabel")!;
      this.searchInput.removeExcludedSearchValues(label.textContent!);

      parent.remove();

      if (this.list.childElementCount === 0) {
        DomUtil.hide(this.aclListContainer);
      }
    }
  }
}

export = UiAclSimple;
