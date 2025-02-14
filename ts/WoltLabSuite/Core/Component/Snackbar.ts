/**
 * Shows snackbar like notifications.
 *
 * @author    Marcwl Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 */

import { getPhrase } from "WoltLabSuite/Core/Language";
import { getPageOverlayContainer } from "WoltLabSuite/Core/Helper/PageOverlay";

enum SnackbarType {
  Success,
  Progress,
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
class Snackbar extends EventTarget {
  #message: string = "";
  #type: SnackbarType;
  #snackbarElement: HTMLElement;

  constructor(message: string, type: SnackbarType) {
    super();

    this.#message = message;
    this.#type = type;

    this.#render();
  }

  get message(): string {
    return this.#message;
  }

  set message(message: string) {
    this.#message = message;

    this.#snackbarElement.querySelector(".snackbar__message")!.textContent = message;
  }

  markAsDone(): void {
    this.#type = SnackbarType.Success;
    this.#updateVisualType();

    const icon = this.#snackbarElement.querySelector(".snackbar__icon fa-icon") as FaIcon;
    icon.setIcon("check");

    this.#setHideTimeout();
  }

  #render(): void {
    const iconWrapper = document.createElement("div");
    iconWrapper.classList.add("snackbar__icon");

    const icon = document.createElement("fa-icon");
    icon.size = 24;
    icon.setIcon(this.isProgress() ? "spinner" : "check");
    iconWrapper.append(icon);

    const message = document.createElement("div");
    message.classList.add("snackbar__message");
    if (this.isProgress()) {
      message.setAttribute("aria-live", "polite");
    }
    message.append(this.message);

    this.#snackbarElement = document.createElement("div");
    this.#snackbarElement.classList.add("snackbar");
    this.#snackbarElement.setAttribute("role", "status");
    this.#updateVisualType();
    this.#snackbarElement.addEventListener("click", () => {
      if (this.isProgress()) {
        return;
      }

      this.close();
    });

    this.#snackbarElement.append(iconWrapper, message);

    getSnackbarContainer().addSnackbar(this);

    if (!this.isProgress()) {
      this.#setHideTimeout();
    }
  }

  #updateVisualType(): void {
    if (this.isProgress()) {
      this.#snackbarElement.classList.add("snackbar--progress");
      this.#snackbarElement.classList.remove("snackbar--success");
    } else {
      this.#snackbarElement.classList.remove("snackbar--progress");
      this.#snackbarElement.classList.add("snackbar--success");
    }
  }

  #setHideTimeout(): void {
    window.setTimeout(() => {
      this.close();
    }, 3_000);
  }

  isProgress(): boolean {
    return this.#type == SnackbarType.Progress;
  }

  isVisible(): boolean {
    if (this.#snackbarElement.parentElement === null) {
      return false;
    }

    if (this.#snackbarElement.classList.contains("snackbar--closing")) {
      return false;
    }

    return true;
  }

  close(): void {
    if (!this.isVisible()) {
      return;
    }

    this.dispatchEvent(new CustomEvent("snackbar:close"));

    // The animation to move the element vertically relative to its height
    // requires the value to be computed first.
    const height = Math.trunc(
      getSnackbarContainer().getGapValue() + this.#snackbarElement.getBoundingClientRect().height,
    );
    this.#snackbarElement.style.setProperty("--height", `${height}px`);

    this.#snackbarElement.classList.add("snackbar--closing");
    this.#snackbarElement.addEventListener("animationend", () => {
      this.#snackbarElement.remove();
    });
  }

  get element(): HTMLElement {
    return this.#snackbarElement;
  }
}

interface SnackbarEventMap {
  "snackbar:close": CustomEvent<void>;
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
interface Snackbar extends EventTarget {
  addEventListener: {
    <T extends keyof SnackbarEventMap>(
      type: T,
      listener: (this: Snackbar, ev: SnackbarEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
  } & HTMLElement["addEventListener"];
}

let snackbarContainer: SnackbarContainer;

function getSnackbarContainer(): SnackbarContainer {
  if (!snackbarContainer) {
    snackbarContainer = new SnackbarContainer();
  }

  return snackbarContainer;
}

class SnackbarContainer {
  readonly #element: HTMLElement;
  #snackbars: Snackbar[] = [];

  constructor() {
    this.#element = document.createElement("div");
    this.#element.classList.add("snackbarContainer");
    getPageOverlayContainer().append(this.#element);
  }

  addSnackbar(snackbar: Snackbar): void {
    const existingStaticSnackbars = this.#snackbars.filter((snackbar) => !snackbar.isProgress());
    if (existingStaticSnackbars.length > 2) {
      const oldSnackbar = existingStaticSnackbars.shift();
      oldSnackbar!.close();
    }

    this.#snackbars.push(snackbar);
    this.#element.prepend(snackbar.element);

    void snackbar.addEventListener("snackbar:close", () => {
      const i = this.#snackbars.indexOf(snackbar);
      if (i !== -1) {
        this.#snackbars = this.#snackbars.splice(i, 1);
      }
    });
  }

  getGapValue(): number {
    const gap = window.getComputedStyle(this.#element).gap;
    const match = gap.match(/^(\d+)px$/);
    if (match === null) {
      return 0;
    }

    return parseInt(match[1]);
  }
}

class SnackbarProgress {
  readonly #snackbar: Snackbar;
  readonly #label: string;
  readonly #length: number;
  #iteration = 0;

  constructor(label: string, length: number) {
    this.#label = label;
    this.#length = length;

    this.#snackbar = new Snackbar(this.#getMessage(), SnackbarType.Progress);
  }

  setIteration(iteration: number): void {
    this.#iteration = iteration;
  }

  markAsDone(): void {
    this.#snackbar.markAsDone();
  }

  get element(): Snackbar {
    return this.#snackbar;
  }

  #getMessage(): string {
    return getPhrase("wcf.global.snackbar.progress", {
      label: this.#label,
      iteration: this.#iteration,
      length: this.#length,
    });
  }
}

export function showSuccessSnackbar(message: string): Snackbar {
  return new Snackbar(message, SnackbarType.Success);
}

export function showProgressSnackbar(label: string, length: number): SnackbarProgress {
  return new SnackbarProgress(label, length);
}

export function showDefaultSuccessSnackbar(): Snackbar {
  return showSuccessSnackbar(getPhrase("wcf.global.success"));
}
