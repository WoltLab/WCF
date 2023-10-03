import "../Element/woltlab-core-menu";
import "../Element/woltlab-core-menu-group";
import "../Element/woltlab-core-menu-item";
import "../Element/woltlab-core-menu-separator";
import MenuBuilder from "./Menu/Builder";

export function create(label: string): MenuBuilder {
  const menu = document.createElement("woltlab-core-menu");
  menu.label = label;

  return new MenuBuilder(menu);
}
