@extends('layouts.app')

@section('title', trans('reports.hours'))

@section('page_css')
    <link rel="stylesheet" href="{{URL::asset('css/datepicker.min.css')}}">
@endsection

@section('page_js')
    <script src="{{asset('js/vue' . (config('app.env') !== 'local' ? '.min' : '') . '.js' )}}"></script>
    <script src="{{URL::asset('js/datepicker.min.js')}}"></script>
    <script src="{{ asset('js/i18n/datepicker.en.js') }}"></script>
    <script src="http://www.chartjs.org/samples/latest/utils.js"></script>

    <script src="{{asset('js/utils.js?v=' . Config::get('app.version'))}}"></script>
    <script src="{{asset('js/mvc/hours/index.js?v=' . Config::get('app.version'))}}"></script>
@endsection

@section('content')
    <div class="row m-b30">
        <div class="col-md-4">
            <div class="form-group">
                <div id="date"></div>
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input id="datepicker-range" type="checkbox" value="">
                        {{ trans('reports.select_date_range') }}
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-9">
        </div>
    </div>
    @verbatim
        <div id="app" v-cloak>
            <div class="row">
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <ul class="list-unstyled">
                                <li v-for="user in users">
                                    <span
                                       v-on:click.prevent="filterByUser(user)"
                                       v-bind:class="{'font-red': user.isActive, 'font-blue': !user.isActive}"
                                       class="cur-pointer">
                                        {{ fullName(user) }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-7" style="top: -250px;">
                    <div class="form-group alert alert-warning alert-dismissable">
                        <?php echo trans('reports.holidays_and_sick_days_not_excluded'); ?>
                    </div>
                    <div class="panel panel-info">
                        <div class="panel-body">
                            <ul class="list-unstyled">
                                <li>
                                    <span class="font-blue"><?php echo trans('reports.total_available'); ?>:</span> {{totalAvailableMinutes | formatMinutes}}
                                </li>
                                <li>
                                    <span class="font-blue"><?php echo trans('reports.registered_hours'); ?>:</span> {{totalTrackedMinutes | formatMinutes}}
                                </li>
                                <li>
                                    <span class="font-blue">% <?php echo trans('reports.percentage_of_registered_hours'); ?>:</span> {{totalTrackedPercentByAvailable}} %
                                </li>
                                <li v-show="!filterParams.user_id">
                                    <span class="font-blue"><?php echo trans('reports.users_with_minimal_time'); ?>:</span>
                                    <ul class="list-unstyled m-l30">
                                        <li v-for="ratingUser in ratingUsers">
                                            <span class="font-blue">{{ fullName(ratingUser) }}</span>
                                                - {{ratingUser.total_worked_minutes | formatMinutes}}
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endverbatim

@endsection