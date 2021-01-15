import { DialogOptions } from "../../Ui/Dialog/Data";

interface InternalFormBuilderData {
  [key: string]: any;
}

export interface AjaxResponseReturnValues {
  dialog: string;
  formId: string;
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
  successCallback: (returnValues: AjaxResponseReturnValues) => void;
  usesDboAction: boolean;
}

export interface LabelFormFieldOptions {
  forceSelection: boolean;
  showWithoutSelection: boolean;
}
