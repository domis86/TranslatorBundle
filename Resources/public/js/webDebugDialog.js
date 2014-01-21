function Domis86WebDebugDialogClass()
{
    this.isInitialized = false;

    var that = this;

    this.prepare = function() {
        jQuery('.domis86_translator_data_collector_clickable').click(function (event) {
            event.preventDefault();
            that.showDomis86WebDebugDialog();
        });
        updateTranslatorDataCollectorIconText();
    };

    this.showDomis86WebDebugDialog = function() {
        jQuery("#domis86_web_debug_dialog_container").dialog({
            modal: true,
            autoOpen: true,
            //title: 'Translation Messages',
            buttons: {
                "Close": function () {
                    jQuery(this).dialog("close");
                }
            },
            width: 1000,
            height: 500
        });

        if (!this.isInitialized) {
            initDomis86WebDebugDialog();
            this.isInitialized = true;
        }
    };

    function initDomis86WebDebugDialog() {
        // convert table to DataTable
        var tableWebDebugDialog = jQuery('#domis86_web_debug_dialog_table').dataTable( {
            "sScrollY": "300px",
            "bPaginate": false,
            "bScrollCollapse": false
        } );

        // Apply the jEditable handlers to the table
        tableWebDebugDialog.find('.messageTranslationContainer').editable(
            jQuery('#domis86_web_debug_dialog_container').data('submit_url'),
            { "callback": function( sValue, y ) {
                // update cell with new value
                jQuery(this).html(jQuery.parseJSON(sValue).value);
            },
            "submitdata": function ( value, settings ) {
                return {
                    "message_id": jQuery(this).data('message_id'),
                    "message_translation_locale": jQuery(this).data('message_translation_locale')
                };
            },
            "height": "14px",
            "width": "100%",
            type    : 'textarea',
            cancel    : 'Cancel',
            submit    : 'OK',
            indicator : 'wait...',
            tooltip   : 'Click to edit...',
            name : 'value',
            onblur : null // TODO: decide what to do on blur (maybe allow user to select if onblur saves or cancels and store this option in cookie/session/db?
    //        onblur : function() {
    //            var onblur_this = this;
    //            jQuery( "#domis86_web_debug_dialog_confirm_blur" ).dialog({
    //                resizable: false,
    //                height:140,
    //                modal: true,
    //                buttons: {
    //                    "OK": function() {
    //                        onblur_this.reset();
    //                        jQuery( this ).dialog( "close" );
    //                    },
    //                    Cancel: function() {
    //                        jQuery( this ).dialog( "close" );
    //                    }
    //                }
    //            });
    //            return true;
    //        }
        } );
    }


    function updateTranslatorDataCollectorIconText() {
        var dialogContainer = jQuery('#domis86_web_debug_dialog_container');
        jQuery('.domis86_translator_data_collector_icon_text').html(dialogContainer.data('count_translated_messages') + '/' + dialogContainer.data('count_used_messages') + ' translated');
        jQuery(".domis86_web_debug_dialog_toolbar_icon_img").hide();
        if (dialogContainer.data('count_translated_messages') === dialogContainer.data('count_used_messages')) {
            jQuery(".domis86_web_debug_dialog_toolbar_icon_img_warning").hide();
            jQuery(".domis86_web_debug_dialog_toolbar_icon_img_edit").show();
        }
    }

}

