;(function ($, Vue, G) {

    const $date = $('#date'),
        $datepickerRange = $('#datepicker-range'),
        datepicker = $date.datepicker({
            maxDate: new Date(),
            minDate: new Date(G.minDate),
            inline: true,
            onSelect(dateStr, datesArray, inst) {
                if (inst.opts.range && inst.selectedDates.length === 2) {
                    app.filterParams.dates = inst.selectedDates;
                } else if (!inst.opts.range) {
                    app.filterParams.dates = inst.selectedDates;
                }
            }

        }).data('datepicker');

    $("#jquery-plugin-select1").select2({
        width: '100%',
        placeholder: "Выбрать",
    });

    $('#jquery-plugin-select1').on('select2:select', function (e) {
        app.filterParams.project_id = e.params.data.id
    });

    let app = new Vue({
            el: '#app',
            data: {
                revenue: G.revenue,
                filterParams: {
                    project_id: '',
                    dates: ['']
                }
            },
            watch: {
                filterParams: {
                    deep: true,
                    handler() {
                        //prevent from very first run
                        if (!this._filterParamsWathcerInit) {
                            this._filterParamsWathcerInit = true;
                            return;
                        }

                        let that = this,
                            sendData = {
                                project_id: this.filterParams.project_id,
                                dates: this.filterParams.dates.map(d => d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate())
                            };

                        $.ajax({
                            url: '/revenues/filter',
                            data: sendData,
                            success(response) {
                                that.revenue = response.revenue;
                            }
                        });
                    }
                },
            }
        });

    datepicker.selectDate(new Date(G.selectedDate)); //select current date by default

    $datepickerRange.on('change', function () {
        datepicker.update('range', $(this).is(':checked'));
    });

})(jQuery, Vue, window._globals || {});