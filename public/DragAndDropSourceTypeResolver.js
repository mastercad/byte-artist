class DragAndDropSourceTypeResolver {

    static resolve(event) {
        var dataTransfer = event.originalEvent.dataTransfer;

        if (dataTransfer 
            && 0 < dataTransfer.files.length
        ) {
            return DragAndDropSourceTypeResolver.SOURCE_TYPE_UPLOAD;
        } else if (0 < event.originalEvent.dataTransfer.getData('text/plain').length) {
            return DragAndDropSourceTypeResolver.SOURCE_TYPE_EXTERNAL;
        }
        return DragAndDropSourceTypeResolver.SOURCE_TYPE_UNKNOWN;
    }
}

DragAndDropSourceTypeResolver.SOURCE_TYPE_UPLOAD = 'source_type_upload';
DragAndDropSourceTypeResolver.SOURCE_TYPE_EXTERNAL = 'source_type_external';
DragAndDropSourceTypeResolver.SOURCE_TYPE_UNKNOWN = 'source_type_unknown';

export default DragAndDropSourceTypeResolver;