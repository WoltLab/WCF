"use strict";
(() => {
    class ReactionSummary extends HTMLElement {
        constructor() {
            super(...arguments);
            this.summaryContainer = document.createElement("div");
        }
        connectedCallback() {
            this.setupShadow();
            this.setData(this.getData());
        }
        setData(data) {
            this.render(data);
        }
        render(data) {
            this.summaryContainer.innerHTML = "";
            if (!data.size)
                return;
            const button = document.createElement("button");
            button.classList.add("reactionSummary");
            button.addEventListener("click", () => {
                this.dispatchEvent(new Event("showDetails"));
            });
            this.summaryContainer.append(button);
            data.forEach((value, key) => {
                const countButton = document.createElement("span");
                countButton.classList.add("reactionCountButton");
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
        getData() {
            const data = JSON.parse(this.getAttribute("data"));
            this.removeAttribute("data");
            return new Map(data);
        }
        setupShadow() {
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
  `;
})();
