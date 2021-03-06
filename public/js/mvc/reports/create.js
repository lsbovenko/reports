'use strict';

function removeTask(event, element) {
    event.preventDefault();
    let id = element.closest('.list-group-item').val();
    if (id) {
        $.ajax({
            url: '/tasks-history/' + id,
            method: 'DELETE',
            success: function success(data) {
                _globals.latestTaskNames = $.grep(_globals.latestTaskNames, function (task) {
                    return task['id'] != id;
                });
                _globals.taskNames = $.grep(_globals.taskNames, function (task) {
                    return task['id'] != id;
                });
                if (_globals.taskNames.length == 0) {
                    $('.icon-edit').each(function () {
                        $(this).hide();
                    });
                    element.closest('.modal-tasks').hide();
                }
                $.amaran({
                    'message': data.success,
                    'position': 'bottom right'
                });
                $('.untracked').each(function () {
                    $(this).find('option').each(function () {
                        if ($(this).val() == id) {
                            $(this).remove();
                        }
                    });
                });
                $('.tasks').each(function () {
                    $(this).find('li').each(function () {
                        if ($(this).val() == id) {
                            $(this).remove();
                        }
                    });
                    $(this).closest('.modal-body').each(function () {
                        $(this).css('height', getHeight() + 'px');
                    });
                });
            },
            error: function error(xhr) {
                let data = JSON.parse(xhr.responseText);
                if (data && data.error) {
                    window.alert(data.error);
                }
            }
        });
    }
}

function getHeight() {
    let height = 256;
    if (_globals.taskNames.length < 5) {
        height = 51 + 41 * _globals.taskNames.length;
    }

    return height;
}

;(function ($, rv, G) {

    $('#widgets').show();

    rv.formatters.not = function (value) {
        return !value;
    };

    var workedMinutes;
    var plannedMinutes;

    var time = ['Time', 'h', 'm'];
    var readMore = ['Show', 'Hide'];

    var $date = $('#date'),
        $form = $('#report-form'),
        $table = $('#report-table'),
        $totaltime = $('#totalTime'),
        totalLoggedMinutes = 0,
        emptyRecord = function emptyRecord() {
        var isTracked = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

        return { name: '', isMeeting: false, description: '', workedTime: { hours: 0, minutes: 0 }, isTracked: isTracked, overtime: false };
    },
        datepicker = $date.datepicker({
        maxDate: new Date(),
        language: 'en',
        onSelect: function onSelect(dateStr, date, inst) {
            inst.hide();
            var data = {date: $date.val()};
            $.ajax({
                url: '/statistics/logged-minutes',
                method: 'GET',
                data: data,
                success: function success(datas) {
                    if (datas.statistics) {
                        totalLoggedMinutes = datas.statistics.total_logged_minutes;
                        tableData.totalTime = Utils.formatMinutes(totalLoggedMinutes,true);
                        tableData.tableTracked = datas.statistics.tracked;
                        tableData.tableUntracked = datas.statistics.untracked;
                    } else {
                        totalLoggedMinutes = 0;
                        tableData.totalTime = '';
                        tableData.tableTracked = [];
                        tableData.tableUntracked = [];
                    }
                    formData.countTotalTime();
                },
            });
            datepickerMonth.date = date;
            datepickerMonth.selectDate(date);
        }
    }).data('datepicker');

    var $dateMonth = $('#date_month'),
        defaultDate = new Date(),
        $buttonNext = $('#button-next'),
        $progressBarRight = $('#progress_bar_right'),
        $remainTime = $('#remain_time'),
        xhr,
        datepickerMonth = $dateMonth.datepicker({
            view: 'months',
            minView: 'months',
            dateFormat: 'MM, yyyy',
            maxDate: defaultDate,
            language: 'en',
            onSelect: function onSelect(dateStr, date, inst) {
                inst.hide();
                if (date.getFullYear() == defaultDate.getFullYear() && date.getMonth() == defaultDate.getMonth()) {
                    $buttonNext.hide();
                } else {
                    $buttonNext.show();
                }
                if (xhr) {
                    xhr.abort();
                }
                xhr = $.ajax({
                    url: '/reports/month-stats',
                    method: 'GET',
                    data: {date: date.getFullYear() + '-' + (date.getMonth() + 1)},
                    success: function success(result) {
                        workedMinutes = result.workedMinutes;
                        plannedMinutes = result.plannedMinutes;
                        $('#progress_time').text(result.formattedWorkedTime + ' / ' + result.formattedPlannedTime);
                        $('#progress_bar_left').attr('style', 'width:' + result.percent + '%');
                        if (result.isExistsOvertime) {
                            $progressBarRight.show();
                            $progressBarRight.attr('style', 'width:' + (100 - result.percent) + '%');
                            $remainTime.text('Overtime: ' + result.formattedDifferenceTime);
                        } else {
                            $progressBarRight.hide();
                            $remainTime.text('Left: ' + result.formattedDifferenceTime);
                        }
                    },
                });
            }
        }).data('datepicker');

    $('#button-prev').on('click', function() {
        var datepickerDate = datepickerMonth.selectedDates;
        var currentDate = new Date(datepickerDate[0].getFullYear(), datepickerDate[0].getMonth() - 1);
        datepickerMonth.date = currentDate;
        datepickerMonth.selectDate(currentDate);
        $buttonNext.show();
    });

    $buttonNext.on('click', function() {
        var datepickerDate = datepickerMonth.selectedDates;
        var currentDate = new Date(datepickerDate[0].getFullYear(), datepickerDate[0].getMonth() + 1);
        datepickerMonth.date = currentDate;
        datepickerMonth.selectDate(currentDate);
        if (currentDate.getFullYear() == defaultDate.getFullYear() && currentDate.getMonth() == defaultDate.getMonth()) {
            $buttonNext.hide();
        }
    });

    $('#salary_button').on('click', function() {
        $('#salary_calculator').modal('show');
        $('#month_salary').val('');
        $('#earned_money').text('');
    });

    $('#month_salary').on('keyup', function() {
        var salary = $('#month_salary').val();
        var regExp = /^\d+$/;

        if (regExp.test(salary)) {
            $('#salary_error').hide();
            $('#earned_money').text((salary * workedMinutes / plannedMinutes).toFixed(2) + ' $');
        } else {
            $('#salary_error').show();
            $('#earned_money').text('');
        }
    });

    var formData = {
        durationTooltip: {
            placement: 'top',
            html: true,
            title: '<ul class="list-unstyled text-justify">\n                        <li><b>1 5</b> = 1 \u0447\u0430\u0441 5 \u043C\u0438\u043D\u0443\u0442</li>\n                        <li><b>0105</b> = 1 \u0447\u0430\u0441 5 \u043C\u0438\u043D\u0443\u0442</li>\n                        <li><b>1h5m</b> = 1 \u0447\u0430\u0441 5 \u043C\u0438\u043D\u0443\u0442</li>\n                        <li><b>5m</b> = 5 \u043C\u0438\u043D\u0443\u0442</li>\n                        <li><b>0 5</b> = 5 \u043C\u0438\u043D\u0443\u0442</li>                        \n                    </ul>'
        },
        reports: {
            tracked: [],
            untracked: []
        },
        durationPickerOptions: {
            onUpdate: function onUpdate(duration) {
                var label = $(this).parent().parent().find('label');

                //make it red if time greater than 8 hours
                label.toggleClass('font-red', duration.hours * 60 + duration.minutes > 480);
                label.text(time[0] + ' (' + duration.hours + time[1] + ':' + duration.minutes + time[2] + ')');
            }
        },
        select2Options: {
            width: '100%',
            placeholder: 'Choose',
            allowClear: true,
            ajax: {
                url: G.searchProjectUrl,
                processResults: function processResults(data) {
                    return {
                        results: data.items,
                        pagination: {
                            more: false
                        }
                    };
                },
                delay: 450
            }
        },
        select2OptionsUntracked: {
            width: '100%',
            placeholder: 'Choose',
            allowClear: true,
            tags: true,
        },
        totalTime: Utils.formatMinutes(totalLoggedMinutes,true),
        countTotalTime: function (){
            var time = totalLoggedMinutes;
            formData.reports.tracked.forEach(function (r) {
                if (r.deleted) return;
                time += r.workedTime.hours * 60 + r.workedTime.minutes;
            });
            formData.reports.untracked.forEach(function (r) {
                if (r.deleted) return;
                time += r.workedTime.hours * 60 + r.workedTime.minutes;
            });
            formData.totalTime = Utils.formatMinutes(time,true);
            $totaltime.parent().toggleClass('font-red',  time > 900);
        },
        controller: {
            addMoreTracked: function addMoreTracked() {
                formData.reports.tracked.push(emptyRecord(true));
            },
            addMoreUntracked: function addMoreUntracked() {
                formData.reports.untracked.push(emptyRecord());
                formData.controller.updateUntracked($(this));
                formData.controller.updateTasks($(this));
            },
            updateUntracked: function updateUntracked(element) {
                if (G.latestTaskNames && G.latestTaskNames.length) {
                    let $untracked = element.closest('.form-group').prev('.root').find('.untracked');
                    $untracked.append('<option></option>');
                    G.latestTaskNames.forEach(function (task) {
                        $untracked.append('<option value="' + task['id'] + '">' + task['task'] + '</option>');
                    });
                }
            },
            updateTasks: function updateTasks(element) {
                if (G.taskNames && G.taskNames.length) {
                    let $tasks = element.closest('.form-group').prev('.root').find('.tasks');
                    $tasks.closest('.modal-body').css('height', getHeight() + 'px');
                    G.taskNames.forEach(function (task) {
                        $tasks.append('<li class="list-group-item" value="' + task['id'] + '">' + task['task'] +
                            '<button onclick="removeTask(event, $(this))" class="fa fa-window-close font-red btn-link cur-pointer float-right" title="Remove"></button></li>');
                    });
                } else {
                    element.closest('.form-group').prev('.root').find('.icon-edit').hide();
                }
            },
            updateTime: function updateTime(e, scope) {
                scope.report.workedTime = $(this).duration('getFormatted', true);
                formData.countTotalTime();
            },
            removeReport: function removeReport(e, scope) {
                scope.report.deleted = true;
                formData.countTotalTime();
            },
            sendNewReport: function sendNewReport() {

                if (!$form.parsley().isValid()) {
                    $form.parsley().validate();

                    return;
                }

                var sendData = { reports: [], date: $date.val() };

                formData.reports.tracked.forEach(function (r) {
                    if (r.deleted) return;

                    sendData.reports.push({
                        name: r.name,
                        time: r.workedTime,
                        isMeeting: +r.isMeeting,
                        description: r.description,
                        isOvertime: +r.overtime,
                        isTracked: 1
                    });
                });

                formData.reports.untracked.forEach(function (r) {
                    if (r.deleted) return;

                    sendData.reports.push({
                        name: r.name,
                        time: r.workedTime,
                        description: r.description,
                        isOvertime: +r.overtime,
                        isTracked: 0
                    });
                });

                $.ajax({
                    url: '/reports/store',
                    method: 'POST',
                    data: sendData,
                    success: function success() {
                        formData.reports.tracked.forEach(function (report) {
                            report.workedTime = { hours: 0, minutes: 0 };
                            report._time = '';
                            report.description = '';
                        });

                        formData.reports.untracked = [];

                        $.amaran({
                            'message': 'Data has been sent.',
                            'position': 'bottom right'
                        });

                        var datepickerDate = datepicker.selectedDates;
                        datepicker.selectDate(datepickerDate[0]);

                        $('.tracked-label').each(function() {
                            $(this).text('Time (HHMM)').removeClass('font-red');
                        });
                    },
                    error: function error(xhr) {
                        var data = JSON.parse(xhr.responseText);
                        if (data && data.error) {
                            window.alert(data.error);
                        }
                    }
                });
            },
            showTasksModal: function showTasksModal(event) {
                event.preventDefault();
                formData.controller.hideTasksModals();
                $(this).closest('.form-group').find('.modal-tasks').show();
            },
            hideTasksModal: function hideTasksModal(event) {
                event.preventDefault();
                formData.controller.hideTasksModals();
            },
            hideTasksModals: function hideTasksModals() {
                $('.modal-tasks').each(function () {
                    $(this).hide();
                });
            },
        },
    };

    /* Hide tasks modals when press 'Esc' button */
    $(document).keydown(function (event) {
        if (event.which == 27) {
            formData.controller.hideTasksModal(event);
        }
    });

    /* Hide tasks modals when click outside the modals */
    $(document).on('click', function (event) {
        $('.modal-tasks').each(function () {
            if ($(this).is(':visible') && $(event.target).closest($(this)).length === 0 && $(event.target).closest('.icon-edit').length === 0) {
                formData.controller.hideTasksModal(event);
            }
        });
    });

    var tableData = {
        totalTime: '',
        tableTracked: [],
        tableUntracked: [],
    };

    rv.formatters.length = function (value) {
        return value.length;
    };

    //rv.bind($form, formData);
    datepicker.selectDate(new Date()); //select current date by default
    rv.bind($form, formData);
    rv.bind($table, tableData);

    /* Select default projects if exist */
    if (G.latestProjects && G.latestProjects.length) {
        G.latestProjects.forEach(function (project) {
            formData.reports.tracked.push(emptyRecord(true));
            $('select.tracked').last().val(project.id).trigger('change');
        });
    }

    $('.readmore').readmore({
        maxHeight: 0,
        moreLink: '<a href="#">' + readMore[0] + '</a>',
        lessLink: '<a href="#">' + readMore[1] + '</a>'
    });
})(jQuery, rivets, _globals || {});