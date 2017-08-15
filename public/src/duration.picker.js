;(function ($) {
    const formatPattern = /^(\d{1,2}(\s+)?[hH]?)?(\s+)?(\s+)?:?(\s+)?(\d{1,2}(\s+)?[mM]?)?$/,
        methods = {
            init(options) {

                options = $.extend({
                    onUpdate: null,

                }, options);

                return this.each(function () {

                    let $input = $(this);

                    if (!$input.is('input[type="text"]')) {
                        console.warn('duration is only applicable to text inputs');
                        return;
                    }

                    if (!$input.data('durationPicker')) {
                        $input.on('keydown', function (e) {
                            let value = $input.val() + e.key;

                            if (e.key.length === 1 && !value.match(formatPattern)) {
                                return false;
                            }
                        });

                        $input.on('change keyup', function () {
                            if ($.isFunction(options.onUpdate) && options.onUpdate !== null) {
                                options.onUpdate.call($input, methods.getFormatted.call($input, true));
                            }
                        });

                        $input.data('durationPicker', $input);
                    }
                });
            },
            getFormatted(asObject = false){
                let $input = $(this),
                    chars = $input.val().replace(/^\s+/, '').split(''),
                    hours = '',
                    minutes = '',
                    i = 0,
                    len = chars.length;

                for (; i < len; i++) {
                    if (chars[i].toLowerCase() === 'm') {
                        hours = 0;
                        i = 0;
                        break;
                    }
                    if (hours.length === 2 || ( $.inArray(chars[i], [' ', 'h', 'H']) !== -1 ) ) {
                        break;
                    }

                    chars[i].match(/[0-9]/) && (hours += chars[i]);
                }

                for (; i < len; i++) {
                    if (minutes.length === 2) {
                        break;
                    }

                    if ( $.inArray(chars[i], [' ', 'm', 'M']) !== -1 ) {
                        continue;
                    }

                    chars[i].match(/[0-9]/) && (minutes += chars[i]);
                }

                hours[0] === '0' ? (hours = hours.substring(1)) : '';
                minutes[0] === '0' ? (minutes = minutes.substring(1)) : '';

                hours = parseInt(hours || 0);
                minutes = parseInt(minutes || 0);

                if (minutes > 59) {
                    hours += parseInt(minutes / 60);
                    minutes = minutes % 60;
                }

                return asObject ? { hours, minutes } : ( hours + 'H ' + minutes + 'M' );
            },
            destroy(){
                let $input = $(this);
                $input.off();
            }
        };

    $.fn.duration = function (method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('durationPicker method is not exist: ' + method);
        }
    };

})(jQuery);