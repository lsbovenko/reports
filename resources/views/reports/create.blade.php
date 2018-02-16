@extends('layouts.app')

@section('title', 'Новый Отчёт')

@section('page_css')
    <link rel="stylesheet" href="{{URL::asset('css/datepicker.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/select2.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/amaran.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/animate.min.css')}}">
@endsection

@section('page_js')
    <script src="{{ asset('js/rivets.bundled.min.js') }}"></script>
    <script src="{{URL::asset('js/datepicker.min.js')}}"></script>
    <script src="{{URL::asset('js/select2.min.js')}}"></script>
    <script src="{{URL::asset('js/duration.picker.js')}}"></script>
    <script src="{{URL::asset('js/parsley.min.js')}}"></script>
    <script src="{{URL::asset('js/i18n/parsley.ru.js')}}"></script>
    <script src="{{URL::asset('js/jquery.amaran.min.js')}}"></script>
    <script src="{{URL::asset('js/rivets.binders.js')}}"></script>
    <script src="{{URL::asset('js/readmore.js')}}"></script>
    <script src="{{URL::asset('js/mvc/reports/create.js?v=' . time())}}"></script>
@endsection

@section('content')
    <div class="row">

        <div class="col-md-12">
            <div class="m-b30">
                <h2 class="text-muted">Новый Отчёт</h2>
            </div>

            <form rv-class-hidden="0" class="form-horizontal hidden" id="report-form">
                <div class="form-group">
                    <div class="col-md-offset-2 col-md-6">
                        <div class="well">
                            <h5 class="text-muted">Как указывать время в отчете</h5>

                            <div class="readmore">
                                <p>
                                    Оплачиваемое время - часы работы над проектом, которые оплачиваются заказчиком.
                                    Это может быть как время по трэкеру, так и часы в Jira,
                                    которые входят в оценку задачи. Если у вас есть сомнения в том,
                                    как классифицировать свое время - обратитесь, пожалуйста, к менеджеру.
                                </p>

                                <p>
                                    Остальное время - время, потраченное на самообразование, внутренние проекты либо
                                    время, потраченное на задачу сверх оценки и не подлежащее оплате клиентом.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="date" class="col-sm-2 control-label">Дата</label>
                    <div class="col-sm-3">
                        <div class="inner-addon right-addon">
                            <i class="fa fa-calendar"></i>
                            <input readonly="readonly" placeholder="date" id="date" type="text" class="form-control"/>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <h3 class="text-muted">Оплачиваемое время</h3>
                    </div>
                </div>

                <div rv-each-report="reports.tracked" rv-class-hidden="report.deleted" class="root">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Проект</label>
                        <div class="col-md-2">
                            <select rv-parsley-required="report.deleted | not" rv-value="report.name"
                                    rv-jquery-plugin-select2="select2Options"
                                    class="form-control chosen-rtl select-project tracked">
                                <option></option>
                                @if($latestProject)
                                    <option value="{{$latestProject['id']}}">{{$latestProject['fullName']}}</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="col-sm-2 control-label">Время (ЧЧММ)</label>
                            <div class="col-md-2">
                                <input rv-parsley-required="report.deleted | not" class="form-control"
                                       rv-on-change="controller.updateTime"
                                       rv-jquery-plugin-tooltip="durationTooltip"
                                       rv-jquery-plugin-duration="durationPickerOptions"
                                       rv-value="report._time" type="text">
                            </div>
                            <div class="col-sm-1 font-red">
                                <i rv-on-click="controller.removeReport" title="Удалить" class="fa fa-window-close cur-pointer"
                                   aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Заметки</label>
                        <div class="col-md-6">
                            <textarea rv-value="report.description" class="form-control"
                                      rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-2 col-md-offset-2">
                        <button rv-on-click="controller.addMoreTracked" type="button" class="btn btn-link">добавить+
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <h3 class="text-muted">Остальное время</h3>
                    </div>
                </div>

                <div rv-each-report="reports.untracked" rv-class-hidden="report.deleted" class="root">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Проект или задание</label>
                        <div class="col-md-2">
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
                            <label class="col-sm-2 control-label">Время (ЧЧММ)</label>
                            <div class="col-md-2">
                                <input rv-parsley-required="report.deleted | not" rv-jquery-plugin-duration="durationPickerOptions"
                                       rv-on-change="controller.updateTime"
                                       rv-jquery-plugin-tooltip="durationTooltip"
                                       rv-value="report._time"
                                       type="text" class="form-control">
                            </div>
                            <div class="col-sm-1 font-red">
                                <i rv-on-click="controller.removeReport" title="Удалить" class="fa fa-window-close cur-pointer"
                                   aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Заметки</label>
                        <div class="col-md-6">
                            <textarea rv-value="report.description" class="form-control"
                                      rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-2 col-md-offset-2">
                        <button rv-on-click="controller.addMoreUntracked" type="button" class="btn btn-link">добавить+
                        </button>
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button rv-on-click="controller.sendNewReport" type="button" class="btn btn-default">Отправить
                        </button>
                    </div>
                </div>
            </form>

        </div>

    </div>

@endsection