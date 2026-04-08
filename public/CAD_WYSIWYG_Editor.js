/* eslint no-invalid-this: "error" */
/* eslint-env es6 */

'use strict';

import BoxManager from './BoxManager.js';
import ElementFactory from './ElementFactory.js';
import FileDropEventHandler from './FileDropEventHandler.js';
import StringHelper from './StringHelper.js';
import PlaceholderManager from './PlaceholderManager.js';

jQuery.fn.selectText = function () {
  const doc = document;
  const element = this[0];

  if (doc.body.createTextRange) {
    const range = document.body.createTextRange();
    range.moveToElementText(element);
    range.select();
  } else if (window.getSelection) {
    const selection = window.getSelection();
    const range = document.createRange();
    range.selectNodeContents(element);
    selection.removeAllRanges();
    selection.addRange(range);
  }
};

(function ($) {
  $.fn.clickToggle = function (func1, func2) {
    const funcs = [func1, func2];
    this.data('toggle-clicked', 0);
    this.click(function () {
      const tc = $(this).data('toggle-clicked');
      $.proxy(funcs[tc], this)();
      $(this).data('toggle-clicked', (tc + 1) % 2);
    });
    return this;
  };
}(jQuery));

/**
 * The main class for CAD Wysiwyg editor
 */
class CAD_WYSIWYG_Editor {
  /**
   * Inits the editor.
   */
  static init() {
    CAD_WYSIWYG_Editor
      .preventUnexpectedBehaviour()
      .initDragBox()
//      .initSortable()
//      .initFileDragAndDrop()
      .initGlobalEditorMenu();
  }

  static initDragBox() {
    BoxManager.init();

    return CAD_WYSIWYG_Editor;
  }

  /**
   * Init Global Editor menu for switch between text modes and provide draggable elements.
   *
   * @returns {CAD_WYSIWYG_Editor} the own object
   */
  static initGlobalEditorMenu() {
    const globalEditorMenu = $('<div id="global_editor_menu"></div>');
    const textEditModeBtn = $('<div class="text-edit-mode-btn"><i class="fas fa-i-cursor"></i></div>');
    const boxEditorDragTarget = $('<div class="box"></div>');
    const itemEditorDragTarget = $('<div class="item"></div>');

    $(globalEditorMenu).append(textEditModeBtn);
    $(globalEditorMenu).append(boxEditorDragTarget);
    $(globalEditorMenu).append(itemEditorDragTarget);

    $('body').append(globalEditorMenu);

    $(textEditModeBtn).clickToggle(
      function () {
        CAD_WYSIWYG_Editor.activateTextMode();
      }, function () {
        CAD_WYSIWYG_Editor.deactivateTextMode();
      },
    );

    $(boxEditorDragTarget).draggable({
      connectToSortable: '#main_container .box',
      helper: 'clone',
      revert: 'invalid',
      placeholder: function () { },
      start: function (event, ui) {
        console.log(event);
        console.log(ui);
        //                $(ui.currentTarget).attr('style', '');
        $(ui.helper).attr('style', '').addClass('box');
      },
      drag: function (event, ui) {
        $(ui.helper).attr('style', '').addClass('box');
      },
      stop: function (event, ui) {
        CAD_WYSIWYG_Editor.initSortable()
          .initBoxOptions();
      },
    }).disableSelection();

    $(itemEditorDragTarget).draggable({
      connectToSortable: '#main_container .box',
      helper: 'clone',
      revert: 'invalid',
      placeholder: function () { },
      start: function (event, ui) {
        console.log(event);
        console.log(ui);
        //                $(ui.currentTarget).attr('style', '');
        $(ui.helper).attr('style', '').addClass('item');
      },
      drag: function (event, ui) {
        $(ui.helper).attr('style', '').addClass('item');
      },
      stop: function (event, ui) {
        CAD_WYSIWYG_Editor.initSortable();
      },
    }).disableSelection();

    return CAD_WYSIWYG_Editor;
  }

  static preventUnexpectedBehaviour() {
    /** Prevent drag and drop an the entire page */
    window.addEventListener('dragover', function (e) {
      e = e || event;
      e.preventDefault();
    }, false);

    window.addEventListener('drop', function (e) {
      e = e || event;
      e.preventDefault();
    }, false);

    return CAD_WYSIWYG_Editor;
  }

  static initSortable() {
    console.log('INIT SORTABLE!');
    return false;
    //            var placeholderElement = jQuery('<div style="background-color: #eee;"></div>');

    $('#main_container.box, #main_container .box').sortable({
      placeholder: 'ui-state-highlight',
      /*                refreshPositions: true, */
      connectWith: '.box',
      //            items: 'div:not(.tool)',
      items: 'div.box, div.item',
      revert: 500,
      /*                containment: 'parent',*/ // schränkt bewegung auf parent ein
      cancel: '.tool, .unsortable',
      start: function (event, ui) {
        console.log('SORTABLE START!!!');
        console.log(ui.item.outerHeight());
        console.log(ui.item.outerWidth());

        if (0 < ui.item.height()) {
          ui.placeholder.height(ui.item.outerHeight());
          ui.placeholder.width(ui.item.outerWidth());
        } else {
          console.log('HABE KEINE HÖHE!');
          ui.placeholder.height('auto !important');
          ui.placeholder.width('auto !important');
        }
        /*                    ui.placeholder.css('visibility', 'visible !important');*/
      },
      sort: function (event, ui) { },
      drag: function (event) { },
      dragover: function (event, ui) { },
      update: function (event, ui) { },
      over: function (event, ui) { },
      out: function (event) { },
      receive: function (event, ui) {
        console.log('SORTABLE RECEIVE!!!');

        // main container soll haupt box bleiben und keine items enthalten => erweitere drop um box!
        if ($(event.target).is('#main_container') &&
          $(ui.item).not('.box')
        ) {
          console.log('WRAP WITH BOX!');
          ui.item = ElementFactory.wrapWithBox(ui.item);
        }
      },
      stop: function (event, ui) {
        console.log('SORTABLE STOP!!!');

        if (0 === $(event.target).find('.box, .item').length) {
          console.log('REMOVE EMPTY BOX!');
          const item = $(event.target);
          item.fadeOut(500, function () {
            item.remove();
          });
        }
      },
    });

    return CAD_WYSIWYG_Editor;
  }

  static initFileDragAndDrop() {
    console.log('INIT FILE DRAG AND DROP!');
    $('.box').disableSelection();

    let imageUploadPlaceholder = undefined;

    $('#main_container .box, #main_container .item').unbind('dragstart dragenter dragover dragleave drag drop').on({
      'dragstart': function (event) {
        console.log('DRAGSTART!');
      },
      'dragenter': function (event) {
        console.log('DRAGENTER!');

        $(event.originalEvent.dataTransfer).addClass('item');
        $(event.originalEvent.dataTransfer).addClass('ui-sortable');
        $(event.originalEvent.dataTransfer).addClass('ui-sortable-handle');
/*
        if ($(ui.item).hasClass('ui-sortable')) {
            $(ui.item).addClass('item');
            $(ui.item).addClass('ui-sortable');
            $(ui.item).addClass('ui-sortable-handle');
        }
*/
        event.preventDefault();
        event.stopPropagation();
      },
      'dragover': function (event) {
        console.log('DRAGOVER!');
        console.log(event);

        const target = $(event.target);
        const originalEvent = $(event.originalEvent);

        $(event.target).addClass('fileupload-drop-target');
        // layerX
        // pageX
        // offsetX
        // clientX
        const cursorX = originalEvent.clientX;
        const cursorY = originalEvent.clientY;
        const targetX = target.clientLeft;
        const targetY = target.clientTop;
        const targetWidth = target.clientWidth;
        const targetHeight = target.clientHeight;

        if (undefined != imageUploadPlaceholder) {
          imageUploadPlaceholder.remove();
          imageUploadPlaceholder = undefined;
        }

        $('.image-upload-placeholder').remove();
        $('#drag_file_placeholder_box').remove();

        let placeHolderCount = 0;
        let placeHolderContent = '<div id="drag_file_placeholder_box" class="box image-upload-placeholder-box">';

        $.each(event.originalEvent.dataTransfer.items, function (item) {
          ++placeHolderCount;
          placeHolderContent += '<div id="drag_file_placeholder_' + placeHolderCount + '" class="image-upload-placeholder" style="border: 1px solid blue; flex: 1;"></div>';
        });

        placeHolderContent += '</div>';
        imageUploadPlaceholder = $(placeHolderContent);

        if (cursorX < (targetWidth / 2)) {
          $(imageUploadPlaceholder).insertBefore(target);
        } else {
          $(imageUploadPlaceholder).insertAfter(target);
        }

        event.preventDefault();
        event.stopPropagation();
      },
      'dragleave': function (event) {
        console.log('DRAGLEAVE!');

        $(event.target).removeClass('fileupload-drop-target');
        $('.image-upload-placeholder').remove();
        $('#drag_file_placeholder_box').remove();

        if (undefined != imageUploadPlaceholder) {
          imageUploadPlaceholder = undefined;
        }
        event.preventDefault();
        event.stopPropagation();
      },
      'drag': function (event) {
        console.log('DRAG!');

        $.each(event.originalEvent.dataTransfer.items, function (index, item) {
          console.log(item);
        });

        event.preventDefault();
        event.stopPropagation();
      },
      'drop': function (event) {
        console.log('DROP!');

        $.each(event.originalEvent.dataTransfer.items, function (index, item) {
          console.log(item);
        });

        event.preventDefault();
        event.stopPropagation();

        if ($(event.target).hasClass('tool')) {
          console.log('TOOL IST KEINE DROP ACCEPTABLE KLASSE!');
          //                        $(this).sortable("cancel");
          return false;
        }

//                    dragAndDropHandler.handle(event);

        $('#drag_file_placeholder_box').attr('id', null).removeClass('image-upload-placeholder-box');
        imageUploadPlaceholder = null;

        FileDropEventHandler.handle(event, function () {
//          CAD_WYSIWYG_Editor.initSortable().initBoxOptions();
        });

        //                    $('.image-upload-placeholder').remove();

        // console.log(e.originalEvent instanceof DragEvent);
/*
        var dataTransfer = event.originalEvent.dataTransfer;

        if (dataTransfer
            && dataTransfer.files.length
        ) {

            $.each(dataTransfer.files, function(index, file) {
                if (undefined === fileList[index]) {
                    var reader = new FileReader();
                    reader.onload = $.proxy(function(file, $fileList, transferEvent) {
                        nr++;
                        let image = file.type.match('image.*') ? createImage(transferEvent.target.result) : undefined;
                        $(event.target).removeClass('.box .item');
                        handleFileUpload($(), image);
//                                setResizable("resizable"+nr);
                    }, this, file, $(event.target));
                    fileList[index] = file;
                    reader.readAsDataURL(file);
                    initDragBox();
                }
            });
        } else {
            considerDragAndDropSource(event);
        }
                // remove if the drop event fails and a placeholder is left.
//                    $('.image-upload-placeholder').remove();
                $(this).addClass("ui-state-highlight").find("p").html("Dropped!");
*/
      },

    });

    function setResizable(id) {
      console.log('setResizable');
      $('#' + id).resizable({
        stop: function (event, ui) {
          height = $('#' + id).height();
          width = $('#' + id).width();
          console.log('width=height=' + width + '==' + height);
        },
      });
    }
    return CAD_WYSIWYG_Editor;
  }

  static extendBoxWithOptions(box) {
    const found = $(box).find('.box-options');

    if (0 === found.length) {
      console.log('EXTEND BOX WITH OPTIONS!');
      console.log(box);
      const boxDirectionChangeBtn = $('<div class="tool box-options"><i class="fas fa-sync-alt change-direction"></i></div>');
      $(box).append(boxDirectionChangeBtn);

      $(boxDirectionChangeBtn).on('click', function () {
        console.log('CAD WYSIWYG EDITOR CHANGE DIRECTION!');
        const wrapper = $(this).parent();
        const currentDirection = wrapper.css('flex-direction');
        const otherDirection = currentDirection == 'row' ? 'column' : 'row';
        wrapper.css({ 'flex-direction': otherDirection });
      });
    }

    return CAD_WYSIWYG_Editor;
  }

  static extendWithResizeOptions(element) {
    $(element).append(
      '<div class="tool resize-width" data-position="right" style="z-index: 1000; position: absolute; width: 10%; height: 100%; background-color: lightblue; opacity: 0.5; top: 0; right: 0;"></div>' +
      '<div class="tool resize-width" data-position="left" style="z-index: 1000; position: absolute; width: 10%; height: 100%; background-color: lightblue; opacity: 0.5; top: 0; left: 0;"></div>' +
      '<div class="tool resize-height" data-position="top" style="z-index: 1000; position: absolute; width: 100%; height: 10%; background-color: lightblue; opacity: 0.5; top: 0; right: 0;"></div>' +
      '<div class="tool resize-height" data-position="bottom" style="z-index: 1000; position: absolute; width: 100%; height: 10%; background-color: lightblue; opacity: 0.5; bottom: 0; left: 0;"></div>',
    );
  }

  static activateTextMode() {
    console.log('ACTIVATE TEXT MODE!');
    $('#main_container .item')
      .attr('contenteditable', true)
      .unbind('change')
      .on('change', function (event) {
        console.log('CHANGE LINE BREAKS!');
        $(this).text(StringHelper.replaceNewLineWithBreak($(this).text().trim()));
      })
      .on('click', function (event) {
        $(this).focus();
      });

    return CAD_WYSIWYG_Editor;
  }

  static deactivateTextMode() {
    console.log('DEACTIVATE TEXT MODE!');
    $('.item').attr('contenteditable', false)
      .unbind('change');

    return CAD_WYSIWYG_Editor;
  }

  static initBoxOptions() {
    $('#main_container.box, #main_container .box').each(function (index, box) {
      console.log(box);
      CAD_WYSIWYG_Editor.extendBoxWithOptions(box);
    });
  }
}

CAD_WYSIWYG_Editor.placeholderManager = new PlaceholderManager();

export default CAD_WYSIWYG_Editor;
