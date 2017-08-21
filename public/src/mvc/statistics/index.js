;(function ($, Vue, G) {

    const $date = $('#date'),
        $datepickerRange = $('#datepicker-range'),
        datepicker = $date.datepicker({
            maxDate: new Date(),
            inline: true,
            onSelect(dateStr, datesArray, inst) {

                if (inst.opts.range && inst.selectedDates.length === 2) {
                    app.filterParams.dates = inst.selectedDates;
                } else if (!inst.opts.range) {
                    app.filterParams.dates = inst.selectedDates;
                }
            }

        }).data('datepicker'),
        nounEnding = function (number, endingArray) {
            let ending = '';

            number = number % 100;
            if (number >= 11 && number <= 19) {
                ending = endingArray[2];
            }
            else {
                let i = number % 10;
                switch (i) {
                    case (1):
                        ending = endingArray[0];
                        break;
                    case (2):
                    case (3):
                    case (4):
                        ending = endingArray[1];
                        break;
                    default:
                        ending = endingArray[2];
                }
            }
            return ending;

        },
        formatMinutes = function (minutes) {
            let hours = parseInt(minutes / 60);

            minutes = minutes % 60;

            return hours +
                ' ' +
                nounEnding(hours, ['час', 'часа', 'часов']) +
                ' ' +
                minutes +
                ' ' +
                nounEnding(minutes, ['минута', 'минуты', 'минут']);
        };

    //add additional properties
    if (G.users) {
        G.users.forEach(u => u.isActive = false);
    }

    let app = new Vue({
            el: '#app',
            data: {
                test: false,
                users: G.users || [],
                statistics: G.statistics || [],
                filterParams: {
                    user_id: null,
                    dates: ['']
                }
            },
            methods: {
                fullName(user) {
                    return user.last_name + ' ' + user.name;
                },
                inArray(needle, haystack) {
                    return $.inArray(needle, haystack) !== -1;
                },
                filterByUser(user, event) {
                    if (this.previousActiveUser) {
                        this.previousActiveUser.isActive = false;
                    }

                    user.isActive = true;
                    this.previousActiveUser = user;
                    this.filterParams.user_id = user.id;
                }
            },
            computed: {
                selectedDates(){
                    return this.filterParams.dates.map(d => d.toString());
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
                                user_id: this.filterParams.user_id,
                                dates: this.filterParams.dates.map(d => d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate())
                            };

                        $.ajax({
                            url: '/statistics/filter',
                            data: sendData,
                            success(statistics) {
                                that.statistics = statistics;
                            }
                        });

                        // if user is selected then we have to visualize data with chart
                        // thus we need to obtain corresponding data
                        if (this.filterParams.user_id) {
                            $.ajax({
                                url: '/statistics/chart-data',
                                data: sendData,
                                success(chartData) {
                                    chart.data = chartData;
                                    chart.update();
                                }
                            });
                        }
                    }
                }
            },
            filters: {
                formatMinutes
            }
        }),
        chart = new Chart($('#chart'), {
            type: 'bar',
            data: {},
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            min: 0,
                            max: 15,
                            stepSize: 1
                        },
                        stacked: true
                    }],
                    xAxes: [{
                        stacked: true,
                        barPercentage: 0.7
                    }]
                },
                tooltips: {
                    callbacks: {
                        label(descriptor){
                            return formatMinutes((descriptor.yLabel * 60).toFixed(2));
                        }
                    }
                },
                onClick(evt) {
                    if (!chart.getElementAtEvent(evt).length) return;

                    let label = chart.getElementAtEvent(evt)[0]._model.label,
                        clickedDate = new Date(label + '/' + (new Date()).getFullYear());

                    // disable range mode and select clicked date
                    datepicker.update('range', false);
                    datepicker.selectDate(clickedDate);
                    $datepickerRange.prop('checked', false);
                }
            }
        });

    datepicker.selectDate(new Date(G.selectedDate)); //select current date by default

    $datepickerRange.on('change', function () {
        datepicker.update('range', $(this).is(':checked'));
    });

})(jQuery, Vue, window._globals || {});