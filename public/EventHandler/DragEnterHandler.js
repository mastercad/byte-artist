import AbstractHandler from "./AbstractHandler.js";
import CAD_WYSIWYG_Editor from "../CAD_WYSIWYG_Editor.js";

class DragEnterHandler extends AbstractHandler {
  static register(element) {
    super.register(element);
  }

  constructor(element) {
    super(element);
    this.element = element;
  }

  handle(event) {
    if (event.target != window.dragTarget) {
      console.log("HANDLE DRAGENTERHANDLER!");
//      CAD_WYSIWYG_Editor.placeholderManager.manage(event);
    }
  }

  contains(search, needle) {
    let regEx = new RegExp(needle);
    return null !== search.match(regEx);
  }
}

DragEnterHandler.eventName = 'dragenter';
export default DragEnterHandler;
