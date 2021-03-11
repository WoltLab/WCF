import { DialogOptions } from "../../Ui/Dialog/Data";
import { DatabaseObjectActionResponse } from "../../Ajax/Data";

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
  onSubmit: (formData: FormBuilderData, submitButton: HTMLButtonElement) => void;
  submitActionName?: string;
  successCallback: (returnValues: DatabaseObjectActionResponse["returnValues"]) => void;
  usesDboAction: boolean;
}

export interface LabelFormFieldOptions {
  forceSelection: boolean;
  showWithoutSelection: boolean;
}
