/* global jQuery */
/* global ajaxurl */

(function($) {
    $(document).ready(function(){
        init();
    });

    function closeDialog(dialog){
        $(dialog).dialog( 'close' );
        $(dialog).dialog( 'destroy' );
    }

    function initializeDialog(){
        $( '.smodinrewriter-dialog' ).dialog({
          modal: true,
          autoOpen: false,
          height: 400,
          width: 700,
          buttons: [{
              text: config.i10n.close_button,
              click: function() {
                $(this).dialog( 'close' );
              }
          }]
        });
    }

    function init() {
        initializeDialog();

        $('#smodinrewriter-button').on('click', function(e){
            e.preventDefault();
            $('.smodinrewriter-section').hide();

            var content = tinymce.get('content').getContent({format: 'raw'}).trim();

            $( '.smodinrewriter-dialog' ).dialog( 'option', 'height', 400 );
            $( '.smodinrewriter-dialog' ).dialog( 'option', 'width', 700 );

            $('#smodinrewriter-modal').dialog('open');
            $('#smodinrewriter-modal').lock();

            if(content.length === 0){
                $('.smodinrewriter-error').html(config.i10n.empty_content).show();
                $('#smodinrewriter-modal').unlock();
                return;
            }
            if(content.length > config.max){
                $('.smodinrewriter-error').html(config.i10n.content_too_long).show();
                $('#smodinrewriter-modal').unlock();
                return;
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    content: content,
                    lang: $('#smodinrewriter-lang').val(),
                    strength: $('#smodinrewriter-strength').val(),
                    nonce: config.ajax.nonce,
                    action: 'smodinrewriter',
                    _action: 'pre-rewrite'
                },
                success: function(data){
                    if(! data) {
                        return;
                    }

                    $('#smodinrewriter-modal').unlock();

                    if(! data.success){
                        $('.smodinrewriter-error').html(data.data.msg).show();
                        return;
                    }

                    $('.smodinrewriter-prewrite-message').html(data.data.message);
                    $('.smodinrewriter-confirm').show();

                    initRewriteWindow(content);
                }
            });
        });
    }

    function initRewriteWindow(content){
        $('#smodinrewriter-modal').on('click', '#smodinrewriter-rewrite', function(e){
            e.preventDefault();

            $('.smodinrewriter-section').hide();
            $('#smodinrewriter-modal').lock();

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    content: content,
                    lang: $('#smodinrewriter-lang').val(),
                    strength: $('#smodinrewriter-strength').val(),
                    nonce: config.ajax.nonce,
                    action: 'smodinrewriter',
                    _action: 'rewrite'
                },
                success: function(data){
                    if(! data) {
                        return;
                    }

                    $( '.smodinrewriter-dialog' ).dialog( 'option', 'height', 600 );
                    $( '.smodinrewriter-dialog' ).dialog( 'option', 'width', 1000 );

                    $('#smodinrewriter-modal').unlock();

                    if(! data.success){
                        $('.smodinrewriter-error').html(data.data.msg).show();
                        return;
                    }

                    $( '.smodinrewriter-dialog' ).dialog( 'option', 'buttons', [{
                        text: config.i10n.publish_button,
                        class: 'button button-primary',
                        click: function() {
                            publishContent( tinymce.get('smodinrewriter-rewritten').getContent({format: 'raw'}).trim(), $(this) );
                        }
                      },
                      {
                        text: config.i10n.close_button,
                        click: function() {
                            closeDialog($(this));
                            initializeDialog();
                        }
                    }] );

                    $('.smodinrewriter-success').show();

                    tinymce.execCommand('mceAddEditor', true, 'smodinrewriter-rewritten');
                    tinymce.get('smodinrewriter-rewritten').setContent(data.data.rewritten);
                                       
                }
            });
        });
    }

    function publishContent(content, dialog){
        closeDialog(dialog);
    }

})(jQuery, config);


(function ($) {
    $.fn.lock = function () {
        $(this).each(function () {
            var $this = $(this);
            var position = $this.css('position');

            if (!position) {
                position = 'static';
            }

            switch (position) {
                case 'absolute':
                case 'relative':
                    break;
                default:
                    $this.css('position', 'relative');
                    break;
            }
            $this.data('position', position);

            var width = $this.width(),
                height = $this.height();

            var locker = $('<div class="locker"></div>');
            locker.width(width).height(height);

            var loader = $('<div class="locker-loader"></div>');
            loader.width(width).height(height);

            locker.append(loader);
            $this.append(locker);
            $(window).resize(function () {
                $this.find('.locker,.locker-loader').width($this.width()).height($this.height());
            });
        });

        return $(this);
    };

    $.fn.unlock = function () {
        $(this).each(function () {
            $(this).find('.locker').remove();
            $(this).css('position', $(this).data('position'));
        });

        return $(this);
    };
})(jQuery);
