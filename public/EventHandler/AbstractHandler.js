import ObjectBinder from './ObjectBinder.js'

class AbstractHandler {

  constructor(element) {
    this.element = element;

    if (new.target === AbstractHandler) {
      throw new TypeError('Cannot construct abstract instances directly!');
    }

    if (this.handle === undefined) {
      throw new TypeError('handle must be overridden');
    }
  }
  /*
   static implementation
  */
  static register(element) {

    if (AbstractHandler.isGlobalEvent(this.eventName)) {
      if (ObjectBinder.checkAlreadyBound(document, this.eventName)) {
        return false;
      }
      console.log("REGISTER "+this.name+"!");
      ObjectBinder.bind(document, this.eventName, new this(element));
      this.addEventListener(element, document);
    } else {
      if (ObjectBinder.checkAlreadyBound(element, this.eventName)) {
        return false;
      }
      console.log("REGISTER "+this.name+"!");
      ObjectBinder.bind(element, this.eventName, new this(element));
      this.addEventListener(element);
    }
  };

  handle(event) {
    console.log("ABSTRACT HANDLE");
  };

  static addEventListener(element, document) {
    let me = this;

    if (AbstractHandler.isGlobalEvent(this.eventName)) {
      document.addEventListener(this.eventName, function(event) {
        document.data[me.eventName].handle(event);
      }, false);
    } else {
      element.addEventListener(this.eventName, function(event) {
        element.data[me.eventName].handle(event);
      }, false);
    }
  };

  static isGlobalEvent(eventName) {
    return 0 < AbstractHandler.globalEvents.indexOf(eventName);
  };

  /**
   * Prevent direct function calls on AbstractHandler
   */
  static preventDirectCall() {
    if ("AbstractHandler" === this.name) {
      throw new TypeError('Function must be called by inherited class! Not directly!');
    }
  }
}

AbstractHandler.globalEvents = [
  "dragstart",
  "dragenter",
  "dragleave",
  "dragend",
  "drop"
];

export default AbstractHandler;
