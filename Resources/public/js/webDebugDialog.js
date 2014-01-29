function Domis86WebDebugDialogClass(aBackendMode, aAssetsBasePath) {
    var that = this;

    var backendMode = aBackendMode;
    var assetsBasePath = aAssetsBasePath;
    var isInitialized = false;
    var countMessages = 0;
    var countMessagesTranslated = 0;

    this.prepare = function () {
        updateTranslatorDataCollectorIconText();

        if (backendMode) {
            jQuery("#domis86_web_debug_dialog_container").show();
            this.showDomis86WebDebugDialog();
        }

        var clickable = jQuery('.domis86_translator_data_collector_clickable');
        if (backendMode) {
            clickable.click(function (event) {
                event.preventDefault();
                showHelpDialog();
            });
            return;
        }
        clickable.click(function (event) {
            event.preventDefault();
            that.showDomis86WebDebugDialog();
        });
    };

    this.showDomis86WebDebugDialog = function () {
        if (!backendMode) {
            jQuery("#domis86_web_debug_dialog_container").dialog({
                modal: true,
                autoOpen: true,
                buttons: {
                    "Help": function () {
                        showHelpDialog();
                    },
                    "Close": function () {
                        jQuery(this).dialog("close");
                    }
                },
                width: 1000,
                height: 500
            });
        }

        if (!isInitialized) {
            initDomis86WebDebugDialog();
            isInitialized = true;
        }
    };

    function initDomis86WebDebugDialog() {
        // add flags to table head
        jQuery('th.column_translation.add_flag').each(function () {
            var flagHtml = '<img src="' + assetsBasePath + 'external/images/flags/' + jQuery(this).data('locale') + '.png" />';
            jQuery(this).prepend(flagHtml);
        });

        // convert table to DataTable
        var tableWebDebugDialog = jQuery('table.domis86_web_debug_dialog_table').dataTable({
            "sScrollY": "300px",
            "bPaginate": false,
            "bScrollCollapse": false,
            "aaSorting": [ [1,'asc'], [2,'asc'] ]
        });

        // Apply the jEditable handlers to the table
        tableWebDebugDialog.find('.messageTranslationContainer').editable(
            jQuery('#domis86_web_debug_dialog_container').data('submit_url'),
            {
                "callback": function (sValue, y) {
                    var newValue = jQuery.parseJSON(sValue).value;
                    // update cell with new value
                    jQuery(this).html(newValue);
                    if (newValue.length > 0) {
                        jQuery(this).removeClass('empty');
                        jQuery(this).addClass('not_empty');
                    } else {
                        jQuery(this).removeClass('not_empty');
                        jQuery(this).addClass('empty');
                    }
                    updateTranslatorDataCollectorIconText();
                },
                "submitdata": function (value, settings) {
                    var messageData = jQuery(this).closest('td.column_translation');
                    return {
                        "message_name": messageData.data('message_name'),
                        "message_domain_name": messageData.data('message_domain_name'),
                        "message_translation_locale": messageData.data('message_translation_locale')
                    };
                },
                "height": "28px",
                "width": "100%",
                type: 'textarea',
                cancel: 'Cancel',
                submit: 'OK',
                indicator: 'saving...',
                tooltip: 'Click to edit translation in database', // TODO: better tooltip and placeholder
                placeholder: '( Click to edit )',
                name: 'message_translation',
                onblur: null // TODO: decide what to do on blur (maybe allow user to select if onblur saves or cancels and store this option in cookie/session/db?
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
            }
        );
    }


    function updateTranslatorDataCollectorIconText() {
        countMessages = 0;
        countMessagesTranslated = 0;

        // count translated Messages
        jQuery('tr.row_message').each(function () {
            countMessages++;
            var isMessageTranslated = true;
            jQuery(this).find('.column_translation').each(function () {
                var isLocaleTranslated = false;
                jQuery(this).find('.isContainer.not_empty').each(function () {
                    isLocaleTranslated = true;
                    return false;
                });
                if (!isLocaleTranslated) {
                    isMessageTranslated = false;
                    jQuery(this).addClass('is_not_translated');
                } else {
                    jQuery(this).removeClass('is_not_translated');
                }
            });
            if (isMessageTranslated) {
                countMessagesTranslated++;
                jQuery(this).removeClass('is_not_translated');
                jQuery(this).find('.column_translation').removeClass('is_not_translated');
            } else {
                jQuery(this).addClass('is_not_translated');
            }
        });

        // update icon and text in panel
        var helpInfo = '';
        if (backendMode) {
            helpInfo = ' (Click for help)'
        }
        jQuery('.domis86_translator_data_collector_icon_text').html(countMessagesTranslated + '/' + countMessages + ' translated' + helpInfo);
        jQuery(".domis86_web_debug_dialog_toolbar_icon_img").hide();
        if (countMessagesTranslated === countMessages) {
            jQuery(".domis86_web_debug_dialog_toolbar_icon_img_warning").hide();
            jQuery(".domis86_web_debug_dialog_toolbar_icon_img_edit").show();
        } else {
            jQuery(".domis86_web_debug_dialog_toolbar_icon_img_warning").show();
            jQuery(".domis86_web_debug_dialog_toolbar_icon_img_edit").hide();
        }
    }

    function showHelpDialog() {
        jQuery("#domis86_help_dialog").dialog({
            autoOpen: true,
            buttons: {
                "Close": function () {
                    jQuery(this).dialog("close");
                }
            },
            width: 400,
            height: 350
        });
    }
}

