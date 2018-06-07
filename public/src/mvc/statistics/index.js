;(function ($, Vue, Utils, H, W, G) {



    const $date = $('#date'),
        strToDate = (str) => {
            let [y,m,d] = str.split('-'),
                date = new Date();
            date.setFullYear(y);
            date.setMonth(m - 1);
            date.setDate(d);
            return date;
        },
        $datepickerRange = $('#datepicker-range'),
        datepicker = $date.datepicker({
            maxDate: new Date(),
            minDate: new Date(G.minDate),
            inline: true,
            onSelect(dateStr, datesArray, inst) {
                if (inst.opts._ignoreOnSelect) {
                    return;
                }
                if (inst.opts.range && inst.selectedDates.length === 2) {
                    app.filterParams.dates = inst.selectedDates;
                } else if (!inst.opts.range) {
                    app.filterParams.dates = inst.selectedDates;
                }
            }

        }).data('datepicker');

    //add additional properties
    if (G.users) {
        G.users.forEach(u => u.isActive = false);
    }


    let stats;
    let app = new Vue({
            el: '#app',
            data: {
                users: G.users || [],
                statistics: stats = G.statistics || [],
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
                },
                deleteReport(report)
                {
                    $.ajax({
                        url: '/reports/' + report.id,
                        method: 'DELETE',
                        success(r) {
                            stats.forEach((stat, i) => {
                                if (!stat[i]) {
                                    return ;
                                }
                                stat[i].tracked.forEach((item, index) => {
                                    if (item.id === report.id) {
                                        stat[i].tracked.splice(index, 1);
                                    }
                                });
                                stat[i].untracked.forEach((item, index) => {
                                    if (item.id === report.id) {
                                        stat[i].untracked.splice(index, 1);
                                    }
                                });
                            });
                        }
                    });
                }
            },
            computed: {
                selectedDates(){
                    return this.filterParams.dates.map(d => d.toString());
                },
                totalInRange(){
                    let result = {tracked: 0, untracked: 0, overtime: 0};

                    this.statistics.forEach(stats => {
                        stats.forEach(stat => {
                            result.tracked += stat.tracked_logged_minutes;
                            result.untracked += stat.untracked_logged_minutes;
                            result.overtime += stat.total_overtime_minutes;
                        });

                    });

                    return {
                        tracked: Utils.formatMinutes(result.tracked, true),
                        untracked: Utils.formatMinutes(result.untracked, true),
                        total: Utils.formatMinutes(result.tracked + result.untracked, true),
                        total_overtime: Utils.formatMinutes(result.overtime, true),
                    };
                }
            },
            watch: {
                filterParams: {
                    deep: true,
                    handler() {
                        let sendData = {},
                            that = this;
                        //prevent from very first run
                        if (!this._filterParamsWathcerInit) {
                            this._filterParamsWathcerInit = true;
                            sendData = $.deparam(W.location.search.replace('?',''));
                            if (sendData.user_id) {
                                this.users.forEach(user => {
                                    if (+sendData.user_id === user.id) {
                                        user.isActive = true;
                                        this.previousActiveUser = user;
                                        this.filterParams.user_id = user.id;
                                    }
                                })
                            }
                            if (sendData.dates && sendData.dates.length > 0) {
                                datepicker.opts.range = sendData.dates.length > 1;
                                $datepickerRange.prop('checked', datepicker.opts.range);
                                datepicker.opts._ignoreOnSelect = true;

                                let dates = sendData.dates.map(strToDate);
                                datepicker.selectedDates = dates;
                                dates.forEach(date => {
                                    datepicker.selectDate(date);

                                });
                                this.filterParams.dates = dates;
                                datepicker.opts._ignoreOnSelect = false;
                            }
                            return
                        }

                        sendData = {
                            user_id: this.filterParams.user_id,
                            dates: this.filterParams.dates.map(d => d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate())
                        };
                        H.pushState(sendData, $(document).prop('title'), '?' + $.param(sendData));


                        $.ajax({
                            url: '/statistics/filter',
                            data: sendData,
                            success(statistics) {
                                that.statistics = statistics;

                            }
                        });

                        // if user is selected then we have to visualize data with chart
                        // thus we need to obtain corresponding data
                        if (sendData.user_id) {
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
                formatMinutes: value => {
                    return Utils.formatMinutes(value);
                }
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
                            return Utils.formatMinutes((descriptor.yLabel * 60).toFixed(2));
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

})(jQuery, Vue, Utils, History, window, window._globals || {});