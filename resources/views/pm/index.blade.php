@extends('layouts.app')

@section('title', trans('reports.pm'))

@section('page_css')
    <link rel="stylesheet" href="{{URL::asset('css/datepicker.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/select2.min.css')}}">
@endsection

@section('page_js')
    <script src="{{asset('js/vue' . (config('app.env') !== 'local' ? '.min' : '') . '.js' )}}"></script>
    <script src="{{URL::asset('js/jquery.history.js')}}"></script>
    <script src="{{URL::asset('js/jquery-deparam.js')}}"></script>
    <script src="{{URL::asset('js/datepicker.min.js')}}"></script>
    <script src="{{ asset('js/i18n/datepicker.en.js') }}"></script>
    <script src="{{URL::asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/mvc/pm/index.js?v=' . Config::get('app.version'))}}"></script>
@endsection

@section('content')

    <div class="row m-b30">
        <div class="col-md-3">
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
        <div class="col-md-8">
            <div class="panel panel-info">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{ trans('reports.project') }}</label>
                        <div class="col-md-6">
                            <select id="jquery-plugin-select1" style="display:none" class="form-control chosen-rtl select-project">
                                <option value="">--{{ trans('reports.all') }}--</option>
                                @foreach ($projects as $project)
                                    <option value="{{$project->id}}">{{$project->getFullName()}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            @verbatim
                <div id="app" v-cloak>
                    <div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo trans('reports.date_pm'); ?></label>
                                <div class="col-md-6">
                                    <span>{{startDate}}</span>
                                    <span v-if="endDate"> - {{endDate}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-info">
                        <div class="panel-body">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <table class="table table-striped table-fixed">
                                        <thead>
                                        <tr>
                                            <th><?php echo trans('reports.employee'); ?></th>
                                            <th><?php echo trans('reports.time_pm'); ?></th>
                                            <th><?php echo trans('reports.time_dec'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="item in pmStatistics">
                                            <td>{{item.employee}}</td>
                                            <td>{{item.hours}}:{{item.minutes}}</td>
                                            <td>{{item.timeDec}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endverbatim
        </div>
    </div>
@endsection