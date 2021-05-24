/**
 * Resolve the external source by url.
 */
class ExternalSourceTypeResolver {

    static resolve(content) {
        switch (content.match(/^http[s]*:\/\/[www\.]*(.*?)\.[a-zA-Z0-9]{1,}/)[1].toLowerCase()) {
            case 'planetromeo':
                return ExternalSourceTypeResolver.EXTERNAL_SOURCE_TYPE_PLANETROMEO;
            case 'imgsrc':
                return ExternalSourceTypeResolver.EXTERNAL_SOURCE_TYPE_IMGSRC;
            case 'youtu':
            case 'youtube':
                return ExternalSourceTypeResolver.EXTERNAL_SOURCE_TYPE_YOUTUBE;
        }
        return ExternalSourceTypeResolver.EXTERNAL_SOURCE_TYPE_UNKNOWN;
    }
}

ExternalSourceTypeResolver.EXTERNAL_SOURCE_TYPE_PLANETROMEO = 'external_source_type_planetromeo';
ExternalSourceTypeResolver.EXTERNAL_SOURCE_TYPE_IMGSRC = 'external_source_type_imgsrc';
ExternalSourceTypeResolver.EXTERNAL_SOURCE_TYPE_YOUTUBE = 'external_source_type_youtube';
ExternalSourceTypeResolver.EXTERNAL_SOURCE_TYPE_UNKNOWN = 'external_source_type_unknown';

export default ExternalSourceTypeResolver;