;(function ($, rv) {

    rv.formatters.not = function (value) {
        return !value;
    };

    const $date = $('#date'),
        $form = $('#report-form'),
        emptyRecord = (isTracked = false) => {
            return {name: '', description: '', workedTime: {hours: 0, minutes: 0}, isTracked}
        },
        datepicker = $date.datepicker({
            maxDate: new Date(),
            onSelect(dateStr, date, inst) {
                inst.hide();
            }
        }).data('datepicker');

    let formData = {
        test: true,
        reports: {
            tracked: [],
            untracked: []
        },
        durationPickerOptions: {
            onUpdate(duration) {
                $(this)
                    .parent()
                    .parent()
                    .find('label').text('Время (' + duration.hours + 'ч:' + duration.minutes + 'м)');

            }
        },
        select2Options: {
            tags: true,
            width: '100%',
            placeholder: "Выбрать/Добавить",
            allowClear: true
        },
        controller: {
            addMoreTracked() {
                formData.reports.tracked.push(emptyRecord(true));
            },
            addMoreUntracked() {
                formData.reports.untracked.push(emptyRecord());
            },
            updateTime(e, scope) {
                scope.report.workedTime = $(this).duration('getFormatted', true);
            },
            removeReport(e, scope){
                scope.report.deleted = true;
            },
            sendNewReport() {

                if (!$form.parsley().isValid()) {
                    $form.parsley().validate();

                    return;
                }

                let sendData = {reports: [], date: $date.val()};

                formData.reports.tracked.forEach(function (r) {
                    if (r.deleted) return ;

                    sendData.reports.push({
                        name: r.name,
                        time: r.workedTime,
                        description: r.description,
                        isTracked: 1
                    });
                });

                formData.reports.untracked.forEach(function (r) {
                    if (r.deleted) return ;

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
                    success() {
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