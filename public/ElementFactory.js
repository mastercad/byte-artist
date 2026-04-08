import CAD_WYSIWYG_Editor from "./CAD_WYSIWYG_Editor.js";

/**
 * Factory to create HTML Element by given sourceEntity
 */
class ElementFactory {

    static create(sourceEntity) {
        if (true === sourceEntity.isCreateCopy()) {
            return ElementFactory.createImage(sourceEntity.getBinData());
        }
        return ElementFactory.createImage(sourceEntity.getUrl());
    }

    static createImage(data) {
        return $('<img id="resizable" style="display: flex; flex-row: row nowrap; align-items: center; max-width: 100%; max-height: 100%;" class="resizable" src="' + data + '" />');
    }

    static createItem() {
        return $('<div class="item box-image-item" style="flex: 1;"></div>');
    }

    static createBox() {
        let box = $('<div class="box box-image-item" style="flex: 1;"></div>');
        CAD_WYSIWYG_Editor.extendBoxWithOptions(box);
        return box;
    }

    static wrapWithBox(element) {
        let box = $('<div class="box box-image-item" style="flex: 1;"></div>');
        box.append(element);

        CAD_WYSIWYG_Editor.extendBoxWithOptions(box);
        return box;
    }

    static wrapWithItem(element) {
        let item = $('<div class="item box-image-item" style="flex: 1;"></div>');
        item.append(element);
        return item;
    }
};
 
export default ElementFactory;
