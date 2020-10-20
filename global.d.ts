import Devtools from './wcfsetup/install/files/ts/WoltLabSuite/Core/Devtools';
import DomUtil from './wcfsetup/install/files/ts/WoltLabSuite/Core/Dom/Util';
import * as ColorUtil from './wcfsetup/install/files/ts/WoltLabSuite/Core/ColorUtil';

declare global {
  interface Window {
    Devtools?: typeof Devtools;
    ENABLE_DEBUG_MODE: boolean;
    SECURITY_TOKEN: string;
    WCF_PATH: string;
    WSC_API_URL: string;

    WCF: any;
    bc_wcfDomUtil: typeof DomUtil;
    __wcf_bc_colorUtil: typeof ColorUtil;
  }

  interface String {
    hashCode: () => string;
  }
}
