(() => {
  type ReactionTypeId = number;
  type Count = number;
  type ReactionData = [ReactionTypeId, Count];
  type Data = Map<ReactionTypeId, Count>;

  class ReactionSummary extends HTMLElement {
    private readonly summaryContainer = document.createElement("div");

    connectedCallback() {
      this.setupShadow();
      this.setData(this.getData(), this.getSelectedReaction());
    }

    setData(data: Data, selectedReaction?: number): void {
      this.render(data, selectedReaction);
    }

    private render(data: Data, selectedReaction?: number): void {
      this.summaryContainer.innerHTML = "";

      if (!data.size) return;

      const button = document.createElement("button");
      button.classList.add("reactionSummary");
      button.addEventListener("click", () => {
        this.dispatchEvent(new Event("showDetails"));
      });
      this.summaryContainer.append(button);

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

    private getData(): Data {
      const data = JSON.parse(this.getAttribute("data")!) as ReactionData[];
      this.removeAttribute("data");
      return new Map(data);
    }

    private getSelectedReaction(): number {
      return parseInt(this.getAttribute("selected-reaction")!);
    }

    private setupShadow(): void {
      const root = this.attachShadow({ mode: "open" });

      const style = document.createElement("style");
      style.textContent = css;

      root.append(style, this.summaryContainer);
    }
  }

  window.customElements.define("reaction-summary", ReactionSummary);

  const css = `
    button {
    	all: unset;
    	cursor: pointer;
    }

    button:focus {
      outline: 5px auto -webkit-focus-ring-color;
    }

    button:hover .reactionCountButton {
      color: #7d8287; // todo: we need css variables ($wcfContentText) for this.
    }

    .reactionSummary {
      display: inline-flex;
      flex-wrap: wrap;
      gap: 5px 5px;
    }

    .reactionCountButton {
      color: #7d8287; // todo: we need css variables ($wcfContentDimmedText) for this.
      white-space: nowrap;
    }

    .reactionType {
      height: 20px;
      width: 20px;
      vertical-align: middle;
    }

    .reactionCount {
      vertical-align: middle;
    }

    .reactionCount::before {
  		content: "\u202f×\u202f";
  	}

    .selected .reactionCount {
      font-weight: 600;
    }
  `;
})();
