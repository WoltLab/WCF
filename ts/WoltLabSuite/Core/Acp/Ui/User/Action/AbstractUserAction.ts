export abstract class AbstractUserAction {
  protected button: HTMLElement;
  protected userData: HTMLElement;
  protected userId: number;

  public constructor(button: HTMLElement, userId: number, userDataElement: HTMLElement) {
    this.button = button;
    this.userId = userId;
    this.userData = userDataElement;

    this.init();
  }

  protected abstract init();
}

export default AbstractUserAction;
