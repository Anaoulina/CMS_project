(function($, api) {
    "use strict";

    var depend_controls = {
        'page_preloader' : {
            controls: {
                'page_preloader_type': {
                    callback: function( to ) {
                        return !!to;
                    }
                },
                'page_preloader_custom': {
                    callback: function( to ) {
                        return !!to && ('custom' === api('page_preloader_type').get());
                    }
                }
            }
        },
        'page_preloader_type' : {
            controls: {
                'page_preloader_custom': {
                    callback: function( to ) {
                        return 'custom' === to;
                    }
                }
            }
        },
        'catalog_mode' : {
            controls: {
                'catalog_mode_price': {
                    callback: function( to ) {
                        return !!to;
                    }
                }
            }
        },
        'freeshipping_thresholds' : {
            controls: {
                'thresholds_text1': {
                    callback: function( to ) {
                        return !!to;
                    }
                },
                'thresholds_text2': {
                    callback: function( to ) {
                        return !!to;
                    }
                }
            }
        }
    }

    // Control visibility for default controls.
    $.each( depend_controls , function( settingId, o ) {
        api( settingId, function( setting ) {
            $.each( o.controls, function( controlId, conditions ) {

                api.control( controlId, function( control ) {
                    var visibility = function( to ) {
                        control.container.toggle( conditions.callback( to ) );
                    };
                    visibility( setting.get() );
                    setting.bind( visibility );
                });
            });
        });
    });


})(jQuery, wp.customize);