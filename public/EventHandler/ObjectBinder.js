class ObjectBinder {

  static bind(element, key, data) {
    if (undefined === element.data) {
      element.data = {};
    }
    element.data[key] = data;
    return ObjectBinder;
  }

  static checkAlreadyBound(element, key) {
    if (undefined !== element.data
      && undefined !== element.data[key]
    ) {
      return true;
    }
    return false;
  }
}

export default ObjectBinder;
