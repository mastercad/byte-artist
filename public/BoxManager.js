import ClickHandler from "./EventHandler/ClickHandler.js";
import DragHandler from "./EventHandler/DragHandler.js";
import DropHandler from "./EventHandler/DropHandler.js";
import DragEnterHandler from "./EventHandler/DragEnterHandler.js";
import DragOverHandler from "./EventHandler/DragOverHandler.js";
import DragLeaveHandler from "./EventHandler/DragLeaveHandler.js";
import DragStartHandler from "./EventHandler/DragStartHandler.js";
import DragStopHandler from "./EventHandler/DragStopHandler.js";
import MouseMoveHandler from "./EventHandler/MouseMoveHandler.js";

class BoxManager {

  static init () {
    this.initEvents();
  }

  static initEvents() {
    [...document.querySelectorAll(BoxManager.elementIdentifier)].forEach(function(item) {
      ClickHandler.register(item);
      DragHandler.register(item);
      DragStartHandler.register(item);
      DragStopHandler.register(item);
      DragOverHandler.register(item);
      DragEnterHandler.register(item);
      DragLeaveHandler.register(item);
      DropHandler.register(item);
    });

    return BoxManager;
  }
}

BoxManager.elementIdentifier = '.box';

export default BoxManager;
