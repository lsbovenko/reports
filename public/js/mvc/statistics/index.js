'use strict';

;(function ($, Vue, Utils, G) {

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

    //add additional properties
    if (G.users) {
        G.users.forEach(function (u) {
            return u.isActive = false;
        });
    }

    var stats = void 0;
    var app = new Vue({
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
            fullName: function fullName(user) {
                return user.last_name + ' ' + user.name;
            },
            inArray: function inArray(needle, haystack) {
                return $.inArray(needle, haystack) !== -1;
            },
            filterByUser: function filterByUser(user, event) {
                if (this.previousActiveUser) {
                    this.previousActiveUser.isActive = false;
                }

                user.isActive = true;
                this.previousActiveUser = user;
                this.filterParams.user_id = user.id;
            },
            deleteReport: function deleteReport(report) {
                $.ajax({
                    url: '/reports/' + report.id,
                    method: 'DELETE',
                    success: function success(r) {
                        stats.forEach(function (stat, i) {
                            if (!stat[i]) {
                                return;
                            }
                            stat[i].tracked.forEach(function (item, index) {
                                if (item.id === report.id) {
                                    stat[i].tracked.splice(index, 1);
                                }
                            });
                            stat[i].untracked.forEach(function (item, index) {
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
            selectedDates: function selectedDates() {
                return this.filterParams.dates.map(function (d) {
                    return d.toString();
                });
            },
            totalInRange: function totalInRange() {
                var result = { tracked: 0, untracked: 0 };

                this.statistics.forEach(function (stats) {
                    stats.forEach(function (stat) {
                        result.tracked += stat.tracked_logged_minutes;
                        result.untracked += stat.untracked_logged_minutes;
                    });
                });

                return {
                    tracked: Utils.formatMinutes(result.tracked),
                    untracked: Utils.formatMinutes(result.untracked),
                    total: Utils.formatMinutes(result.tracked + result.untracked)
                };
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
                        user_id: this.filterParams.user_id,
                        dates: this.filterParams.dates.map(function (d) {
                            return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
                        })
                    };

                    $.ajax({
                        url: '/statistics/filter',
                        data: sendData,
                        success: function success(statistics) {
                            that.statistics = statistics;
                        }
                    });

                    // if user is selected then we have to visualize data with chart
                    // thus we need to obtain corresponding data
                    if (this.filterParams.user_id) {
                        $.ajax({
                            url: '/statistics/chart-data',
                            data: sendData,
                            success: function success(chartData) {
                                chart.data = chartData;
                                chart.update();
                            }
                        });
                    }
                }
            }
        },
        filters: {
            formatMinutes: function formatMinutes(value) {
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
                    label: function label(descriptor) {
                        return Utils.formatMinutes((descriptor.yLabel * 60).toFixed(2));
                    }
                }
            },
            onClick: function onClick(evt) {
                if (!chart.getElementAtEvent(evt).length) return;

                var label = chart.getElementAtEvent(evt)[0]._model.label,
                    clickedDate = new Date(label + '/' + new Date().getFullYear());

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
})(jQuery, Vue, Utils, window._globals || {});
//# sourceMappingURL=index.js.map