/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { CarouselSlide, FancyboxInstance } from "@fancyapps/ui";
import { CarouselInstance } from "@fancyapps/ui/dist/carousel/carousel";
import { allExternalMediaEnabled } from "WoltLabSuite/Core/Ui/Message/UserConsent";
import { stringToBool } from "WoltLabSuite/Core/Core";

export class ConsentPlugin {
  #api: FancyboxInstance | undefined = undefined;
  #sliderToType = new Map<CarouselSlide, string | undefined>();
  #templateNode: HTMLTemplateElement | undefined;
  #onAddSlideEvent = this.#onAddSlide.bind(this);
  #onAttachSlideElEvent = this.#onAttachSlideEl.bind(this);

  constructor() {
    this.#templateNode = document.getElementById("consentImageViewer") as HTMLTemplateElement;
  }

  init(api: FancyboxInstance): void {
    if (this.#showAllMedia()) {
      return;
    }

    this.#api = api;

    api.on("Carousel.addSlide", this.#onAddSlideEvent).on("Carousel.attachSlideEl", this.#onAttachSlideElEvent);
  }

  destroy(): void {
    this.#api
      ?.off("Carousel.addSlide", this.#onAddSlideEvent)
      ?.off("Carousel.attachSlideEl", this.#onAttachSlideElEvent);
  }

  #onAddSlide(_: FancyboxInstance, __: CarouselInstance, slide: CarouselSlide): void {
    if (!slide.src) {
      return;
    }

    if (this.#isExternalURL(slide.src)) {
      this.#sliderToType.set(slide, slide.type);
      slide.type = "consent";
    }
  }

  #onAttachSlideEl(_: FancyboxInstance, carousel: CarouselInstance, slide: CarouselSlide): void {
    if (slide.type !== "consent") {
      return;
    }

    slide.el!.innerHTML = "";
    slide.el!.append(this.getConsentHTML(carousel, slide));
  }

  private getConsentHTML(carousel: CarouselInstance, slide: CarouselSlide): DocumentFragment {
    const clone = this.#templateNode!.content.cloneNode(true) as DocumentFragment;

    const messageUserConsent = clone.querySelector(".messageUserConsent") as HTMLElement;
    messageUserConsent.dataset.payload = "";

    const externalURL = clone.querySelector(".externalURL") as HTMLAnchorElement;
    const url = new URL(slide.src!);
    externalURL.href = slide.src!;
    externalURL.innerText = url.host;

    const button = clone.querySelector(".jsButtonMessageUserConsentEnable") as HTMLButtonElement;
    button.addEventListener("click", () => {
      this.destroy();

      this.#templateNode!.dataset.showAllMedia = "1";

      carousel
        .getSlides()
        .filter((slide) => slide.type === "consent")
        .forEach((slide) => {
          slide.type = this.#sliderToType.get(slide);
          slide.el!.innerHTML = "";
        });

      // refresh current slide content
      carousel.emit("detachSlideEl", slide);
      carousel.emit("attachSlideEl", slide);
    });

    return clone;
  }

  #showAllMedia(): boolean {
    if (!this.#templateNode) {
      return true;
    }

    if (stringToBool(this.#templateNode.dataset.showAllMedia!)) {
      return true;
    }

    return allExternalMediaEnabled();
  }

  #isExternalURL(url: string): boolean {
    return new URL(url).origin !== window.location.origin;
  }
}
