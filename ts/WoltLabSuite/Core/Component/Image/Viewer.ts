import { Fancybox } from "@fancyapps/ui";

export function setup() {
  Fancybox.bind("[data-fancybox]");
  Fancybox.bind('[data-fancybox="attachments"]');
}
