import AbstractHandler from "./AbstractHandler.js";
import CAD_WYSIWYG_Editor from "../CAD_WYSIWYG_Editor.js";

class DragStartHandler extends AbstractHandler {
  static register(element) {
    element.setAttribute('draggable', true);
    super.register(element);
  }

  handle(event) {
    console.log("HANDLE DRAGSTARTHANDLER!");
    window.dragTarget = event.target;
    CAD_WYSIWYG_Editor.placeholderManager.parseDraggableElement(window.dragTarget);
  }
}

DragStartHandler.eventName = 'dragstart';
export default DragStartHandler;
