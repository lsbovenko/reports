'use strict';

;(function ($, rv, G) {

    rv.formatters.not = function (value) {
        return !value;
    };

    var lang;
    var time;
    var placeholder;
    var message;
    var readMore;
    if ($.trim($('#lang').text()) == 'English') {
        lang = 'en';
        time = ['Time', 'h', 'm'];
        placeholder = 'Choose';
        message = 'Data has been sent.';
        readMore = ['Show', 'Hide'];
    } else {
        lang = 'ru';
        time = ['Время', 'ч', 'м'];
        placeholder = 'Выбрать';
        message = 'Данные были отправлены.';
        readMore = ['Показать', 'Скрыть'];
    }

    var $date = $('#date'),
        $form = $('#report-form'),
        $totaltime = $('#totalTime'),
        totalLoggedMinutes = 0,
        emptyRecord = function emptyRecord() {
        var isTracked = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

        return { name: '', description: '', workedTime: { hours: 0, minutes: 0 }, isTracked: isTracked, overtime: false };
    },
        datepicker = $date.datepicker({
        maxDate: new Date(),
        language: lang,
        onSelect: function onSelect(dateStr, date, inst) {
            inst.hide();
            var data = {date: $date.val()};
            $.ajax({
                url: '/statistics/logged-minutes',
                method: 'GET',
                data: data,
                success: function success(datas) {
                    totalLoggedMinutes = datas.totalLoggedMinutes;
                    formData.countTotalTime();
                },
            });
        }
    }).data('datepicker');


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
            placeholder: placeholder,
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
                            report.time = { hours: 0, minutes: 0 };
                            report._time = '';
                            report.description = '';
                        });

                        formData.reports.untracked = [];

                        $.amaran({
                            'message': message,
                            'position': 'bottom right'
                        });
                    },
                    error: function error(xhr) {
                        var data = JSON.parse(xhr.responseText);
                        if (data && data.error) {
                            window.alert(data.error);
                        }
                    }
                });
            }
        }
    };

    //rv.bind($form, formData);
    datepicker.selectDate(new Date()); //select current date by default
    rv.bind($form, formData);

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