import AcpUiPackagePrepareInstallation from "../Ui/Package/PrepareInstallation";

function installPackage(button: HTMLButtonElement): void {
  const installation = new AcpUiPackagePrepareInstallation();
  installation.start(button.dataset.package!, button.dataset.packageVersion!);
}

export function setup(): void {
  document.querySelectorAll<HTMLButtonElement>(".jsInstallPackage").forEach((button) => {
    button.addEventListener("click", () => {
      installPackage(button);
    });
  });
}
