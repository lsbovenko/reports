;(function ($, rv) {

    const $date = $('#date'),
        $form = $('#report-form'),
        emptyRecord = (isTracked = false) => {
            return {name: '', description: '', workedTime: {hours: 0, minutes: 0}, isTracked}
        },
        datepicker = $date.datepicker({
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
                let report = scope.report,
                    ref = report.isTracked ? scope.reports.tracked : scope.reports.untracked,
                    updated = [];

                $(this).closest('.report-container').remove();

                ref.splice(scope.index, 1); //suddenly it is buggy :(
            },
            sendNewReport() {

                if (!$form.parsley().isValid()) {
                    $form.parsley().validate();

                    return;
                }

                let sendData = {reports: []};

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

    window.tracked = formData.reports.tracked;
    rv.bind($form, formData);
    datepicker.selectDate(new Date()); //select current date by default

})(jQuery, rivets);