;(function ($, Vue, G) {

    const $date = $('#date'),
        hoursInDay = 8,
        minutsInHours = 60,
        $datepickerRange = $('#datepicker-range'),
        datepicker = $date.datepicker({
            maxDate: new Date(),
            minDate: new Date(G.minDate),
            inline: true,
            onSelect(dateStr, datesArray, inst) {
                if (inst.opts.range && inst.selectedDates.length === 2) {
                    app.filterParams.dates = inst.selectedDates;
                } else if (!inst.opts.range) {
                    app.filterParams.dates = inst.selectedDates;
                }
            }

        }).data('datepicker'),
        nounEnding = function (number, endingArray) {
            let ending = '';

            number = number % 100;
            if (number >= 11 && number <= 19) {
                ending = endingArray[2];
            }
            else {
                let i = number % 10;
                switch (i) {
                    case (1):
                        ending = endingArray[0];
                        break;
                    case (2):
                    case (3):
                    case (4):
                        ending = endingArray[1];
                        break;
                    default:
                        ending = endingArray[2];
                }
            }
            return ending;

        },
        formatMinutes = function (minutes) {
            let hours = parseInt(minutes / 60);

            minutes = minutes % 60;

            return hours +
                ' ' +
                nounEnding(hours, ['час', 'часа', 'часов']) +
                ' ' +
                minutes +
                ' ' +
                nounEnding(minutes, ['минута', 'минуты', 'минут']);
        };

    let app = new Vue({
            el: '#app',
            data: {
                users: G.users || [],
                usersAndLoggedMinutes: G.usersAndLoggedMinutes,
                quantityWorkedDays: 1,
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
                }
            },
            computed: {
                totalAvailableMinutes(){
                    let usersQty = this.filterParams.user_id ? 1 : this.users.length;
                    return usersQty * hoursInDay * minutsInHours * this.quantityWorkedDays;
                },
                totalTrackedMinutes(){
                    let total = 0;
                    this.usersAndLoggedMinutes.forEach(user => {
                        total += parseInt(user.total_worked_minutes);
                    });
                    return total;
                },
                totalTrackedPercentByAvailable(){
                    if (this.totalAvailableMinutes) {
                        return parseInt((100 * this.totalTrackedMinutes) / this.totalAvailableMinutes);
                    }
                    return 0;
                },
                ratingUsers(){
                    let result = [];
                    this.usersAndLoggedMinutes.forEach(user => {
                        if (result.length < 5 && user.is_active && user.is_revenue_required) {
                            result.push(user);
                        }
                    });
                    return result;
                },
                selectedDates(){
                    return this.filterParams.dates.map(d => d.toString());
                },
            },
            watch: {
                filterParams: {
                    deep: true,
                    handler() {
                        //prevent from very first run
                        if (!this._filterParamsWathcerInit) {
                            this._filterParamsWathcerInit = true;
                            return;
                        }

                        let that = this,
                            sendData = {
                                user_id: this.filterParams.user_id,
                                dates: this.filterParams.dates.map(d => d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate())
                            };

                        $.ajax({
                            url: '/hours/filter',
                            data: sendData,
                            success(response) {
                                that.usersAndLoggedMinutes = response.usersAndLoggedMinutes;
                                that.quantityWorkedDays = response.quantityWorkedDays;
                            }
                        });
                    }
                }
            },
            filters: {
                formatMinutes
            }
        });

    datepicker.selectDate(new Date(G.selectedDate)); //select current date by default

    $datepickerRange.on('change', function () {
        datepicker.update('range', $(this).is(':checked'));
    });

})(jQuery, Vue, window._globals || {});