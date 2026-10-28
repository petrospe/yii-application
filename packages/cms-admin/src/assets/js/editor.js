(function () {
    function boot() {
        if (!document.querySelector('textarea.cms-editor') || typeof tinymce === 'undefined') {
            return;
        }
        tinymce.init({
            selector: 'textarea.cms-editor',
            height: 460,
            menubar: false,
            license_key: 'gpl',
            plugins: 'link lists image code table autoresize',
            toolbar: 'undo redo | blocks | bold italic | bullist numlist | link image | code',
            convert_urls: false,
            relative_urls: false,
            images_upload_handler: function (blobInfo) {
                return new Promise(function (resolve, reject) {
                    var data = new FormData();
                    data.append('file', blobInfo.blob(), blobInfo.filename());
                    if (window.cmsCsrfParam) {
                        data.append(window.cmsCsrfParam, window.cmsCsrfToken);
                    }
                    fetch(window.cmsUploadUrl, { method: 'POST', body: data, credentials: 'same-origin', headers: { Accept: 'application/json' } })
                        .then(function (response) { return response.json(); })
                        .then(function (json) {
                            if (json.location) {
                                resolve(json.location);
                            } else {
                                reject(json.error || 'Upload failed');
                            }
                        })
                        .catch(function () { reject('Upload failed'); });
                });
            }
        });
    }

    function start() {
        if (typeof tinymce !== 'undefined') {
            boot();
            return;
        }
        if (!document.querySelector('textarea.cms-editor')) {
            return;
        }
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js';
        script.onload = boot;
        document.head.appendChild(script);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
