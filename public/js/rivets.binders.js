;(function(rv, $){
    if (rv !== undefined) {

        if (typeof $ === 'function') {
            rv.binders['jquery-plugin-*'] = function(el, options){
                $(el)[this.args[0]](options);
            }
        }

        if ($.isFunction($.fn.parsley)) {
            rv.binders['parsley-*'] = function (el, value) {
                $(el).parsley().options[this.args[0]] = value;
            };
        }

    }
})(rivets, jQuery);