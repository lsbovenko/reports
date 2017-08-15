'use strict';

;(function ($, rv) {

    var $date = $('#date'),
        $form = $('#report-form'),
        emptyRecord = function emptyRecord() {
        return { name: '', description: '', workedTime: { hours: 0, minutes: 0 } };
    },
        datepicker = $date.datepicker({
        onSelect: function onSelect(dateStr, date, inst) {
            inst.hide();
        }
    }).data('datepicker');

    var formData = {
        test: true,
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
                formData.reports.tracked.push(emptyRecord());
            },
            addMoreUntracked: function addMoreUntracked() {
                formData.reports.untracked.push(emptyRecord());
            },
            updateTime: function updateTime(e, scope) {
                scope.report.workedTime = $(this).duration('getFormatted', true);
            },
            sendNewReport: function sendNewReport() {

                if (!$form.parsley().isValid()) {
                    $form.parsley().validate();

                    return;
                }

                var sendData = { reports: [] };

                formData.reports.tracked.forEach(function (r) {
                    sendData.reports.push({
                        name: r.name,
                        time: r.workedTime,
                        description: r.description,
                        isTracked: 1
                    });
                });

                formData.reports.untracked.forEach(function (r) {
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
                        formData.reports.tracked = [];
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
})(jQuery, rivets);
//# sourceMappingURL=create.js.map