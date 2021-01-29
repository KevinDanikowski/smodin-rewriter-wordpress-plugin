/* global jQuery */
/* global ajaxurl */

(function($) {
    $(document).ready(function(){
        init();
    });

    function init() {
        $('#smodinrewriter-button').on('click', function(e){
            e.preventDefault();
            $('#smodinrewriter-modal .section').hide();
            $('#smodinrewriter-modal').dialog('open');
            $('#smodinrewriter-modal').lock();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    content: tinymce.activeEditor.getContent(),
                    nonce: config.ajax.nonce,
                    action: 'smodinrewriter',
                    _action: 'rewrite'
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
                    $('.smodinrewriter-success').html(data.data.rewritten).show();
                    //console.log(data);
                }
            });
        });


        $( '.smodinrewriter-dialog' ).dialog({
          modal: true,
          autoOpen: false,
          height: 400,
          width: 700,
          buttons: {
            Ok: function() {
              $( this ).dialog( 'close' );
            }
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
