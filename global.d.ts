import DatePicker from "./ts/WoltLabSuite/Core/Date/Picker";
import Devtools from "./ts/WoltLabSuite/Core/Devtools";
import DomUtil from "./ts/WoltLabSuite/Core/Dom/Util";
import * as ColorUtil from "./ts/WoltLabSuite/Core/ColorUtil";
import * as EventHandler from "./ts/WoltLabSuite/Core/Event/Handler";
import UiDropdownSimple from "./ts/WoltLabSuite/Core/Ui/Dropdown/Simple";
import "@woltlab/zxcvbn";
import { Reaction } from "./ts/WoltLabSuite/Core/Ui/Reaction/Data";

declare global {
  interface Window {
    Devtools?: typeof Devtools;
    ENABLE_DEBUG_MODE: boolean;
    ENABLE_DEVELOPER_TOOLS: boolean;
    LANGUAGE_ID: number;
    PAGE_TITLE: string;
    REACTION_TYPES: {
      [key: string]: Reaction;
    };
    TIME_NOW: number;
    WCF_PATH: string;
    WSC_API_URL: string;

    jQuery: JQueryStatic;
    WCF: any;
    bc_wcfDomUtil: typeof DomUtil;
    bc_wcfSimpleDropdown: typeof UiDropdownSimple;
    __wcf_bc_colorPickerInit?: () => void;
    __wcf_bc_colorUtil: typeof ColorUtil;
    __wcf_bc_datePicker: typeof DatePicker;
    __wcf_bc_eventHandler: typeof EventHandler;
  }

  interface String {
    hashCode: () => string;
  }

  interface JQuery {
    sortable(...args: any[]): unknown;

    redactor(...args: any[]): unknown;

    messageTabMenu(...args: any[]): unknown;
  }

  type ArbitraryObject = Record<string, unknown>;
}
