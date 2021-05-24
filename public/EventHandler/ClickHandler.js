import AbstractHandler from "./AbstractHandler.js";

class ClickHandler extends AbstractHandler {
  handle(event) {
//    console.log("HANDLE CLICKHANDLER!");
  }
}

ClickHandler.eventName = 'click';
export default ClickHandler;
