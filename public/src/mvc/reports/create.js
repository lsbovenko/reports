;(function ($, rv, G) {

    rv.formatters.not = function (value) {
        return !value;
    };

    const $date = $('#date'),
        $form = $('#report-form'),
        emptyRecord = (isTracked = false) => {
            return {name: '', description: '', workedTime: {hours: 0, minutes: 0}, isTracked, overtime: false}
        },
        datepicker = $date.datepicker({
            maxDate: new Date(),
            onSelect(dateStr, date, inst) {
                inst.hide();
            }
        }).data('datepicker');

    let formData = {
        durationTooltip: {
            placement: 'top',
            html: true,
            title: `<ul class="list-unstyled text-justify">
                        <li><b>1 5</b> = 1 час 5 минут</li>
                        <li><b>0105</b> = 1 час 5 минут</li>
                        <li><b>1h5m</b> = 1 час 5 минут</li>
                        <li><b>5m</b> = 5 минут</li>
                        <li><b>0 5</b> = 5 минут</li>                        
                    </ul>`
        },
        reports: {
            tracked: [],
            untracked: []
        },
        durationPickerOptions: {
            onUpdate(duration) {
                let label = $(this)
                    .parent()
                    .parent()
                    .find('label');

                //make it red if time greater than 8 hours
                label.toggleClass('font-red', (duration.hours * 60 + duration.minutes) > 480);
                label.text('Время (' + duration.hours + 'ч:' + duration.minutes + 'м)');
            }
        },
        select2Options: {
            width: '100%',
            placeholder: "Выбрать",
            allowClear: true,
            ajax: {
                url: G.searchProjectUrl,
                processResults: function (data) {
                    return {
                        results: data.items,
                        pagination: {
                            more: false
                        }
                    };
                },
                delay: 450,
            },
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
            removeReport(e, scope) {
                scope.report.deleted = true;
            },
            sendNewReport() {

                if (!$form.parsley().isValid()) {
                    $form.parsley().validate();

                    return;
                }

                let sendData = {reports: [], date: $date.val()};

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
                    success() {
                        formData.reports.tracked.forEach(function (report) {
                            report.time = {hours: 0, minutes: 0};
                            report._time = '';
                            report.description = '';
                        });

                        formData.reports.untracked = [];

                        $.amaran({
                            'message': 'Данные были отправлены.',
                            'position': 'bottom right'
                        });
                    },

                    error(xhr) {
                        const data = JSON.parse(xhr.responseText);
                        if (data && data.error) {
                            window.alert(data.error);
                        }
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
            $('select.tracked').last().val(project.id).trigger('change');
        });
    }

    $('.readmore').readmore({
        maxHeight: 0,
        moreLink: '<a href="#">Показать</a>',
        lessLink: '<a href="#">Скрыть</a>'
    });

})(jQuery, rivets, _globals || {});