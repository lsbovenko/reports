@extends('layouts.app')

@section('page_css')
    <link rel="stylesheet" href="{{URL::asset('css/datepicker.min.css')}}">
@endsection

@section('page_js')
    <script src="{{asset('js/vue' . (env('APP_ENV') !== 'local' ? '.min' : '') . '.js' )}}"></script>
    <script src="{{URL::asset('js/datepicker.min.js')}}"></script>
    <script src="{{URL::asset('js/Chart.bundle.min.js')}}"></script>
    <script src="http://www.chartjs.org/samples/latest/utils.js"></script>

    <script src="{{asset('js/mvc/statistics/index.js?v=' . time() )}}"></script>
@endsection

@section('content')
<style>
    table {
        table-layout: fixed;
    }
</style>
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
        <div class="col-md-9">
            <canvas id="chart" width="300" height="100"></canvas>
        </div>
    </div>
    @verbatim
        <div id="app">
            <div class="row" v-if="!statistics.length">
                <div class="col-md-4 col-md-offset-6">
                    <h3 class="text-muted">Нет данных</h3>
                </div>
            </div>
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

                <div class="col-md-9">

                    <div v-for="userStatistics in statistics">
                        <div class="panel panel-info" v-for="item in userStatistics">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <span class="label label-warning">{{item.date}}</span>
                                    <span class="label label-primary">{{fullName(item.user)}}</span>
                                    <span class="label label-success">{{item.total_logged_minutes | formatMinutes}}</span>
                                </h4>
                            </div>
                            <div class="panel-body">

                                <div class="row"  v-if="item.tracked.length">
                                    <div class="col-md-4">
                                        <h4 class="text-muted">Зафиксированное время</h4>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-striped table-fixed">
                                            <thead>
                                            <tr>
                                                <th>Проект</th>
                                                <th>Дата добавления</th>
                                                <th>Продолжительность</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="tracked in item.tracked">
                                                <td>{{tracked.project_name}}</td>
                                                <td>{{tracked.created}}</td>
                                                <td><span class="label label-success">{{tracked.total_minutes | formatMinutes}}</span></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row" v-if="item.untracked.length">
                                    <div class="col-md-4">
                                        <h4 class="text-muted">Другая активность</h4>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-striped table-fixed">
                                            <thead>
                                            <tr>
                                                <th>Задача</th>
                                                <th>Дата добавления</th>
                                                <th>Продолжительность</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="activity in item.untracked">
                                                <td>
                                                    <span v-if="activity.project_name">
                                                        {{activity.project_name}}
                                                    </span>
                                                    <span v-if="activity.task">
                                                        {{activity.task}}
                                                    </span>
                                                </td>
                                                <td>{{activity.created}}</td>
                                                <td><span class="label label-success">{{activity.total_minutes | formatMinutes}}</span></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>

    @endverbatim

@endsection