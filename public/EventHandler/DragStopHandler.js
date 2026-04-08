import AbstractHandler from './AbstractHandler.js';

class DragStopHandler extends AbstractHandler {
  static register(element) {
    element.setAttribute('draggable', true);
    super.register(element);
  }

  handle(event) {
//    console.log("HANDLE DRAGSTOPHANDLER!");
    window.dragTarget = undefined;
//    CAD_WYSIWYG_Editor.placeholderManager.reset();
  }
}

DragStopHandler.eventName = 'dragend';
export default DragStopHandler;
