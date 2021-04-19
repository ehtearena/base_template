/*
 * jQuery File Upload Demo
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global $ */
function initiateUploaderX(el)
{
  $(el).fileupload({
    // Uncomment the following to send cross-domain cookies:
    //xhrFields: {withCredentials: true},
    url: '/upload/upload'
  }).on('fileuploadsubmit', function (e, data) {
    data.formData = '[{ name: '+el+'}]';
});

  // Enable iframe cross-domain access via redirect option:
  $(el).fileupload(
    'option',
    'redirect',
    window.location.href.replace(/\/[^/]*$/, '/cors/result.html?%s')
  ).on('fileuploadsubmit', function (e, data) {
    data.formData = '[{ name: '+el+'}]';
});


  if (window.location.hostname === 'msea.registrar.go.ke') {
    $(el).fileupload('option', {
      url: '/upload/upload',
      disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
      maxFileSize: 999000,
      acceptFileTypes: /(\.|\/)(txt|pdf|gif|jpe?g|png)$/i }).on('fileuploadsubmit', function (e, data) {
        data.formData = '[{ name: '+el+'}]';
});
    } else {
    // Load existing files:
    $(el).addClass('fileupload-processing');

    $.ajax({
      url: $(el).fileupload('option', 'url'),
      dataType: 'json',
      context: $(el)[0]
    }).always(function () {
        $(this).removeClass('fileupload-processing');
    }).done(function (result)
     {
       //$(this).fileupload('option', 'done').call(this, $.Event('done'), { result: result });
    });
  }
}


function initiateUploader()
{
  $('.fileupload').fileupload({
    // Uncomment the following to send cross-domain cookies:
    //xhrFields: {withCredentials: true},
    url: '/upload/upload/'
  });

  // Enable iframe cross-domain access via redirect option:
  $('.fileupload').fileupload(
    'option',
    'redirect',
    window.location.href.replace(/\/[^/]*$/, '/cors/result.html?%s')
  );

  if (window.location.hostname === 'msea.registrar.go.ke') {

    $('.fileupload').fileupload('option', {
      url: '/upload/upload/',
      // Enable image resizing, except for Android and Opera,
      // which actually support image resizing, but fail to
      // send Blob objects via XHR requests:
      disableImageResize: /Android(?!.*Chrome)|Opera/.test(
        window.navigator.userAgent
      ),
      maxFileSize: 999000,
      acceptFileTypes: /(\.|\/)(pdp|gif|jpe?g|png)$/i
    });
    // Upload server status check for browsers with CORS support:
    if ($.support.cors) {
      $.ajax({
        url: '//msea.registrar.go.ke/',
        type: 'HEAD'
      }).fail(function () {
        $('<div class="alert alert-danger"></div>')
          .text('Upload server currently unavailable - ' + new Date())
          .appendTo('.fileupload');
      });
    }
  } else {

    $('.fileupload').addClass('fileupload-processing');
    $.ajax({
      url: $('.fileupload').fileupload('option', 'url'),
      dataType: 'json',
      context: $('.fileupload')[0]
    })
      .always(function () {
        $(this).removeClass('fileupload-processing');
      })
      .done(function (result) {
        $(this).fileupload('option', 'done').call(this, $.Event('done'), { result: result });
      });
  }
}
