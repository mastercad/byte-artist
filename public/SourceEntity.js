class SourceEntity {
    constructor() {
        let url;
        let binData;
        let id;
        let createCopy;
        let fileMimeType;
        let origEvent;
        let externalSource;

        this.setUrl = function(givenUrl) {url = givenUrl; return this;}
        this.getUrl = function() {return url;}

        this.setBinData = function(givenBinData) {binData = givenBinData; return this;}
        this.getBinData = function() {return binData;}

        this.setId = function(givenId) {id = givenId; return this;}
        this.getId = function() {return id;}

        this.setCreateCopy = function(givenCreateCopy) {createCopy = givenCreateCopy; return this;}
        this.isCreateCopy = function() {return createCopy;}

        this.setFileMimeType = function(givenFileMimeType) {fileMimeType = givenFileMimeType; return this;}
        this.getFileMimeType = function() {return fileMimeType;}

        this.setOrigEvent = function(givenOrigEvent) {origEvent = givenOrigEvent; return this;}
        this.getOrigEvent = function() {return origEvent;}

        this.setExternalSource = function(givenExternalSource) {externalSource = givenExternalSource; return this;}
        this.getExternalSource = function() {return externalSource;}
    }
}

export default SourceEntity;