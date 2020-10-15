import Devtools from './wcfsetup/install/files/ts/WoltLabSuite/Core/Devtools';
import ColorUtil from './wcfsetup/install/files/ts/WoltLabSuite/Core/ColorUtil';

declare global {
  interface Window {
    Devtools?: typeof Devtools;
    WCF_PATH: string;

    __wcf_bc_colorUtil: typeof ColorUtil;
  }

  interface String {
    hashCode: () => string;
  }
}
