import StringHelper from "./StringHelper.js";

/**
 * Investigate external source for given drop event.
 */
class ExternalSourceResolver {

    static resolve(url, sourceEntity) {
        let match = '';
        let regex = /src="(http[s]*:\/\/[www\.]*([^\/]*)[a-zA-Z0-9]{0,}\/.*?)"/g;

        while (match = regex.exec(url)) {
            // 1 => file url
            // 2 => domain
            switch (match[2].toLowerCase()) {
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
            }
            sourceEntity.setExternalSource(ExternalSourceResolver.EXTERNAL_SOURCE_UNKNOWN);
        }

        let imageUrl = '';
        if (false === (imageUrl = ExternalSourceResolver.handleDragAndDropEventWithImageSrc(sourceEntity.getOrigEvent()))
            && false === (imageUrl = ExternalSourceResolver.handleDragAndDropEventWithBackgroundUrl(sourceEntity.getOrigEvent()))
        ) {
            return false;
        }
        sourceEntity.setUrl(imageUrl);
        return ExternalSourceResolver.EXTERNAL_SOURCE_UNKNOWN;
    }

    /**
     * Returns expected image url from background image path.
     */
    static handleDragAndDropEventWithBackgroundUrl(event) {
        let cleanContent = StringHelper.cleanUpFromNotVisibileChars(
            event.originalEvent.dataTransfer.getData('text/html')
        );
        let imageSrcData = cleanContent.match(/background\-image\s*\:\s*url\("*(.*?)"*\)/);

        if (null !== imageSrcData
            && 0 < imageSrcData.length
        ) {
            let sourceUrlData = cleanContent.match(/a href="(.*?\.[a-zA-Z0-9]{1,})\/.*?"/);
            if (null !== sourceUrlData
                && 0 < sourceUrlData.length
            ) {
                return sourceUrlData[1]+'/'+imageSrcData[1];
            }
            return imageSrcData[1];
        }
        return false;

    }

    /**
     * Returns expected image from source path.
     */
    static handleDragAndDropEventWithImageSrc(event) {
        let imageSrcData = StringHelper.cleanUpFromNotVisibileChars(
            event.originalEvent.dataTransfer.getData('text/html')
        ).match(/ src.*?=.*?"([^"]*?)"/);

        if (null !== imageSrcData
            && 0 < imageSrcData.length
        ) {
            return imageSrcData[1];
        }
        return false;
    }
}

ExternalSourceResolver.EXTERNAL_SOURCE_PLANETROMEO = 'external_source_type_planetromeo';
ExternalSourceResolver.EXTERNAL_SOURCE_IMGSRC = 'external_source_type_imgsrc';
ExternalSourceResolver.EXTERNAL_SOURCE_YOUTUBE = 'external_source_type_youtube';
ExternalSourceResolver.EXTERNAL_SOURCE_UNKNOWN = 'external_source_type_unknown';

export default ExternalSourceResolver;