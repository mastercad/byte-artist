class ItemManager {

  static init () {
    this.initEvents();
  }

  static initEvents() {
    [...document.querySelectorAll(ItemManager.elementIdentifier)].forEach(function(item) {
      item.addEventListener('click', function() {
        console.log(item.innerHTML);
      });
    });

    return ItemManager;
  }
}

ItemManager.elementIdentifier = '.item';

export default ItemManager;
