export interface Reaction {
  title: string;
  renderedIcon: string;
  iconPath: string;
  showOrder: number;
  reactionTypeID: number;
  isAssignable: 1 | 0;
}

export interface ReactionStats {
  [key: string]: number;
}
