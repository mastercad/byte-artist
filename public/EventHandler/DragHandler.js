import AbstractHandler from "./AbstractHandler.js";

class DragHandler extends AbstractHandler {
  static register(element) {
    element.setAttribute('draggable', true);
    super.register(element);
  }

  handle(event) {
  }
}

DragHandler.eventName = 'drag';
export default DragHandler;
