import DOMHelper from "./DOMHelper.js";
import Mapper from "./Mapper.js";

class PlaceholderManager {

  constructor() {
    console.log("LEGE NEUEN PLACEHOLDERMANAGER AN!");
    this.currentTarget = undefined;
    this.currentBeforeX = undefined;
    this.currentBeforeY = undefined;
    this.currentPlaceholder = undefined;
    this.copy = false;
  }

  manage(event) {
    console.log(event);
//    console.log("PLACEHOLDER MANAGER FÜR");
//    console.log(window.dragTarget);
//    console.log("Über");
//    console.log(event.target);

    if (event.target === window.dragTarget) {
      if (this.copy) {
        this.reset();
      }
      return false;
    }

    if (event.target.classList.contains('placeholder')
      || event.target === this.currentPlaceholder
    ) {
      return false;
    }

    let coords = this.calculateCaretPosition(event);

    if (this.currentTarget === event.target
      && this.currentBeforeX === coords.beforeX
      && this.currentBeforeY === coords.beforeY
    ) {
      return false;
    }

    if (undefined !== this.currentPlaceholder
      && undefined !== this.currentPlaceholder.parentNode
      && null !== this.currentPlaceholder.parentNode
    ) {
      this.reset();
    }

    this.currentTarget = event.target;

    this.createPlaceholder(event);

    if (undefined === this.currentPlaceholder) {
      console.log("PLACEHOLER NOT CREATED!");
      return false;
    }

    try {
      // in the first half of the square
      if (coords.beforeX || coords.beforeY) {
        DOMHelper.insertBefore(this.currentPlaceholder, event.target);
      } else if (!coords.beforeX || !coords.beforeY) {
        DOMHelper.insertAfter(this.currentPlaceholder, event.target);
      }
    } catch {
      console.log("KANN NICHT EINFÜGEN!");
    }
    this.currentBeforeX = coords.beforeX;
    this.currentBeforeY = coords.beforeY;
  }

  createPlaceholder(event) {
    if (0 < event.dataTransfer.items.length) {
      console.log("External DRAG!");
      this.currentPlaceholder = document.createElement('div');
      this.currentPlaceholder.classList.add('box');
      this.currentPlaceholder.classList.add('placeholder');
    } else if (true === this.copy) {
      this.currentPlaceholder = window.dragTarget.cloneNode(true);
      this.currentPlaceholder.classList.add('placeholder');
    } else {
      this.currentPlaceholder = window.dragTarget;
    }
  }

  persistPlaceholder() {
    console.log("Persist Placeholder!");
    if (undefined !== this.currentPlaceholder) {
      console.log("HABE Placeholder zum löschen!");

      let dataTransferFiles = event.dataTransfer.files;
      for (let currentPos = 0; currentPos < dataTransferFiles.length; ++currentPos) {
        let image = document.createElement('img');
//        image.setAttribute('style', 'width: 100%;');
        let reader = new FileReader();
        reader.onload = (function(aImg) {
          return function(e) {
  //          aImg.onload = function() {
  //            ctx.drawImage(aImg,0,0);
  //          }
            // e.target.result is a dataURL for the image
            aImg.src = e.target.result;
          };
        })(image);
        reader.readAsDataURL(dataTransferFiles[currentPos]);
        this.currentPlaceholder.appendChild(image);
      }
      this.currentPlaceholder.classList.remove('placeholder');
      this.currentPlaceholder = undefined;
      this.reset();
    }
  }

  reset() {
    if (undefined !== this.currentPlaceholder) {
      DOMHelper.removeElement(this.currentPlaceholder);
      this.currentPlaceholder = undefined;
    }
    this.currentTarget = undefined;
    this.currentBeforeX = undefined;
    this.currentBeforeY = undefined;
  }

  calculateCaretPosition(event) {
    let rect = event.target.getBoundingClientRect();
    let x = (event.clientX - rect.left);
    let y = (event.clientY - rect.top);

    return {
      x: x,
      y: y,
      width: rect.width,
      height: rect.height,
      beforeX: (x < (rect.width / 2)),
      beforeY: (y < (rect.height / 2))
    };
  }

  /**
   * Function to parse given element and set members of service depending on element attributes.
   *
   * @param {DOMElement} element
   */
  parseDraggableElement(element) {
    Mapper.map(element.attributes, this, PlaceholderManager.attributeMap);
  }
}

PlaceholderManager.attributeMap = {
  'cad-copy': 'copy',
  'cad-clone': '!copy',
}

export default PlaceholderManager;
