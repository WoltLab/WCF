import { DialogOptions } from "../../Ui/Dialog/Data";

interface InternalFormBuilderData {
  [key: string]: any;
}

export type FormBuilderData = InternalFormBuilderData | Promise<InternalFormBuilderData>;

export interface FormBuilderDialogOptions {
  actionParameters: {
    [key: string]: any;
  };
  closeCallback: () => void;
  destroyOnClose: boolean;
  dialog: DialogOptions;
  onSubmit: (FormBuilderData, HTMLButtonElement) => void;
  submitActionName?: string;
  successCallback: (AjaxResponseReturnValues) => void;
  usesDboAction: boolean;
}

export interface LabelFormFieldOptions {
  forceSelection: boolean;
  showWithoutSelection: boolean;
}
