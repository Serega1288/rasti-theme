var $ = jQuery.noConflict();
$(document).ready(function ($) {
    var fcaImportButton = $('#fca-import-order');
    var progressBox = $('.fca_preloader');
    var resultDiv = $('.fca_ajax_result');

    if (fcaImportButton.length) {
        console.log(DataObject);
        fcaImportButton.on('click', function () {
            resultDiv.slideUp(100);
            progressBox.slideDown(250);
            getTheOrders();
        });
    }

    function getTheOrders() {
        $.ajax({
            type: 'POST',
            url: DataObject.ajaxUrl,
            data: {
                action: DataObject.importCall,
            },

            success: function (response) {
                progressBox.slideUp(250);
				resultDiv.slideDown(100);
				resultDiv.text(response['response_text']);
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    }
});