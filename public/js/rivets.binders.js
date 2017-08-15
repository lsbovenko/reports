;(function(rv, $){
    if (rv !== undefined) {

        if (typeof $ === 'function') {
            rv.binders['jquery-plugin-*'] = {
                block: true,
                bind: function(){
                    console.log(arguments, this);
                },
                routine: function(el, options) {
                    $(el)[this.args[0]](options);
                },
                unbind: function(el){
                    $(el)[this.args[0]]('destroy');
                }
            }
        }

        if ($.isFunction($.fn.parsley)) {
            rv.binders['parsley-*'] = function (el, value) {
                $(el).parsley().options[this.args[0]] = value;
            };
        }

    }
})(rivets, jQuery);