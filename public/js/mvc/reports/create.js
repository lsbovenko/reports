'use strict';

;(function ($, rv, G) {

    rv.formatters.not = function (value) {
        return !value;
    };

    var $date = $('#date'),
        $form = $('#report-form'),
        emptyRecord = function emptyRecord() {
        var isTracked = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

        return { name: '', description: '', workedTime: { hours: 0, minutes: 0 }, isTracked: isTracked };
    },
        datepicker = $date.datepicker({
        maxDate: new Date(),
        onSelect: function onSelect(dateStr, date, inst) {
            inst.hide();
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
                $(this).parent().parent().find('label').text('Время (' + duration.hours + 'ч:' + duration.minutes + 'м)');
            }
        },
        select2Options: {
            tags: true,
            width: '100%',
            placeholder: "Выбрать/Добавить",
            allowClear: true
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
            },
            removeReport: function removeReport(e, scope) {
                scope.report.deleted = true;
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
                        isTracked: 1
                    });
                });

                formData.reports.untracked.forEach(function (r) {
                    if (r.deleted) return;

                    sendData.reports.push({
                        name: r.name,
                        time: r.workedTime,
                        description: r.description,
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
                            'message': 'Данные были отпралвены.',
                            'position': 'bottom right'
                        });
                    }
                });
            }
        }
    };

    rv.bind($form, formData);
    datepicker.selectDate(new Date()); //select current date by default

    /* Select default projects if exist */
    if (G.latestProjects && G.latestProjects.length) {
        G.latestProjects.forEach(function (project) {
            formData.reports.tracked.push(emptyRecord(true));
            $('select.tracked').last().val(project.name).trigger('change');
        });
    }
})(jQuery, rivets, _globals || {});
//# sourceMappingURL=create.js.map