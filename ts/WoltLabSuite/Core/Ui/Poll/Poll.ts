/**
 * Handles the poll UI.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Poll
 * @since   5.5
 */

import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import { formatNumeric } from "../../StringUtil";
import Participants from "./View/Participants";
import Results from "./View/Results";
import VoteView from "./View/Vote";
import VoteHandler from "./Vote";

export enum PollViews {
  vote = "vote",
  results = "results",
}

export class Poll {
  public readonly pollId: number;
  private readonly element: HTMLElement;

  private readonly voteView?: VoteView = undefined;
  private readonly resultsView?: Results = undefined;
  private participants?: Participants = undefined;

  private readonly voteHandler?: VoteHandler = undefined;

  private readonly views: Map<PollViews, HTMLElement> = new Map();

  public constructor(pollID: number) {
    const poll = document.getElementById(`poll${pollID}`);

    if (poll === null) {
      throw new Error(`Could not find poll with id "${pollID}".`);
    }

    this.element = poll;
    this.pollId = pollID;

    this.getInnerContainer()
      .querySelectorAll("div")
      .forEach((element) => {
        if (element.dataset.key) {
          this.views.set(element.dataset.key as PollViews, element);
        }
      });

    if (this.canViewResults) {
      this.resultsView = new Results(this);
    }

    if (this.canVote) {
      this.voteView = new VoteView(this);
      this.voteHandler = new VoteHandler(this);
    }

    if (this.canViewParticipants()) {
      this.participants = new Participants(this);
    }
  }

  public getElement(): HTMLElement {
    return this.element;
  }

  public hasView(key: PollViews): boolean {
    return this.views.has(key);
  }

  public getView(key: PollViews): HTMLElement {
    if (!this.hasView(key)) {
      throw new Error(`The view "${key}" is unknown for poll "${this.pollId}".`);
    }

    return this.views.get(key)!;
  }

  public displayView(key: PollViews): void {
    if (!this.hasView(key)) {
      throw new Error(`The view "${key}" is unknown for poll "${this.pollId}".`);
    }

    this.views.forEach((view) => {
      view.hidden = true;
    });

    this.views.get(key)!.hidden = false;

    this.voteView?.checkVisibility(key);
    this.resultsView?.checkVisibility(key);
    this.voteHandler?.checkVisibility(key);

    if (!this.participants && this.canViewParticipants()) {
      this.participants = new Participants(this);
      this.participants.showButton();
    }
  }

  public addView(key: PollViews, html: string): void {
    const container = document.createElement("div");
    container.dataset.key = key;
    container.hidden = true;

    DomUtil.setInnerHtml(container, html);

    this.getInnerContainer().append(container);

    if (this.views.has(key)) {
      this.views.get(key)!.remove();
    }

    this.views.set(key, container);

    if (key === PollViews.vote) {
      this.voteHandler!.initSelects();
    }
  }

  private canViewParticipants(): boolean {
    return this.canViewResults && this.isPublic;
  }

  private getInnerContainer(): HTMLElement {
    const innerContainer = this.element.querySelector<HTMLElement>(".pollInnerContainer");

    if (!innerContainer) {
      throw new Error(`Could not find inner container for poll "${this.pollId}"`);
    }

    return innerContainer;
  }

  public changeTotalVotes(votes: number, tooltip: string): void {
    const badge = this.getElement().querySelector<HTMLSpanElement>(".pollTotalVotesBadge");

    if (!badge) {
      throw new Error(`Could not find total votes badge.`);
    }

    badge.textContent = formatNumeric(votes);
    badge.dataset.tooltip = tooltip;
  }

  get isPublic(): boolean {
    return this.element.dataset.isPublic === "true";
  }

  get maxVotes(): number {
    return parseInt(this.element.dataset.maxVotes!, 10);
  }

  get question(): string {
    return this.element.dataset.question!;
  }

  get canVote(): boolean {
    return this.element.dataset.canVote === "true";
  }

  set canVote(canVote: boolean) {
    this.element.dataset.canVote = canVote ? "true" : "false";
  }

  get canViewResults(): boolean {
    return this.element.dataset.canViewResult === "true";
  }

  set canViewResults(canViewResults: boolean) {
    this.element.dataset.canViewResult = canViewResults ? "true" : "false";
  }
}

const polls: Map<number, Poll> = new Map();
function setup(): void {
  document.querySelectorAll(".pollContainer").forEach((pollElement: HTMLElement) => {
    if (!pollElement.dataset.pollId) {
      throw new Error("Invalid poll element given. Missing pollID.");
    }

    const pollID = parseInt(pollElement.dataset.pollId, 10);

    if (!polls.has(pollID)) {
      polls.set(pollID, new Poll(pollID));
    }
  });
}

export function setupAll(): void {
  DomChangeListener.add("WoltLabSuite/Core/Ui/Poll/Manager/Poll", () => {
    setup();
  });

  setup();
}

export default setupAll;
