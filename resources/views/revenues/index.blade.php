@extends('layouts.app')

@section('title', 'Revenues')

@section('page_css')
    <link rel="stylesheet" href="{{URL::asset('css/datepicker.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/select2.min.css')}}">
@endsection

@section('page_js')
    <script src="{{asset('js/vue' . (env('APP_ENV') !== 'local' ? '.min' : '') . '.js' )}}"></script>
    <script src="{{URL::asset('js/datepicker.min.js')}}"></script>
    <script src="{{URL::asset('js/select2.min.js')}}"></script>
    <script src="{{URL::asset('js/Chart.bundle.min.js')}}"></script>
    <script src="http://www.chartjs.org/samples/latest/utils.js"></script>

    <script src="{{asset('js/mvc/revenues/index.js?v=' . Config::get('app.version'))}}"></script>
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
                        выбрать диапазон дат
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="panel panel-info">
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Проект</label>
                        <div class="col-md-6">
                            <select id="jquery-plugin-select1" style="display:none" class="form-control chosen-rtl select-project">
                                <option value="">--Все--</option>
                                @foreach ($projects as $project)
                                    <option value="{{$project->id}}">{{$project->getFullName()}}</option>
                                    @if($project->children->count())
                                        @foreach ($project->children as $childProject)
                                            <option value="{{$childProject->id}}">{{$childProject->getFullName()}}</option>
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            @verbatim
                <div id="app" v-cloak>
                    <div class="panel panel-info">
                        <div class="panel-body">
                            <div class="form-group">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li>
                                            <span class="font-blue">Approximate revenue: </span>$ {{revenue}}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endverbatim
        </div>
    </div>
@endsection