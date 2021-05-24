import AbstractHandler from "./AbstractHandler.js";
import CAD_WYSIWYG_Editor from "../CAD_WYSIWYG_Editor.js";

class DragOverHandler extends AbstractHandler {
  static register(element) {
    super.register(element);
  }

  constructor(element) {
    super(element);
    this.element = element;
  }

  handle(event) {
//    console.log("HANDLE DRAGOVERHANDLER!");

    CAD_WYSIWYG_Editor.placeholderManager.manage(event);
  }
}

DragOverHandler.eventName = 'dragover';
export default DragOverHandler;
