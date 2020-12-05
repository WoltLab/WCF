/**
 * Provides the trophy icon designer.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Trophy/Badge
 */

import * as Language from "../../../Language";
import UiDialog from "../../../Ui/Dialog";
import * as UiStyleFontAwesome from "../../../Ui/Style/FontAwesome";
import { DialogCallbackObject, DialogCallbackSetup } from "../../../Ui/Dialog/Data";

interface Rgba {
  r: number;
  g: number;
  b: number;
  a: number;
}

type Color = string | Rgba;

/**
 * @exports     WoltLabSuite/Core/Acp/Ui/Trophy/Badge
 */
class AcpUiTrophyBadge implements DialogCallbackObject {
  private badgeColor?: HTMLSpanElement = undefined;
  private readonly badgeColorInput: HTMLInputElement;
  private dialogContent?: HTMLElement = undefined;
  private icon?: HTMLSpanElement = undefined;
  private iconColor?: HTMLSpanElement = undefined;
  private readonly iconColorInput: HTMLInputElement;
  private readonly iconNameInput: HTMLInputElement;

  /**
   * Initializes the badge designer.
   */
  constructor() {
    const iconContainer = document.getElementById("badgeContainer")!;
    const button = iconContainer.querySelector(".button") as HTMLElement;
    button.addEventListener("click", (ev) => this.click(ev));

    this.iconNameInput = iconContainer.querySelector('input[name="iconName"]') as HTMLInputElement;
    this.iconColorInput = iconContainer.querySelector('input[name="iconColor"]') as HTMLInputElement;
    this.badgeColorInput = iconContainer.querySelector('input[name="badgeColor"]') as HTMLInputElement;
  }

  /**
   * Opens the icon designer.
   */
  private click(event: MouseEvent): void {
    event.preventDefault();

    UiDialog.open(this);
  }

  /**
   * Sets the icon name.
   */
  private setIcon(iconName: string): void {
    this.icon!.textContent = iconName;

    this.renderIcon();
  }

  /**
   * Sets the icon color, can be either a string or an object holding the
   * individual r, g, b and a values.
   */
  private setIconColor(color: Color): void {
    if (typeof color !== "string") {
      color = `rgba(${color.r}, ${color.g}, ${color.b}, ${color.a})`;
    }

    this.iconColor!.dataset.color = color;
    this.iconColor!.style.setProperty("background-color", color, "");

    this.renderIcon();
  }

  /**
   * Sets the badge color, can be either a string or an object holding the
   * individual r, g, b and a values.
   */
  private setBadgeColor(color: Color): void {
    if (typeof color !== "string") {
      color = `rgba(${color.r}, ${color.g}, ${color.b}, ${color.a})`;
    }

    this.badgeColor!.dataset.color = color;
    this.badgeColor!.style.setProperty("background-color", color, "");

    this.renderIcon();
  }

  /**
   * Renders the custom icon preview.
   */
  private renderIcon(): void {
    const iconColor = this.iconColor!.style.getPropertyValue("background-color");
    const badgeColor = this.badgeColor!.style.getPropertyValue("background-color");

    const icon = this.dialogContent!.querySelector(".jsTrophyIcon") as HTMLElement;

    // set icon
    icon.className = icon.className.replace(/\b(fa-[a-z0-9-]+)\b/, "");
    icon.classList.add(`fa-${this.icon!.textContent!}`);

    icon.style.setProperty("color", iconColor, "");
    icon.style.setProperty("background-color", badgeColor, "");
  }

  /**
   * Saves the custom icon design.
   */
  private save(event: MouseEvent): void {
    event.preventDefault();

    const iconColor = this.iconColor!.style.getPropertyValue("background-color");
    const badgeColor = this.badgeColor!.style.getPropertyValue("background-color");
    const icon = this.icon!.textContent!;

    this.iconNameInput.value = icon;
    this.badgeColorInput.value = badgeColor;
    this.iconColorInput.value = iconColor;

    const iconContainer = document.getElementById("iconContainer")!;
    const previewIcon = iconContainer.querySelector(".jsTrophyIcon") as HTMLElement;

    // set icon
    previewIcon.className = previewIcon.className.replace(/\b(fa-[a-z0-9-]+)\b/, "");
    previewIcon.classList.add("fa-" + icon);
    previewIcon.style.setProperty("color", iconColor, "");
    previewIcon.style.setProperty("background-color", badgeColor, "");

    UiDialog.close(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "trophyIconEditor",
      options: {
        onSetup: (context) => {
          this.dialogContent = context;

          this.iconColor = context.querySelector("#jsIconColorContainer .colorBoxValue") as HTMLSpanElement;
          this.badgeColor = context.querySelector("#jsBadgeColorContainer .colorBoxValue") as HTMLSpanElement;
          this.icon = context.querySelector(".jsTrophyIconName") as HTMLSpanElement;

          const buttonIconPicker = context.querySelector(".jsTrophyIconName + .button") as HTMLAnchorElement;
          buttonIconPicker.addEventListener("click", (event) => {
            event.preventDefault();

            UiStyleFontAwesome.open((iconName) => this.setIcon(iconName));
          });

          const iconColorContainer = document.getElementById("jsIconColorContainer")!;
          const iconColorPicker = iconColorContainer.querySelector(".jsButtonIconColorPicker") as HTMLAnchorElement;
          iconColorPicker.addEventListener("click", (event) => {
            event.preventDefault();

            const picker = iconColorContainer.querySelector(".jsColorPicker") as HTMLAnchorElement;
            picker.click();
          });

          const badgeColorContainer = document.getElementById("jsBadgeColorContainer")!;
          const badgeColorPicker = badgeColorContainer.querySelector(".jsButtonBadgeColorPicker") as HTMLAnchorElement;
          badgeColorPicker.addEventListener("click", (event) => {
            event.preventDefault();

            const picker = badgeColorContainer.querySelector(".jsColorPicker") as HTMLAnchorElement;
            picker.click();
          });

          const colorPicker = new window.WCF.ColorPicker(".jsColorPicker");
          colorPicker.setCallbackSubmit(() => this.renderIcon());

          const submitButton = context.querySelector(".formSubmit > .buttonPrimary") as HTMLElement;
          submitButton.addEventListener("click", (ev) => this.save(ev));
        },
        onShow: () => {
          this.setIcon(this.iconNameInput.value);
          this.setIconColor(this.iconColorInput.value);
          this.setBadgeColor(this.badgeColorInput.value);
        },
        title: Language.get("wcf.acp.trophy.badge.edit"),
      },
    };
  }
}

let acpUiTrophyBadge: AcpUiTrophyBadge;

/**
 * Initializes the badge designer.
 */
export function init(): void {
  if (!acpUiTrophyBadge) {
    acpUiTrophyBadge = new AcpUiTrophyBadge();
  }
}
