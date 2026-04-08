
class DOMHelper {

  // example
  //var newEl = document.createElement('div');
  //newEl.innerHTML = '<p>Hello World!</p>';

  //var ref = document.querySelector('div.before');

  //insertAfter(newEl, ref);
  static insertAfter(element, referenceNode) {
    return referenceNode.parentNode.insertBefore(element, referenceNode.nextSibling);
  }

  // example
  //var newEl = document.createElement('div');
  //newEl.innerHTML = '<p>Hello World!</p>';

  //var ref = document.querySelector('div.before');

  //insertBefore(newEl, ref);
  static insertBefore(element, referenceNode) {
    return referenceNode.parentNode.insertBefore(element, referenceNode);
  }

  static addElementById(parentId, elementTag, elementId, html) {
    // Adds an element to the document
    var p = document.getElementById(parentId);
    var newElement = document.createElement(elementTag);
    newElement.setAttribute('id', elementId);
    newElement.innerHTML = html;
    p.appendChild(newElement);
  }

  static removeElementById(elementId) {
    // Removes an element from the document
    var element = document.getElementById(elementId);
    element.parentNode.removeChild(element);
  }

  static addElement(parent, element) {
    parent.appendChild(element);
  }

  static removeElement(element) {
    element.parentNode.removeChild(element);
  }
}

export default DOMHelper;
