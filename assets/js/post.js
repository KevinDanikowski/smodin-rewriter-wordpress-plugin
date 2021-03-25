/* global jQuery */
/* global ajaxurl */

(function($) {
    $(document).ready(function(){
        init();
    });

    function closeDialog(dialog){
        $(dialog).dialog( 'close' );
    }

    function initializeDialog(){
        $( '.smodinrewriter-dialog' ).dialog({
          modal: true,
          autoOpen: false,
          height: 400,
          width: 700,
          dialogClass: 'smodinrewriter-noclose',
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

        var sr_clipboard = new ClipboardJS('#smodinrewriter-clipboard', {
            container: document.getElementById('smodinrewriter-modal')
        });
        sr_clipboard.on('success', function(e) {
            // no message needs to be shown.
        });

        $('#smodinrewriter-button').on('click', function(e){
            e.preventDefault();
            $('.smodinrewriter-section').hide();

            var content = tinymce.get('content').getContent({format: 'raw'}).trim();

            $( '.smodinrewriter-dialog' ).dialog( 'option', 'height', 300 );
            $( '.smodinrewriter-dialog' ).dialog( 'option', 'width', 800 );

            $('#smodinrewriter-modal').dialog('open');
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
                    _action: 'pre-rewrite'
                },
                success: function(data){
                    if(! data) {
                        return;
                    }

                    if(! data.success){
                        $('.smodinrewriter-error').html(data.data.msg).show();
                        $('#smodinrewriter-modal').unlock();
                        return;
                    }

                    $( '.smodinrewriter-dialog' ).dialog( 'option', 'buttons', [{
                        text: config.i10n.confirm_button,
                        class: 'button button-primary smodinrewriter-confirm',
                        click: function() {
                            initRewriteWindow(content);
                        }
                      },
                      {
                        text: config.i10n.close_button,
                        click: function() {
                            closeDialog($(this));
                            initializeDialog();
                        }
                    }] );

                    $('.smodinrewriter-prewrite-disclaimer').addClass('notice').addClass('notice-info');
                    $('.smodinrewriter-prewrite-message').html(data.data.message);
                    $('.smodinrewriter-confirm').show();
                    $('#smodinrewriter-modal').unlock();
                }
            });
        });
    }

    function initRewriteWindow(content){
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
                    text: config.i10n.email_button,
                    class: 'smodinrewriter-link',
                    click: function() {
                        $('#smodinrewriter-clipboard').attr('data-clipboard-text', data.data.debug);
                        $('#smodinrewriter-clipboard').trigger('click');
                        location.href = 'mailto:' + data.data.mailto;
                    }
                  },
                  {
                    text: config.i10n.draft_button,
                    class: 'button button-secondary',
                    click: function() {
                        publishContent( tinymce.get('smodinrewriter-rewritten').getContent({format: 'raw'}).trim(), $(this), true );
                    }
                  },
                  {
                    text: config.i10n.publish_button,
                    class: 'button button-primary',
                    click: function() {
                        publishContent( tinymce.get('smodinrewriter-rewritten').getContent({format: 'raw'}).trim(), $(this), false );
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
    }

    function publishContent(content, dialog, asDraft){
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
                _action: 'publish',
                draft: asDraft,
                id: config.id
            },
            complete: function(data){
                window.onbeforeunload = null;
                $(window).off('beforeunload');
                location.href = config.url;
            }
        });
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
