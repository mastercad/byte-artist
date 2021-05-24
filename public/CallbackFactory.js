import FileDownloadManager from "./FileDownloadManager.js";
import SourceEntity from "./SourceEntity.js";
import ElementFactory from "./ElementFactory.js";
import ExternalSourceResolver from "./ExternalSourceResolver.js";
import CAD_WYSIWYG_Editor from "./CAD_WYSIWYG_Editor.js";

class CallbackFactory {

    static create(callbackType, data) {
        switch (callbackType) {
            case CallbackFactory.CALLBACK_TYPE_UPLOAD:
                return CallbackFactory.createUploadCallback(data);
            case CallbackFactory.CALLBACK_TYPE_EXTERNAL:
                    return CallbackFactory.createExternalCallback(data);
            default:
                console.log("WRONG CALLBACK TYPE PROVIDED!");
                break;
        }
        return false;
    }

    static createUploadCallback(file) {
        return function() {
            let sourceEntity = new SourceEntity();
            sourceEntity.setId(CallbackFactory.globalFileCount++)
                .setUrl(file)
                .setOrigEvent(event)
                .setCreateCopy(true);

                FileDownloadManager.download(sourceEntity, function(event) {
                sourceEntity.setBinData(event.target.result);

                let element = ElementFactory.create(sourceEntity);
                let placeholder = $('.image-upload-placeholder:not(.locked)').first().addClass('locked');
                $(placeholder).removeClass('image-upload-placeholder locked');
                $(placeholder).replaceWith(ElementFactory.wrapWithItem(element));
                CAD_WYSIWYG_Editor.initSortable()
                    .initFileDragAndDrop();
            });
        };
    }

    static createExternalCallback(data) {
        return function() {
            let sourceEntity = new SourceEntity();
            sourceEntity.setId(CallbackFactory.globalFileCount++)
                .setUrl(data[1]);

            switch (data[2].toLowerCase()) {
                case 'planetromeo':
                    sourceEntity.setExternalSource(ExternalSourceResolver.EXTERNAL_SOURCE_PLANETROMEO);
                    break;
                case 'imgsrc':
                        sourceEntity.setExternalSource(ExternalSourceResolver.EXTERNAL_SOURCE_IMGSRC);
                    break;
                case 'youtu':
                case 'youtube':
                    sourceEntity.setExternalSource(ExternalSourceResolver.EXTERNAL_SOURCE_YOUTUBE);
                    break;
                default:
                    sourceEntity.setExternalSource(ExternalSourceResolver.EXTERNAL_SOURCE_UNKNOWN);
                    break;
            }

            FileDownloadManager.downloadFromExternal(sourceEntity, function(event) {
                if (false !== event) {
                    sourceEntity.setBinData(event.target.result);
                }
                let element = ElementFactory.create(sourceEntity);
//                $('#drag_file_placeholder_'+sourceEntity.getId()).replaceWith(me.wrapWithItem(element));
                let placeholder = $('.image-upload-placeholder').first();
                $(placeholder).removeClass('image-upload-placeholder');
                $(placeholder).replaceWith(ElementFactory.wrapWithItem(element));
                CAD_WYSIWYG_Editor.initSortable()
                    .initFileDragAndDrop();
            });
        };
    }
}

CallbackFactory.CALLBACK_TYPE_UPLOAD = 'callback_type_upload';
CallbackFactory.CALLBACK_TYPE_EXTERNAL = 'callback_type_external';
CallbackFactory.globalFileCount = 0;

export default CallbackFactory;