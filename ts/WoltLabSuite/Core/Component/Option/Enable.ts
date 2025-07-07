/**
 * Enables or disables options based on the value of the element.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";

type SelectOption = {
  value: string;
  option: string;
};

function change(option: HTMLInputElement | HTMLSelectElement) {
  const disableOptions = JSON.parse(option.dataset.disableOptions!);
  const enableOptions = JSON.parse(option.dataset.enableOptions!);

  if (option instanceof HTMLInputElement) {
    switch (option.type) {
      case "checkbox":
        showOptions(option.checked, disableOptions, enableOptions);
        break;

      case "radio":
        if (option.checked) {
          let isActive = true;
          if (option.dataset.isBoolean && option.value !== "1") {
            isActive = false;
          }
          showOptions(isActive, disableOptions, enableOptions);
        }
        break;
    }
  } else if (option instanceof HTMLSelectElement) {
    const value = option.value;
    const relevantDisableOptions: string[] = [];
    const relevantEnableOptions: string[] = [];

    for (const item of disableOptions as SelectOption[]) {
      if (item.value === value) {
        relevantDisableOptions.push(item.option);
      } else {
        relevantEnableOptions.push(item.option);
      }
    }

    for (const item of enableOptions as SelectOption[]) {
      if (item.value === value) {
        relevantEnableOptions.push(item.option);
      } else {
        relevantDisableOptions.push(item.option);
      }
    }

    showOptions(true, relevantDisableOptions, relevantEnableOptions);
  }
}

function showOptions(isActive: boolean, disableOptions: string[], enableOptions: string[]) {
  disableOptions.forEach((optionName) => {
    getOptionElements(optionName).forEach((element) => {
      enableOption(element, !isActive);
    });
  });

  enableOptions.forEach((optionName) => {
    getOptionElements(optionName).forEach((element) => {
      enableOption(element, isActive);
    });
  });
}

function enableOption(element: HTMLElement, enable: boolean) {
  if (
    element instanceof HTMLSelectElement ||
    (element instanceof HTMLInputElement &&
      (element.type === "checkbox" || element.type === "file" || element.type === "radio"))
  ) {
    if (element instanceof HTMLInputElement && element.type === "radio") {
      if (!element.checked) {
        element.disabled = !enable;
      } else {
        // Skip active radio buttons, this preserves the value on submit,
        // while the user is still unable to move the selection to the other,
        // now disabled options.
      }
    } else {
      element.disabled = !enable;
    }

    const parentOptionTypeBoolean = element.closest(".optionTypeBoolean");
    if (parentOptionTypeBoolean) {
      const noElement = document.getElementById(element.id + "_no") as HTMLInputElement;
      noElement.disabled = !enable;

      const neverElement = document.getElementById(element.id + "_never") as HTMLInputElement;
      if (neverElement) {
        neverElement.disabled = !enable;
      }
    }
  } else {
    if (enable) {
      element.removeAttribute("readonly");
    } else {
      element.setAttribute("readonly", "true");
    }
  }

  if (enable) {
    element.closest("dl")?.classList.remove("disabled");
  } else {
    element.closest("dl")?.classList.add("disabled");
  }
}

function getOptionElements(optionName: string): HTMLElement[] {
  const optionElement = document.getElementById(optionName);
  if (optionElement) {
    return [optionElement];
  }

  const container = document.querySelectorAll<HTMLElement>(`.${optionName}Input > dd`);

  return Array.from(container)
    .map((element) => {
      return Array.from(element.querySelectorAll<HTMLElement>("input, select, textarea"));
    })
    .flat();
}

export function setup() {
  wheneverFirstSeen(".jsEnablesOptions", (element: HTMLInputElement | HTMLSelectElement) => {
    change(element);

    element.addEventListener("change", () => {
      change(element);
    });
  });
}
