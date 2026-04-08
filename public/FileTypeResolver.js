class FileTypeResolver {
    static FILE_TYPE_IMAGE = 'file_type_image';
    static FILE_TYPE_UNKNOWN = 'file_type_unknown';

    resolve(file) {
        switch(true) {
            case /image.*/.test(file):
                return this.FILE_TYPE_IMAGE;
        }
        return this.FILE_TYPE_UNKNOWN;
    }
}

export default FileTypeResolver;