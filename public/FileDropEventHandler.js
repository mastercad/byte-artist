import FileDropEventIteratorFactory from "./FileDropEventIteratorFactory.js";

class FileDropEventHandler {

    static handle(event, callbackFunc) {
        const iterator = FileDropEventIteratorFactory.create(event);

        let file = iterator.next();

        while (!file.done) {
            file.execute();
            file = iterator.next();
        }

        console.log("HANDLE FILE DROP EVENT FINISHED!");

        if (undefined !== callbackFunc) {
            callbackFunc();
        }
    }
}

export default FileDropEventHandler;