import DragAndDropSourceTypeResolver from "./DragAndDropSourceTypeResolver.js";
import CallbackFactory from "./CallbackFactory.js";
import StringHelper from "./StringHelper.js";

class FileDropEventIteratorFactory {

    static create(event) {
        switch (DragAndDropSourceTypeResolver.resolve(event)) {
            case DragAndDropSourceTypeResolver.SOURCE_TYPE_UPLOAD:
                return FileDropEventIteratorFactory.makeIterator(
                    event.originalEvent.dataTransfer.files,
                    FileDropEventIteratorFactory.CALLBACK_TYPE_UPLOAD
                );

            case DragAndDropSourceTypeResolver.SOURCE_TYPE_EXTERNAL:
                let regex = /src="(http[s]*:\/\/[www\.]*([^\/]*)[a-zA-Z0-9]{0,}\/.*?)"/g;
                let match = undefined;
                let matches = [];
                let matchCount = 0;

                while (match = regex.exec(StringHelper.cleanUpFromNotVisibileChars(event.originalEvent.dataTransfer.getData('text/html')))) {
                    matches[matchCount++] = match;
                }

                return FileDropEventIteratorFactory.makeIterator(
                    matches,
                    FileDropEventIteratorFactory.CALLBACK_TYPE_EXTERNAL
                );

            default:
                console.log("NOTHING RESOLVED!");
                break;
        }
        return false;
    }

    static makeIterator(data, callbackType) {
        let nextIndex = 0;
        return {
            next: () => {
                if (nextIndex < data.length) {
                    let dataPart = data[nextIndex++];
                    return {
                        execute: CallbackFactory.create(callbackType, dataPart),
                        done: false
                    };
                }
                return {
                    done: true
                };
            }
        };
    }
}

FileDropEventIteratorFactory.CALLBACK_TYPE_UPLOAD = 'callback_type_upload';
FileDropEventIteratorFactory.CALLBACK_TYPE_EXTERNAL = 'callback_type_external';

export default FileDropEventIteratorFactory;