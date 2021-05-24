import AbstractHandler from "./AbstractHandler.js";
import CAD_WYSIWYG_Editor from "../CAD_WYSIWYG_Editor.js";

class DragLeaveHandler extends AbstractHandler {
  static register(element) {
    super.register(element);
  }

  constructor(element) {
    super(element);
    this.element = element;
  }

  handle(event) {
//    console.log("HANDLE DRAGLEAVEHANDLER!");
    CAD_WYSIWYG_Editor.placeholderManager.manage(event);
  }
}

DragLeaveHandler.eventName = 'dragleave';
export default DragLeaveHandler;
