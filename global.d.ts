import Devtools from './wcfsetup/install/files/ts/WoltLabSuite/Core/Devtools';
import DomUtil from './wcfsetup/install/files/ts/WoltLabSuite/Core/Dom/Util';
import * as ColorUtil from './wcfsetup/install/files/ts/WoltLabSuite/Core/ColorUtil';
import UiDropdownSimple from './wcfsetup/install/files/ts/WoltLabSuite/Core/Ui/Dropdown/Simple';

declare global {
  interface Window {
    Devtools?: typeof Devtools;
    ENABLE_DEBUG_MODE: boolean;
    SECURITY_TOKEN: string;
    TIME_NOW: number;
    WCF_PATH: string;
    WSC_API_URL: string;

    WCF: any;
    bc_wcfDomUtil: typeof DomUtil;
    __wcf_bc_colorUtil: typeof ColorUtil;
    bc_wcfSimpleDropdown: typeof UiDropdownSimple;
  }

  interface String {
    hashCode: () => string;
  }
}
