import DatePicker from "./wcfsetup/install/files/ts/WoltLabSuite/Core/Date/Picker";
import Devtools from "./wcfsetup/install/files/ts/WoltLabSuite/Core/Devtools";
import DomUtil from "./wcfsetup/install/files/ts/WoltLabSuite/Core/Dom/Util";
import * as ColorUtil from "./wcfsetup/install/files/ts/WoltLabSuite/Core/ColorUtil";
import UiDropdownSimple from "./wcfsetup/install/files/ts/WoltLabSuite/Core/Ui/Dropdown/Simple";
import "@woltlab/zxcvbn";
import { Reaction } from "./wcfsetup/install/files/ts/WoltLabSuite/Core/Ui/Reaction/Data";

declare global {
  interface Window {
    Devtools?: typeof Devtools;
    ENABLE_DEBUG_MODE: boolean;
    REACTION_TYPES: {
      [key: string]: Reaction;
    };
    SECURITY_TOKEN: string;
    TIME_NOW: number;
    WCF_PATH: string;
    WSC_API_URL: string;

    jQuery: any;
    WCF: any;
    bc_wcfDomUtil: typeof DomUtil;
    bc_wcfSimpleDropdown: typeof UiDropdownSimple;
    __wcf_bc_colorUtil: typeof ColorUtil;
    __wcf_bc_datePicker: typeof DatePicker;
  }

  interface String {
    hashCode: () => string;
  }
}
