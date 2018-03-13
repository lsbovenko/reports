'use strict';

;(function ($, Vue, Utils, G) {

    var $date = $('#date'),
        hoursInDay = 8,
        minutsInHours = 60,
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

    var app = new Vue({
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
            }
        },
        computed: {
            totalAvailableMinutes: function totalAvailableMinutes() {
                var usersQty = this.filterParams.user_id ? 1 : this.users.length;
                return usersQty * hoursInDay * minutsInHours * this.quantityWorkedDays;
            },
            totalTrackedMinutes: function totalTrackedMinutes() {
                var total = 0;
                this.usersAndLoggedMinutes.forEach(function (user) {
                    total += parseInt(user.total_worked_minutes);
                });
                return total;
            },
            totalTrackedPercentByAvailable: function totalTrackedPercentByAvailable() {
                if (this.totalAvailableMinutes) {
                    return parseInt(100 * this.totalTrackedMinutes / this.totalAvailableMinutes);
                }
                return 0;
            },
            ratingUsers: function ratingUsers() {
                var result = [];
                this.usersAndLoggedMinutes.forEach(function (user) {
                    if (result.length < 5 && user.is_active && user.is_revenue_required) {
                        result.push(user);
                    }
                });
                return result;
            },
            selectedDates: function selectedDates() {
                return this.filterParams.dates.map(function (d) {
                    return d.toString();
                });
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
                        url: '/hours/filter',
                        data: sendData,
                        success: function success(response) {
                            that.usersAndLoggedMinutes = response.usersAndLoggedMinutes;
                            that.quantityWorkedDays = response.quantityWorkedDays;
                        }
                    });
                }
            }
        },
        filters: {
            formatMinutes: function formatMinutes(value) {
                return Utils.formatMinutes(value);
            }
        }
    });

    datepicker.selectDate(new Date(G.selectedDate)); //select current date by default

    $datepickerRange.on('change', function () {
        datepicker.update('range', $(this).is(':checked'));
    });
})(jQuery, Vue, Utils, window._globals || {});
