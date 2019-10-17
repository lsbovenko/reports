@extends('layouts.app')

@section('title', trans('reports.revenue'))

@section('page_css')
    <link rel="stylesheet" href="{{URL::asset('css/datepicker.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/select2.min.css')}}">
@endsection

@section('page_js')
    <script src="{{asset('js/vue' . (config('app.env') !== 'local' ? '.min' : '') . '.js' )}}"></script>
    <script src="{{URL::asset('js/datepicker.min.js')}}"></script>
    <script src="{{ asset('js/i18n/datepicker.en.js') }}"></script>
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
                    <div class="panel panel-info">
                        <div class="panel-body">
                            <div class="form-group">
                                <div class="col-md-8">
                                    <ul class="list-unstyled">
                                        <li>
                                            <span class="font-blue"><?php echo trans('reports.fixed_price_revenue'); ?>: </span>$ {{fixedPriceRevenue}}
                                        </li>
                                        <li>
                                            <span class="font-blue"><?php echo trans('reports.not_fixed_price_revenue'); ?>: </span>$ {{notFixedPriceRevenue}}
                                        </li>
                                        <li>
                                            <span class="font-blue"><?php echo trans('reports.total_revenue'); ?>: </span>$ {{totalRevenue}}
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