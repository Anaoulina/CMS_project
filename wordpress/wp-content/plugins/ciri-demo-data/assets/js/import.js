(function ($) {
    var LaStudioImport = {
        complete: {
            posts: 0,
            media: 0,
            users: 0,
            comments: 0,
            terms: 0
        },

        updateDelta: function (type, delta) {
            this.complete[ type ] += delta;

            var self = this;
            requestAnimationFrame(function () {
                self.render();
            });
        },
        updateProgress: function ( type, complete, total ) {
            return;
            var text = complete + '/' + total;
            document.getElementById( 'completed-' + type ).innerHTML = text;
            total = parseInt( total );
            if ( 0 == total ) {
                total = 1;
            }
            var percent = parseInt( complete ) / total;
            document.getElementById( 'progress-' + type ).innerHTML = Math.round( percent * 100 ) + '%';
            document.getElementById( 'progressbar-' + type ).value = percent * 100;
        },
        render: function () {
            var types = Object.keys( this.complete );
            var complete = 0;
            var total = 0;

            for (var i = types.length - 1; i >= 0; i--) {
                var type = types[i];
                this.updateProgress( type, this.complete[ type ], this.data.count[ type ] );

                complete += this.complete[ type ];
                total += this.data.count[ type ];
            }

            this.updateProgress( 'total', complete, total );
        }
    };

    function InitEventSource( url, $parent ){
        var evtSource = new EventSource( url );
        evtSource.onmessage = function ( message ) {
            var data = JSON.parse( message.data );
            switch ( data.action ) {
                case 'updateDelta':
                    LaStudioImport.updateDelta( data.type, data.delta );
                    break;

                case 'ImportingProductContent':
                    console.log('ImportingProductContent');
                    break;

                case 'complete':
                    evtSource.close();
                    $('#live-log-status').show();
                    $('.demo-importer-message').html( '<div class="updated below-h2">' + LaStudioImport.data.strings.complete + '</div>');
                    $( '.demo-importer-message-info' ).removeClass('is-running');
                    $parent.find('img').css('opacity', '1');
                    $parent.find('.more-details').html('Demo Imported');
                    if($parent.hasClass('imported')){
                        $parent.addClass('rendered');
                        $parent.find('.importer-button').removeClass('button-primary importer-button').addClass('button-secondary');
                        $parent.find('.reimporter-button').removeClass('button-secondary').addClass('button-primary');
                    }
                    $parent.parent().find('.wrap-importer').removeClass('active');
                    $parent.removeClass('not-imported').addClass('imported active');

                    $parent.find('.spinner').removeAttr('style');
                    $parent.find('.theme-actions').removeAttr('style');
                    break;
            }
        };
        evtSource.addEventListener( 'log', function ( message ) {
            var data = JSON.parse( message.data),
                row = document.createElement('tr'),
                level = document.createElement( 'td'),
                message = document.createElement( 'td' );

            level.appendChild( document.createTextNode( data.level ) );
            row.appendChild( level );
            message.appendChild( document.createTextNode( data.message ) );
            row.appendChild( message );

            var $tbody = $('.demo-importer-message-info tbody');
            $tbody.append( row );
            $tbody.scrollTop($tbody.prop('scrollHeight'));
        });
    }

    $(document)
        .on('click','.wrap-importer .button',function(e){
            e.preventDefault();
            var $btn = $(this),
                $parent = $btn.closest('.wrap-importer');
            if(confirm($btn.data('title')) == true){

                // Reset response div content.
                $( '.demo-importer-message' ).empty();
                $( '.demo-importer-message-info' ).empty().addClass('is-running');

                $parent.find('.spinner').css('display', 'inline-block');
                $parent.find('.theme-actions').css('opacity','1');

                $.ajax({
                        method:     'POST',
                        url:        lastudio_importer.ajax_url,
                        data:       {
                            action : 'lastudio-importer',
                            id : $parent.data('demo-id'),
                            security : lastudio_importer.ajax_nonce,
                            args : {
                                content: true,
                                widget:  true,
                                slider: true,
                                option: true,
                                elementor: true,
                                fetch_attachments: true
                            }
                        }
                    })
                    .done( function( response ) {
                        if ( 'undefined' !== typeof response.data) {
                            LaStudioImport.data = response.data;
                            $('.demo-importer-message').html( '<div class="updated below-h2">Processing...</div><div style="display: inline-block;line-height: 30px;"><span class="spinner"></span>Please Wait...</div>' );
                            $('.demo-importer-message-info').html('<div id="live-log-status"><table id="import-log" class="widefat"><thead><tr><th>Type</th><th>Message</th></tr></thead><tbody></tbody></table></div>');
                            InitEventSource( response.data.url , $parent );
                        }
                        else {
                            $( '.demo-importer-message' ).append( '<div class="error below-h2">' + response + '</div>' );
                        }

                    })
                    .fail( function( error ) {
                        $( '.demo-importer-message' ).append( '<div class="error below-h2"> Error: ' + error.statusText + ' (' + error.status + ')' + '</div>' );
                    });
            }
        })

})(jQuery);