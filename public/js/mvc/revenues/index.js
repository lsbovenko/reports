'use strict';

;(function ($, Vue, G) {

    var $date = $('#date'),
        $datepickerRange = $('#datepicker-range'),
        datepicker = $date.datepicker({
        maxDate: new Date(),
        minDate: new Date(G.minDate),
        inline: true,
        onSelect: function onSelect(dateStr, datesArray, inst) {
            if (inst.opts.range && inst.selectedDates.length === 2) {
                app.filterParams.dates = inst.selectedDates;
            } else if (!inst.opts.range) {
                app.filterParams.dates = inst.selectedDates;
            }
        }
    }).data('datepicker');

    $("#jquery-plugin-select1").select2({
        width: '100%',
        placeholder: "Выбрать"
    });

    $('#jquery-plugin-select1').on('select2:select', function (e) {
        app.filterParams.project_id = e.params.data.id;
    });

    var app = new Vue({
        el: '#app',
        data: {
            fixedPriceRevenue: G.fixedPriceRevenue,
            notFixedPriceRevenue: G.notFixedPriceRevenue,
            filterParams: {
                project_id: '',
                dates: ['']
            }
        },
        computed: {
            totalRevenue: function totalRevenue() {
                return this.fixedPriceRevenue + this.notFixedPriceRevenue;
            }
        },
        watch: {
            filterParams: {
                deep: true,
                handler: function handler() {
                    //prevent from very first run
                    if (!this._filterParamsWathcerInit) {
                        this._filterParamsWathcerInit = true;
                        return;
                    }

                    var that = this,
                        sendData = {
                        project_id: this.filterParams.project_id,
                        dates: this.filterParams.dates.map(function (d) {
                            return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
                        })
                    };

                    $.ajax({
                        url: '/revenues/filter',
                        data: sendData,
                        success: function success(response) {
                            that.fixedPriceRevenue = response.fixedPriceRevenue;
                            that.notFixedPriceRevenue = response.notFixedPriceRevenue;
                        }
                    });
                }
            }
        }
    });

    datepicker.selectDate(new Date(G.selectedDate)); //select current date by default

    $datepickerRange.on('change', function () {
        datepicker.update('range', $(this).is(':checked'));
    });
})(jQuery, Vue, window._globals || {});