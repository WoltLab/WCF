/**
 * Handles the poll UI.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/Manager
 * @since   5.5
 */

import DomUtil from "../../../Dom/Util";
import Results from "./View/Results";
import VoteView from "./View/Vote";
import VoteHandler from "./Vote";

export enum PollViews {
  vote = "vote",
  results = "results",
}

export class Manager {
  public readonly pollID: number;
  public readonly canViewResults: boolean;
  public readonly canVote: boolean;
  public readonly isPublic: boolean;
  public readonly maxVotes: number;
  public readonly question: string;
  protected poll: HTMLElement;

  private voteView?: VoteView;
  private resultsView?: Results;

  private voteHandler?: VoteHandler;

  private activeView?: PollViews;

  public constructor(
    pollID: number,
    canViewResults: boolean,
    canVote: boolean,
    isPublic: boolean,
    maxVotes: number,
    question: string,
  ) {
    this.pollID = pollID;
    this.canViewResults = canViewResults;
    this.canVote = canVote;
    this.isPublic = isPublic;
    this.maxVotes = maxVotes;
    this.question = question;

    const poll = document.getElementById(`poll${pollID}`);

    if (poll === null) {
      throw new Error(`Could not find poll with id "${pollID}".`);
    }

    this.poll = poll;

    if (this.canViewResults) {
      this.resultsView = new Results(this);
    }

    if (this.canVote) {
      this.voteView = new VoteView(this);
      this.voteHandler = new VoteHandler(this);
    }
  }

  public getPollContainer(): HTMLElement {
    return this.poll;
  }

  public changeView(view: PollViews, html: string): void {
    this.activeView = view;

    this.voteView?.checkVisibility(view);
    this.resultsView?.checkVisibility(view);
    this.voteHandler?.checkVisibility(view);
    this.setInnerContainer(html);

    if (view === PollViews.vote) {
      this.voteHandler?.initSelects();
    }
  }

  private canViewParticipants(): boolean {
    return this.canViewResults && this.isPublic;
  }

  private getInnerContainer(): HTMLElement {
    const innerContainer = (this.poll.querySelector(".pollInnerContainer") as HTMLElement) || null;

    if (!innerContainer) {
      throw new Error(`Could not find inner container for poll "${this.pollID}"`);
    }

    return innerContainer;
  }

  protected setInnerContainer(html: string): void {
    DomUtil.setInnerHtml(this.getInnerContainer(), html);
  }
}

export default Manager;
