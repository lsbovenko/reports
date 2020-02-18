@extends('layouts.app')

@section('title', trans('reports.new_report'))

@section('page_css')
    <link rel="stylesheet" href="{{URL::asset('css/datepicker.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/select2.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/amaran.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/animate.min.css')}}">
@endsection

@section('page_js')
    <script src="{{ asset('js/rivets.bundled.min.js') }}"></script>
    <script src="{{URL::asset('js/datepicker.min.js')}}"></script>
    <script src="{{ asset('js/i18n/datepicker.en.js') }}"></script>
    <script src="{{URL::asset('js/select2.min.js')}}"></script>
    <script src="{{URL::asset('js/duration.picker.js')}}"></script>
    <script src="{{URL::asset('js/parsley.min.js')}}"></script>
    <script src="{{URL::asset('js/i18n/parsley.ru.js')}}"></script>
    <script src="{{URL::asset('js/jquery.amaran.min.js')}}"></script>
    <script src="{{URL::asset('js/rivets.binders.js')}}"></script>
    <script src="{{URL::asset('js/readmore.js')}}"></script>
    <script src="{{asset('js/utils.js?v=' . Config::get('app.version'))}}"></script>
    <script src="{{URL::asset('js/mvc/reports/create.js?v=' . Config::get('app.version'))}}"></script>
@endsection

@section('content')
    <div class="row">

        <div class="col-md-8">
            <div class="m-b30">
                <h2 class="text-muted">{{ trans('reports.new_report') }}</h2>
            </div>

            <form rv-class-hidden="0" class="form-horizontal hidden" id="report-form">
                <div class="form-group">
                    <div class="col-md-offset-2 col-md-8">
                        <div class="well">
                            <h5 class="text-muted">{{ trans('reports.indicate_time') }}</h5>

                            <div class="readmore">
                                <p>
                                    {{ trans('reports.paid_time_description') }}
                                </p>

                                <p>
                                    {{ trans('reports.rest_of_the_time_description') }}
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="date" class="col-sm-2 control-label">{{ trans('reports.date') }}</label>
                    <div class="col-sm-3 col-md-4">
                        <div class="inner-addon right-addon">
                            <i class="fa fa-calendar"></i>
                            <input readonly="readonly" placeholder="date" id="date" type="text" class="form-control"/>
                        </div>
                    </div>
                    <label class="col-sm-3 control-label col-md-4">{{ trans('reports.total_time') }}: <span id="totalTime" rv-text="totalTime"></span></label>
                </div>

                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">
                        <h3 class="text-muted">{{ trans('reports.billable_time') }}</h3>
                    </div>
                </div>

                <div rv-each-report="reports.tracked" rv-class-hidden="report.deleted" class="root">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{ trans('reports.project') }}</label>
                        <div class="col-md-3">
                            <select rv-parsley-required="report.deleted | not" rv-value="report.name"
                                    rv-jquery-plugin-select2="select2Options"
                                    class="form-control chosen-rtl select-project tracked">
                                <option></option>
                                @foreach($latestProjects as $latestProject)
                                    <option value="{{$latestProject['id']}}">{{$latestProject['fullName']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="col-sm-2 control-label">{{ trans('reports.time') }}</label>
                            <div class="col-md-3">
                                <input rv-parsley-required="report.deleted | not" class="form-control"
                                       rv-on-change="controller.updateTime"
                                       rv-jquery-plugin-tooltip="durationTooltip"
                                       rv-jquery-plugin-duration="durationPickerOptions"
                                       rv-value="report._time" type="text">
                            </div>
                            <div class="col-sm-1 font-red">
                                <i rv-on-click="controller.removeReport" title="{{ trans('reports.remove') }}" class="fa fa-window-close cur-pointer"
                                   aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{ trans('reports.notes') }}</label>
                        <div class="col-md-8">
                            <textarea rv-value="report.description" class="form-control"
                                      rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-2 col-md-offset-2">
                        <button rv-on-click="controller.addMoreTracked" type="button" class="btn btn-link">{{ trans('reports.add_low') }}+
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">
                        <h3 class="text-muted">{{ trans('reports.rest_of_the_time') }}</h3>
                    </div>
                </div>

                <div rv-each-report="reports.untracked" rv-class-hidden="report.deleted" class="root">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{ trans('reports.project_or_task') }}</label>
                        <div class="col-md-3">
                            <input class="form-control" type="text" rv-parsley-required="report.deleted | not" rv-value="report.name">
                            {{--<select rv-parsley-required="report.deleted | not" rv-value="report.name"
                                    rv-jquery-plugin-select2="select2Options"
                                    class="form-control chosen-rtl select-project">
                                <option></option>
                                @foreach($projects as $project)
                                    <option id="{{$project->name}}">{{$project->name}}</option>
                                @endforeach
                            </select>--}}
                        </div>
                        <div>
                            <label class="col-sm-2 control-label">{{ trans('reports.time') }}</label>
                            <div class="col-md-3">
                                <input rv-parsley-required="report.deleted | not" rv-jquery-plugin-duration="durationPickerOptions"
                                       rv-on-change="controller.updateTime"
                                       rv-jquery-plugin-tooltip="durationTooltip"
                                       rv-value="report._time"
                                       type="text" class="form-control">
                            </div>
                            <div class="col-sm-1 font-red">
                                <i rv-on-click="controller.removeReport" title="{{ trans('reports.remove') }}" class="fa fa-window-close cur-pointer"
                                   aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{ trans('reports.notes') }}</label>
                        <div class="col-md-8">
                            <textarea rv-value="report.description" class="form-control"
                                      rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-2 col-md-offset-2">
                        <button rv-on-click="controller.addMoreUntracked" type="button" class="btn btn-link">{{ trans('reports.add_low') }}+
                        </button>
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button rv-on-click="controller.sendNewReport" type="button" class="btn btn-default">{{ trans('reports.submit') }}
                        </button>
                    </div>
                </div>
                <div class="col-md-offset-2 col-md-8">
                    <div class="form-group alert alert-warning alert-dismissable">
                        {{ trans('reports.contact_your_manager') }}
                    </div>
                </div>
            </form>

        </div>
        <div class="col-md-4">
            <div class="m-t80">
                <label>{{ trans('reports.choose_month') }}</label>
                <div class="form-group choose-month">
                    <button type="button" id="button-prev"></button>
                    <input type="text" id="date_month" readonly="readonly" />
                    <button type="button" id="button-next"></button>
                </div>
            </div>
            <div class="progress" id="progress">
                <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" style="width:0" id="progress_bar_left">
                    <span id="progress_time"></span>
                </div>
                <div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" style="width:0" id="progress_bar_right">
                </div>
            </div>
            <div id="remain_time"></div>
            <div class="m-t80">
                <table class="table table-bordered" id="report-table">
                    <tbody>
                    <tr rv-show="totalTime | != ''">
                        <th colspan="3">{{ trans('reports.total_time') }}: { totalTime }</th>
                    </tr>
                    <tr rv-show="tableTracked | length != null">
                        <th colspan="3">{{ trans('reports.billable_time') }}</th>
                    </tr>
                    <tr class="success" rv-each-record-tracked="tableTracked">
                        <td rv-text="record-tracked.project_name"></td>
                        <td rv-text="record-tracked.descirption"></td>
                        <td rv-text="record-tracked.formatted_time"></td>
                    </tr>
                    <tr rv-show="tableUntracked | length != null">
                        <th colspan="3">{{ trans('reports.other_activity') }}</th>
                    </tr>
                    <tr class="info" rv-each-record-untracked="tableUntracked">
                        <td rv-text="record-untracked.task"></td>
                        <td rv-text="record-untracked.descirption"></td>
                        <td rv-text="record-untracked.formatted_time"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection