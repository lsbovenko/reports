'use strict';

var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

;(function ($, Vue, Utils, H, W, rv, G) {
    var reportDurationData = {
        previousDuration: 0,
        duration: 0,
        totalDateDuration: 0,
        isDateDurationMoreThan15Hours: 0,
        isDateDurationMoreThan15HoursForDate: 0,
        durationTooltip: {
            placement: 'top',
            html: true,
            title: '<ul class="list-unstyled text-justify">\n' +
                '<li><b>1 5</b> = 1 \u0447\u0430\u0441 5 \u043C\u0438\u043D\u0443\u0442</li>\n' +
                '<li><b>0105</b> = 1 \u0447\u0430\u0441 5 \u043C\u0438\u043D\u0443\u0442</li>\n' +
                '<li><b>1h5m</b> = 1 \u0447\u0430\u0441 5 \u043C\u0438\u043D\u0443\u0442</li>\n' +
                '<li><b>5m</b> = 5 \u043C\u0438\u043D\u0443\u0442</li>\n' +
                '<li><b>0 5</b> = 5 \u043C\u0438\u043D\u0443\u0442</li>\n' +
                '</ul>',
        },
        durationPickerOptions: {
            onUpdate: function onUpdate(duration) {
                reportDurationData.duration = duration.hours * 60 + duration.minutes;

                reportDurationData.isDateDurationMoreThan15Hours =
                    reportDurationData.totalDateDuration - reportDurationData.previousDuration + reportDurationData.duration > 900;

                $(this).toggleClass('font-red', reportDurationData.isDateDurationMoreThan15Hours);
            }
        },
    };

    var $date = $('#date'),
        strToDate = function strToDate(str) {
        var _str$split = str.split('-'),
            _str$split2 = _slicedToArray(_str$split, 3),
            y = _str$split2[0],
            m = _str$split2[1],
            d = _str$split2[2],
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
        language: 'en',
        onSelect: function onSelect(dateStr, datesArray, inst) {
            app.$data.selectedProject = '';
            if (inst.opts._ignoreOnSelect) {
                return;
            }
            if (inst.opts.range && inst.selectedDates.length === 2) {
                app.filterParams.dates = inst.selectedDates;
            } else if (!inst.opts.range) {
                app.filterParams.dates = inst.selectedDates;
            }
        },
        onChangeView: function onChangeView(view) {
            if (view == 'days') {
                var $dates = $date.find('.datepicker--cell-day:not(.-other-month-)');
                var $firstDate = $dates[0];
                var $lastDate = $dates[$dates.length - 1];
                var year = $firstDate.getAttribute('data-year');
                var month = parseInt($firstDate.getAttribute('data-month')) + 1;
                var lastDay = $lastDate.getAttribute('data-date');
                var firstDate = strToDate(year + '-' + month + '-' + '01');
                var lastDate = strToDate(year + '-' + month + '-' + lastDay);
                var currentDate = new Date(G.selectedDate);

                datepicker.clear();
                datepicker.update('range', true);
                datepicker.selectDate(firstDate);
                if (lastDate < currentDate) {
                    datepicker.selectDate(lastDate);
                } else {
                    datepicker.selectDate(currentDate);
                }
                $datepickerRange.prop('checked', 'checked');
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
            selectedProject: '',
            isAllPeriodChecked: false,
            filterParams: {
                user_id: null,
                dates: [''],
                isMeeting: false,
                isEditReport: false,
            },
            editReportId: 0,
            editDateStr: 0,
            datapickerDateObj: 0,
            datapickerDateNew: 0,
            $currentModal: 0,
            editReportIdToBillable: 0,
        },
        methods: {
            fullName: function fullName(user) {
                return user.last_name + ' ' + user.name;
            },
            inArray: function inArray(needle, haystack) {
                return $.inArray(needle, haystack) !== -1;
            },
            getDayOfWeek: function getDay(date) {
                var d = new Date(date);
                var weekday = new Array(7);
                weekday[0] = "Sunday";
                weekday[1] = "Monday";
                weekday[2] = "Tuesday";
                weekday[3] = "Wednesday";
                weekday[4] = "Thursday";
                weekday[5] = "Friday";
                weekday[6] = "Saturday";
                return weekday[d.getDay()];
            },
            filterByUser: function filterByUser(user, event) {
                this.clearProjectFilter();
                if (this.previousActiveUser) {
                    this.previousActiveUser.isActive = false;
                }

                user.isActive = true;
                this.previousActiveUser = user;
                this.filterParams.user_id = user.id;
            },
            clearProjectFilter: function clearProjectFilter() {
                this.selectedProject = '';
            },
            filterByProject: function filterByProject(project) {
                this.selectedProject = (this.selectedProject == project) ? '' : project;
                this.filterParams.isMeeting = false;
                this.isAllPeriodChecked = false;
            },
            filterByMeeting: function filterByMeeting(isMeeting) {
                this.filterParams.isMeeting = isMeeting;
                this.isAllPeriodChecked = false;
            },
            getTimeAllPeriod: function getTimeAllPeriod() {
                var isMeeting = +this.filterParams.isMeeting;
                this.isAllPeriodChecked = true;
                $.ajax({
                    url: '/statistics/time-all-period',
                    type: 'get',
                    data: 'user_id=' + this.filterParams.user_id
                        + '&project=' + this.selectedProject
                        + '&is_meeting=' + isMeeting,
                    success: function success(result) {
                        $('#time-all-period').text(Utils.formatMinutes(result.workedMinutes, true));
                    },
                    complete: function() {
                        $('.loader-small').hide();
                    }
                });
            },
            reportContainsSelectedProject(projects) {
                if (this.selectedProject == '') {
                    return true;
                }
                for (var i = 0; i < projects.length; i++ ) {
                    if (projects[i].project_name == this.selectedProject) {
                        return true;
                    }
                }
                return false;
            },
            selectedProjectDailyTime: function selectedProjectDailyTime(projects) {
                var projectDailyTime = 0;
                var selectedProject = this.selectedProject;
                projects.forEach(function(project) {
                    if (project.project_name == selectedProject) {
                        projectDailyTime += project.total_minutes;
                    }
                });

                return projectDailyTime;
            },
            deleteReport: function deleteReport(report) {
                var vm = this;
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
                        vm.filterParams.isEditReport = !vm.filterParams.isEditReport;
                    }
                });
            },
            editReport: function editReport(report, totalLoggedMinutes) {
                reportDurationData.previousDuration = report.total_minutes;
                reportDurationData.duration = report.total_minutes;
                reportDurationData.totalDateDuration = totalLoggedMinutes;
                this.editReportId = report.id;
            },
            saveReport: function saveReport(report) {
                var idDescription = $('#' + report.id + '_description').val();
                var vm = this;
                if (!(report.total_minutes == reportDurationData.duration && report.descirption == idDescription)
                    && reportDurationData.duration
                    && !reportDurationData.isDateDurationMoreThan15Hours
                ) {
                    var sendData = {
                        worked_minutes: reportDurationData.duration,
                        description: idDescription,
                    };
                    $.ajax({
                        url: '/reports/' + report.id + '/update',
                        type: 'put',
                        data: sendData,
                        success: function success(result) {
                            vm.filterParams.isEditReport = !vm.filterParams.isEditReport;
                        },
                        error: function error(result) {
                            window.alert(result.responseJSON.error);
                        }
                    });
                } else {
                    $.amaran({
                        'message': 'Report time not updated - duration more than 15 hours, or duration not new, or duration not selected',
                        'position': 'bottom right',
                        'color': 'red'
                    });
                }
                this.editReportId = 0;
            },
            cancelReport: function cancelReport() {
                this.editReportId = 0;
            },
            editTotalMinutes: function editTotalMinutes(reportId) {
                return reportId + '_total_minutes';
            },
            editDescription: function editDescription(reportId) {
                return reportId + '_description';
            },
            getDate: function getDate(date, userId) {
                return date + '_' + userId;
            },
            editDate: function editDate(date, userId, totalDateDuration) {
                var $date = $('#' + date + '_' + userId);
                if (this.datapickerDateObj) {
                    this.datapickerDateObj.$el.hide();
                }
                var vm = this;
                var totalNewDateDuration = 0;
                this.datapickerDateObj = $date.datepicker({
                    maxDate: new Date(),
                    minDate: new Date(G.minDate),
                    language: 'en',
                    dateFormat: 'yyyy-mm-dd',
                    onSelect: function onSelect(formattedDate, dateObj, inst) {
                        vm.datapickerDateNew = formattedDate;
                        if (vm.datapickerDateNew != date && vm.datapickerDateNew) {
                            var sendData = {
                                date: vm.datapickerDateNew
                            };
                            $.ajax({
                                url: '/statistics/logged-minutes',
                                type: 'get',
                                data: sendData,
                                success: function success(result) {
                                    if (result.statistics) {
                                        totalNewDateDuration = result.statistics.total_logged_minutes;
                                    }
                                    reportDurationData.isDateDurationMoreThan15HoursForDate =
                                        totalDateDuration + totalNewDateDuration > 900;
                                }
                            });
                        }
                    }
                }).data('datepicker');
                this.datapickerDateObj.$el.show();
                this.datapickerDateObj.selectDate(strToDate(date));
                this.editDateStr = date;
            },
            saveDate: function saveDate(date, userId) {
                var vm = this;
                if (this.datapickerDateNew != date
                    && this.datapickerDateNew
                    && !reportDurationData.isDateDurationMoreThan15HoursForDate
                ) {
                    var sendData = {
                        user_id: userId,
                        old_date: date,
                        new_date: this.datapickerDateNew,
                    };
                    $.ajax({
                        url: '/reports/update-dates',
                        type: 'put',
                        data: sendData,
                        success: function success(result) {
                            vm.filterParams.isEditReport = !vm.filterParams.isEditReport;
                        },
                        error: function error(result) {
                            window.alert(result.responseJSON.error);
                        }
                    });
                } else {
                    $.amaran({
                        'message': 'Reports date not updated - duration more than 15 hours, or date not new, or date not selected',
                        'position': 'bottom right',
                        'color': 'red'
                    });
                }
                this.editDateStr = 0;
                this.datapickerDateObj.$el.hide();
            },
            editDuration: function editDuration() {
                var $reportDuration = $('.report-duration');
                $reportDuration.tooltip(reportDurationData.durationTooltip);
                $reportDuration.duration(reportDurationData.durationPickerOptions);
            },
            editReportToUnbillable: function editReportToUnbillable(reportId) {
                this.editReportId = 0;
                var vm = this;
                $.ajax({
                    url: '/reports/' + reportId + '/update-to-unbillable',
                    type: 'put',
                    success: function success(result) {
                        vm.filterParams.isEditReport = !vm.filterParams.isEditReport;
                    }
                });
            },
            editReportToBillable: function editReportToBillable(reportId) {
                this.editReportIdToBillable = reportId;
                this.editReportId = 0;
            },
            getModal: function getModal(reportId) {
                return reportId + '_modal';
            },
            saveModal: function saveModal(reportId) {
                var projectId = this.$currentModal.find('select option:selected').val();
                var isMeeting = this.$currentModal.find('input[type="checkbox"]:checked').length;
                var vm = this;
                if (projectId) {
                    var sendData = {
                        project_id: +projectId,
                        is_meeting: +isMeeting,
                    };
                    $.ajax({
                        url: '/reports/' + reportId + '/update-to-billable',
                        type: 'put',
                        data: sendData,
                        success: function success(result) {
                            vm.filterParams.isEditReport = !vm.filterParams.isEditReport;
                        },
                        error: function error(result) {
                            window.alert(result.responseJSON.error);
                        }
                    });
                } else {
                    $.amaran({
                        'message': 'Report type not updated - project not selected',
                        'position': 'bottom right',
                        'color': 'red'
                    });
                }
                this.editReportIdToBillable = 0;
            },
            cancelModal: function cancelModal() {
                this.editReportIdToBillable = 0;
            },
            select2Options: function select2Options() {
                $('.select-project').select2({
                    width: '100%',
                    placeholder: 'Choose',
                    allowClear: true
                });
            },
        },
        computed: {
            filterProjects: function filterProjects() {
                var trackedProjects = [];
                var untrackedProjects = [];
                this.statistics.forEach(function(statistic) {
                    statistic.forEach(function(dailyReport) {
                        dailyReport.tracked.forEach(function(project) {
                            if (!trackedProjects.includes(project.project_name)) {
                                trackedProjects.push(project.project_name);
                            }
                        });
                        dailyReport.untracked.forEach(function(project) {
                            if (!untrackedProjects.includes(project.task)) {
                                untrackedProjects.push(project.task);
                            }
                        });
                    });
                });

                return (trackedProjects.length == 0 && untrackedProjects.length == 0) ? [] : trackedProjects;
            },
            selectedProjectTotalTime: function selectedProjectTotalTime() {
                var projectTime = 0;
                var selectedProject = this.selectedProject;
                this.statistics.forEach(function(statistic) {
                    statistic.forEach(function(dailyReport) {
                        dailyReport.tracked.forEach(function(project) {
                            if (project.project_name == selectedProject) {
                                projectTime += project.total_minutes;
                            }
                        });
                    });
                });

                return Utils.formatMinutes(projectTime, true);
            },
            selectedDates: function selectedDates() {
                return this.filterParams.dates.map(function (d) {
                    return d.toString();
                });
            },
            totalInRange: function totalInRange() {
                var result = { tracked: 0, untracked: 0, overtime: 0 };

                this.statistics.forEach(function (stats) {
                    stats.forEach(function (stat) {
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
                    planned: Utils.formatMinutes(result.tracked + result.untracked - result.overtime, true)
                };
            }
        },
        updated: function () {
            this.$nextTick(function () {
                if (this.editReportIdToBillable) {
                    this.$currentModal = $('#' + this.editReportIdToBillable + '_modal');
                    this.select2Options();
                    var vm = this;
                    $.ajax({
                        url: '/reports/' + this.editReportIdToBillable,
                        type: 'get',
                        success: function success(result) {
                            if (result.report.project_id) {
                                vm.$currentModal.find('.select-project option[value="' + result.report.project_id + '"]')
                                    .prop('selected', 'selected').trigger('change');
                                vm.$currentModal.find('.checkbox').prop('checked', result.report.is_meeting);
                            } else {
                                vm.$currentModal.find('.select-project').val(null).trigger('change');
                                vm.$currentModal.find('.checkbox').prop('checked', false);
                            }
                        }
                    });
                }
            });
        },
        watch: {
            filterParams: {
                deep: true,
                handler: function handler() {
                    var _this = this;

                    var sendData = {},
                        that = this;
                    //prevent from very first run
                    if (!this._filterParamsWathcerInit) {
                        this._filterParamsWathcerInit = true;
                        sendData = $.deparam(W.location.search.replace('?', ''));
                        if (sendData.user_id) {
                            this.users.forEach(function (user) {
                                if (+sendData.user_id === user.id) {
                                    user.isActive = true;
                                    _this.previousActiveUser = user;
                                    _this.filterParams.user_id = user.id;
                                }
                            });
                        }
                        if (sendData.dates && sendData.dates.length > 0) {
                            datepicker.opts.range = sendData.dates.length > 1;
                            $datepickerRange.prop('checked', datepicker.opts.range);
                            datepicker.opts._ignoreOnSelect = true;

                            var dates = sendData.dates.map(strToDate);
                            datepicker.selectedDates = dates;
                            dates.forEach(function (date) {
                                datepicker.selectDate(date);
                            });
                            this.filterParams.dates = dates;
                            datepicker.opts._ignoreOnSelect = false;
                        }
                        return;
                    }

                    sendData = {
                        user_id: this.filterParams.user_id,
                        dates: this.filterParams.dates.map(function (d) {
                            return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
                        }),
                        is_meeting: +this.filterParams.isMeeting,
                    };
                    H.pushState(sendData, $(document).prop('title'), '?' + $.param(sendData));

                    $.ajax({
                        url: '/statistics/filter',
                        data: sendData,
                        success: function success(statistics) {
                            that.statistics = statistics;
                        }
                    });

                    // if user is selected then we have to visualize data with chart
                    // thus we need to obtain corresponding data
                    if (sendData.user_id) {
                        $.ajax({
                            url: '/statistics/chart-data',
                            data: sendData,
                            success: function success(chartData) {
                                chart.options.scales.yAxes[0].ticks.max = chartData.maxTime;
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
            },
            formatMinutesShort: function formatMinutesShort(value) {
                var hours = parseInt(value / 60).toString();
                var minutes = (value % 60).toString();

                return hours + 'h' + minutes + 'm';
            },
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
            annotation: {
                annotations: [{
                    type: 'line',
                    mode: 'horizontal',
                    borderDash: [6, 3],
                    scaleID: 'y-axis-0',
                    value: 8,
                    borderColor: '#337ab7',
                    borderWidth: 1,
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

    $(document).mouseup(function (e) {
        var containerDatapicker = $(app.datapickerDateObj.$el);
        var containerSaveButton = containerDatapicker.parent().find($('.label-success'));

        if ((!containerDatapicker.is(e.target) && containerDatapicker.has(e.target).length === 0)
            && (!containerSaveButton.is(e.target) && containerSaveButton.has(e.target).length === 0)
        ) {
            containerDatapicker.hide();
            app.editDateStr = 0;
        }
    });

    datepicker.selectDate(new Date(G.selectedDate)); //select current date by default

    $datepickerRange.on('change', function () {
        datepicker.update('range', $(this).is(':checked'));
    });
})(jQuery, Vue, Utils, History, window, rivets, window._globals || {});