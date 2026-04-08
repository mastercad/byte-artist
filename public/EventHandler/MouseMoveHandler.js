import AbstractHandler from "./AbstractHandler.js";
import PlaceholderManager from "../PlaceholderManager.js";

class MouseMoveHandler extends AbstractHandler {
  static register(element) {
    super.register(element);
  }

  constructor(element) {
    super(element);
    this.element = element;
    this.placeholderManager = new PlaceholderManager(element);
  }

  handle(event) {
    console.log("HANDLE MOUSEMOVEHANDLER!");
//    console.log(event);

    this.placeholderManager.manage(event);
  }

  contains(search, needle) {
    let regEx = new RegExp(needle);
    return null !== search.match(regEx);
  }
}

MouseMoveHandler.eventName = 'mousemove';
export default MouseMoveHandler;
