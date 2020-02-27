var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

;(function ($, Vue, H, W, G) {
    var $date = $('#date'),
        $datepickerRange = $('#datepicker-range'),
        $projectDropdown = $('#jquery-plugin-select1'),
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
        };

    $("#jquery-plugin-select1").select2({
        width: '100%',
        placeholder: "Choose",
    });

    $('#jquery-plugin-select1').on('select2:select', function (e) {
        app.filterParams.project_id = e.params.data.id
    });

    let app = new Vue({
        el: '#app',
        data: {
            pmStatistics: G.pmStatistics,
            filterParams: {
                project_id: '',
                dates: ['']
            }
        },
        watch: {
            filterParams: {
                deep: true,
                handler() {
                    var sendData = {},
                        _this = this,
                        that = this;
                    //prevent from very first run
                    if (!this._filterParamsWathcerInit) {
                        this._filterParamsWathcerInit = true;
                        sendData = $.deparam(W.location.search.replace('?', ''));
                        if (sendData.project_id) {
                            _this.filterParams.project_id = sendData.project_id;
                            $projectDropdown.val(sendData.project_id);
                            $projectDropdown.select2().trigger('change');
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
                        project_id: this.filterParams.project_id,
                        dates: this.filterParams.dates.map(d => d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate())
                    };

                    H.pushState(sendData, $(document).prop('title'), '?' + $.param(sendData));

                    $.ajax({
                        url: '/pm/filter',
                        data: sendData,
                        success(response) {
                            that.pmStatistics = response.pmStatistics;
                        }
                    });
                }
            },
        }
    });

    datepicker.selectDate(new Date(G.selectedDate)); //select current date by default

    $datepickerRange.on('change', function () {
        datepicker.update('range', $(this).is(':checked'));
    });

})(jQuery, Vue, History, window, window._globals || {});