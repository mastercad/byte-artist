import AbstractHandler from './AbstractHandler.js'
import CAD_WYSIWYG_Editor from '../CAD_WYSIWYG_Editor.js';
import PlaceholderManager from '../PlaceholderManager.js';

class DropHandler extends AbstractHandler {
  handle(event) {
    console.log("HANDLE DROPHANDLER!!!");

    CAD_WYSIWYG_Editor.placeholderManager.persistPlaceholder();
  }
}

DropHandler.eventName = 'drop';
export default DropHandler;
