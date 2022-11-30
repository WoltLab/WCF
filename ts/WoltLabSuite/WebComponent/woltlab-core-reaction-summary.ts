/**
 * The `<woltlab-core-reaction-summary>` element presents the
 * reactions of an element and offers a detailed summary.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

{
  type ReactionTypeId = number;
  type Count = number;
  type ReactionData = [ReactionTypeId, Count];
  type Data = Map<ReactionTypeId, Count>;

  class WoltlabCoreReactionSummaryElement extends HTMLElement {
    connectedCallback() {
      this.setData(this.#getData(), this.#getSelectedReaction());
    }

    setData(data: Data, selectedReaction?: number): void {
      this.#render(data, selectedReaction);
    }

    get objectId(): number {
      return parseInt(this.getAttribute("object-id")!);
    }

    get objectType(): string {
      return this.getAttribute("object-type")!;
    }

    #render(data: Data, selectedReaction?: number): void {
      this.innerHTML = "";

      if (!data.size) return;

      const button = document.createElement("button");
      button.classList.add("reactionSummary", "jsTooltip");
      button.title = window.WoltLabLanguage.getPhrase("wcf.reactions.summary.listReactions");
      button.addEventListener("click", () => {
        this.dispatchEvent(new Event("showDetails"));
      });
      this.append(button);

      data.forEach((value, key) => {
        const countButton = document.createElement("span");
        countButton.classList.add("reactionCountButton");
        if (key === selectedReaction) {
          countButton.classList.add("selected");
        }

        const icon = document.createElement("span");
        icon.innerHTML = window.REACTION_TYPES[key].renderedIcon;
        countButton.append(icon);

        const counter = document.createElement("span");
        counter.classList.add("reactionCount");
        counter.textContent = value.toString();
        countButton.append(counter);

        button.append(countButton);
      });
    }

    #getData(): Data {
      const data = JSON.parse(this.getAttribute("data")!) as ReactionData[];
      this.removeAttribute("data");
      return new Map(data);
    }

    #getSelectedReaction(): number {
      return parseInt(this.getAttribute("selected-reaction")!);
    }
  }

  window.customElements.define("woltlab-core-reaction-summary", WoltlabCoreReactionSummaryElement);
}
