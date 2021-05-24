class FileDownloadManager {

    static download(sourceEntity, callbackFunc) {
        let reader = new FileReader();

        reader.onload = function(event) {
            sourceEntity.setUrl(undefined)
                .setBinData(event);

            callbackFunc(event);
        };

        reader.readAsDataURL(sourceEntity.getUrl());
    }

    static downloadFromExternal(sourceEntity, callbackFunc) {
        if (true !== sourceEntity.isCreateCopy()) {
            console.log("External File shouldnt downloaded!");
            return callbackFunc(false);
        }
        /*
        $.get('blob:'+sourceEntity.getUrl()).then(function(data) {
            let blob = new Blob([data], {type: sourceEntity.getMimeType()});
        });
        */
        /*
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function(){
            if (this.readyState == 4
                && this.status == 200
            ){
                //this.response is what you're looking for
                handler(this.response);
                console.log(this.response, typeof this.response);
                var url = window.URL || window.webkitURL;
                sourceEntity.setBinData(url.createObjectURL(this.response));
            }
        }
        console.log(sourceEntity.getUrl());
        xhr.open('GET', sourceEntity.getUrl());
        xhr.responseType = 'blob';
        xhr.send();
        */
       /*
        jQuery.ajax({
            url:sourceEntity.getUrl(),
            cache:false,
            xhrFields:{
                responseType: 'blob'
            },
            success: function(data){
                var url = window.URL || window.webkitURL;
                sourceEntity.setBinData(url.createObjectURL(data));
            },
            error:function(){

            }
        });
        */
        var xhr = new XMLHttpRequest();
        xhr.open('POST', sourceEntity.getUrl(), true);
        xhr.responseType = 'arraybuffer';
        xhr.onload = function () {
            if (this.status === 200) {
                var filename = "";
                var disposition = xhr.getResponseHeader('Content-Disposition');
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    var matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                }
                var type = xhr.getResponseHeader('Content-Type');

                var blob;
                if (typeof File === 'function') {
                    try {
                        blob = new File([this.response], filename, { type: type });
                    } catch (e) { /* Edge */ }
                }
                if (typeof blob === 'undefined') {
                    blob = new Blob([this.response], { type: type });
                }

                if (typeof window.navigator.msSaveBlob !== 'undefined') {
                    // IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
                    window.navigator.msSaveBlob(blob, filename);
                } else {
                    var URL = window.URL || window.webkitURL;
                    sourceEntity.setBinData(URL.createObjectURL(blob));
/*
                    if (filename) {
                        // use HTML5 a[download] attribute to specify filename
                        var a = document.createElement("a");
                        // safari doesn't support this yet
                        if (typeof a.download === 'undefined') {
                            window.location = downloadUrl;
                        } else {
                            a.href = downloadUrl;
                            a.download = filename;
                            document.body.appendChild(a);
                            a.click();
                        }
                    } else {
                        window.location = downloadUrl;
                    }
*/
                    setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 100); // cleanup
                }
            }
        };
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
//        xhr.send($.param(params));
        xhr.send();
    }
}

export default FileDownloadManager;