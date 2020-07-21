@extends('layouts.app')

@section('title', trans('reports.statistics'))

@section('page_css')
    <link rel="stylesheet" href="{{URL::asset('css/datepicker.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/select2.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/amaran.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('css/animate.min.css')}}">
@endsection

@section('page_js')
    <script src="{{ asset('js/rivets.bundled.min.js') }}"></script>
    <script src="{{asset('js/vue' . (config('app.env') !== 'local' ? '.min' : '') . '.js' )}}"></script>
    <script src="{{URL::asset('js/jquery.history.js')}}"></script>
    <script src="{{URL::asset('js/jquery-deparam.js')}}"></script>
    <script src="{{URL::asset('js/datepicker.min.js')}}"></script>
    <script src="{{ asset('js/i18n/datepicker.en.js') }}"></script>
    <script src="{{URL::asset('js/select2.min.js')}}"></script>
    <script src="{{URL::asset('js/duration.picker.js')}}"></script>
    <script src="{{URL::asset('js/Chart.bundle.min.js')}}"></script>
    <script src="{{ asset('js/jquery.amaran.js') }}"></script>
    <script src="http://www.chartjs.org/samples/latest/utils.js"></script>

    <script src="{{asset('js/utils.js?v=' . Config::get('app.version'))}}"></script>
    <script src="{{asset('js/mvc/statistics/index.js?v=' . Config::get('app.version'))}}"></script>
@endsection

@section('content')
<style>

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
                        {{ trans('reports.select_date_range') }}
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <canvas id="chart" width="300" height="100"></canvas>
        </div>
    </div>
    @verbatim
        <div id="app" v-cloak>
            <div class="row" v-if="!statistics.length">
                <div class="col-md-4 col-md-offset-6">
                    <h3 class="text-muted"><?php echo trans('reports.no_data'); ?></h3>
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
                    <div class="panel panel-info" v-if="filterParams.user_id && filterProjects.length > 0">
                        <div class="panel-heading">
                            <small><?php echo trans('reports.filter_by_project'); ?></small>
                            <div class="project-filter" v-for="project in filterProjects">
                                <button v-on:click="filterByProject(project)"
                                        class="label"
                                        v-bind:class="{'label-primary': selectedProject != project, 'label-danger': selectedProject == project}">
                                    {{project}}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-info" v-if="filterParams.user_id && filterParams.dates.length > 1 && selectedProject">
                        <div class="panel-heading">
                            <small><?php echo trans('reports.filter_by_meeting'); ?></small>
                            <input type="checkbox" v-on:click="filterByMeeting(filterParams.isMeeting)" v-model="filterParams.isMeeting">
                        </div>
                    </div>
                    <div class="panel panel-info" v-if="filterParams.user_id && filterParams.dates.length > 1 && selectedProject">
                        <div class="panel-heading">
                            <small><?php echo trans('reports.total_current_range'); ?></small>
                            <span class="label label-success"><?php echo trans('reports.fixed_time_low'); ?> {{selectedProjectTotalTime}}</span>
                            <div class="project-filter" v-if="!isAllPeriodChecked">
                                <button v-on:click="getTimeAllPeriod()" class="label label-primary">
                                    <?php echo trans('reports.get_time_all_period'); ?>
                                </button>
                            </div>
                            <div class="project-filter" v-if="isAllPeriodChecked">
                                <small><?php echo trans('reports.total_all_period'); ?></small>
                                <img src="<?php echo asset('assets/images/loader.gif'); ?>" class='loader-small'>
                                <span class="label label-success" id="time-all-period"></span>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-info" v-if="filterParams.user_id && filterParams.dates.length > 1 && !selectedProject">
                        <div class="panel-heading">
                            <small><?php echo trans('reports.total_current_range'); ?></small>
                            <span class="label label-primary"><?php echo trans('reports.in_total'); ?> {{totalInRange.total}}</span>
                            <span class="label label-success"><?php echo trans('reports.fixed_time_low'); ?> {{totalInRange.tracked}}</span>
                            <span class="label label-info"><?php echo trans('reports.other_activity_low'); ?> {{totalInRange.untracked}}</span>
                            <span title="<?php echo trans('reports.time_working_day'); ?>" class="label label-default"><?php echo trans('reports.as_planned'); ?> {{totalInRange.planned}}</span>
                        </div>
                    </div>
                    <div v-for="userStatistics in statistics">
                        <div class="panel panel-info" v-for="item in userStatistics" v-if="reportContainsSelectedProject(item.tracked)">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <span class="label label-warning">{{item.date}}</span>
                                    <span class="label label-info">{{getDayOfWeek(item.date)}}</span>
                                    <span class="label label-warning">{{fullName(item.user)}}</span>
                                    <span v-if="selectedProject" class="label label-primary">
                                        {{selectedProjectDailyTime(item.tracked) | formatMinutes}}
                                    </span>
                                    <span v-else class="label label-primary">{{item.total_logged_minutes | formatMinutes}}</span>
                                    <span class="project-filter pull-right">
                                        <button v-on:click="editDate(item.date, item.user.id, item.total_logged_minutes)" class="label label-primary" v-if="item.editable && item.date != editDateStr" title="<?php echo trans('reports.change_date'); ?>">
                                            <?php echo trans('reports.change_date'); ?>
                                        </button>
                                        <button v-on:click="saveDate(item.date, item.user.id)" class="label label-success" v-if="item.date == editDateStr" title="<?php echo trans('reports.save_date'); ?>">
                                            <?php echo trans('reports.save_date'); ?>
                                        </button>
                                    </span>
                                </h4>
                            </div>
                            <span class="date-datepicker" v-bind:id="getDate(item.date, item.user.id)"></span>
                            <div class="panel-body">

                                <div class="row"  v-if="item.tracked.length">
                                    <div class="col-md-4">
                                        <h4 class="text-muted"><?php echo trans('reports.fixed_time'); ?></h4>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-striped table-fixed">
                                            <thead>
                                            <tr>
                                                <th style="width: 15%"><?php echo trans('reports.project'); ?></th>
                                                <th style="width: 8%"><?php echo trans('reports.meeting'); ?></th>
                                                <th style="width: 15%"><?php echo trans('reports.date_added'); ?></th>
                                                <th style="width: 19%"><?php echo trans('reports.duration'); ?></th>
                                                <th style="width: 20%"><?php echo trans('reports.notes'); ?></th>
                                                <th style="width: 18%"></th>
                                                <th style="width: 5%"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-bind:class="{'bg-highlight': tracked.overtime}"
                                                v-if="!tracked.deleted && (!selectedProject || selectedProject == tracked.project_name)"
                                                v-for="tracked in item.tracked">
                                                <td>{{tracked.project_name}}</td>
                                                <td><i v-if="tracked.is_meeting" class="font-red fa fa-check"></i></td>
                                                <td>{{tracked.created}}</td>
                                                <td v-if="tracked.id != editReportId"><span class="label label-success report-width">{{tracked.total_minutes | formatMinutes}}</span></td>
                                                <td v-if="tracked.id == editReportId">
                                                    <small class="font-extra-small">
                                                        <input class="report-duration" type="text" v-on:mouseover="editDuration()"
                                                               v-bind:value="tracked.total_minutes | formatMinutesShort" v-bind:id="editTotalMinutes(tracked.id)">
                                                    </small>
                                                </td>
                                                <td v-if="tracked.id != editReportId"><small class="font-extra-small">{{tracked.descirption}}</small></td>
                                                <td v-if="tracked.id == editReportId"><small class="font-extra-small"><textarea style="resize:none;width:100%;" v-bind:value="tracked.descirption" v-bind:id="editDescription(tracked.id)"></textarea></small></td>
                                                <td><div class="project-filter">
                                                        <button v-on:click="editReportToUnbillable(tracked.id)" class="label label-primary" v-if="item.editable && tracked.id != editReportId" title="<?php echo trans('reports.move_to_unbillable'); ?>">
                                                            <?php echo trans('reports.move_to_unbillable'); ?>
                                                        </button>
                                                        <button v-on:click="editReport(tracked, item.total_logged_minutes)" class="label label-primary" v-if="item.editable && tracked.id != editReportId" title="<?php echo trans('reports.edit'); ?>">
                                                            <?php echo trans('reports.edit'); ?>
                                                        </button>
                                                        <button v-on:click="saveReport(tracked)" class="label label-success" v-if="tracked.id == editReportId" title="<?php echo trans('reports.save'); ?>">
                                                            <?php echo trans('reports.save'); ?>
                                                        </button>
                                                        <button v-on:click="cancelReport()" class="label label-danger" v-if="tracked.id == editReportId" title="<?php echo trans('reports.cancel'); ?>">
                                                            <?php echo trans('reports.cancel'); ?>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="font-red">
                                                    <i v-if="item.editable" v-on:click="deleteReport(tracked)" title="<?php echo trans('reports.remove'); ?>" class="fa fa-window-close cur-pointer"
                                                       aria-hidden="true"></i></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row" v-if="item.untracked.length && !selectedProject">
                                    <div class="col-md-4">
                                        <h4 class="text-muted"><?php echo trans('reports.other_activity'); ?></h4>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-striped table-fixed">
                                            <thead>
                                            <tr>
                                                <th style="width: 23%"><?php echo trans('reports.task'); ?></th>
                                                <th style="width: 15%"><?php echo trans('reports.date_added'); ?></th>
                                                <th style="width: 19%"><?php echo trans('reports.duration'); ?></th>
                                                <th style="width: 20%"><?php echo trans('reports.notes'); ?></th>
                                                <th style="width: 18%"></th>
                                                <th style="width: 5%"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-bind:class="{'bg-highlight': activity.overtime}" v-if="!activity.deleted" v-for="activity in item.untracked">
                                                <td>
                                                    <span v-if="activity.project_name">
                                                        {{activity.project_name}}
                                                    </span>
                                                    <span v-if="activity.task">
                                                        {{activity.task}}
                                                    </span>
                                                </td>
                                                <td>{{activity.created}}</td>
                                                <td v-if="activity.id != editReportId"><span class="label label-info report-width">{{activity.total_minutes | formatMinutes}}</span></td>
                                                <td v-if="activity.id == editReportId">
                                                    <small class="font-extra-small">
                                                        <input class="report-duration" type="text" v-on:mouseover="editDuration()"
                                                               v-bind:value="activity.total_minutes | formatMinutesShort" v-bind:id="editTotalMinutes(activity.id)">
                                                    </small>
                                                </td>
                                                <td v-if="activity.id != editReportId"><small class="font-extra-small">{{activity.descirption}}</small></td>
                                                <td v-if="activity.id == editReportId"><small class="font-extra-small"><textarea style="resize:none;width:100%;" v-bind:value="activity.descirption" v-bind:id="editDescription(activity.id)"></textarea></small></td>
                                                <td><div class="project-filter">
                                                        <button v-on:click="editReportToBillable(activity.id)" class="label label-primary" v-if="item.editable && activity.id != editReportId" title="<?php echo trans('reports.move_to_billable'); ?>">
                                                            <?php echo trans('reports.move_to_billable'); ?>
                                                        </button>
                                                        <button v-on:click="editReport(activity, item.total_logged_minutes)" class="label label-primary" v-if="item.editable && activity.id != editReportId" title="<?php echo trans('reports.edit'); ?>">
                                                            <?php echo trans('reports.edit'); ?>
                                                        </button>
                                                        <button v-on:click="saveReport(activity)" class="label label-success" v-if="activity.id == editReportId" title="<?php echo trans('reports.save'); ?>">
                                                            <?php echo trans('reports.save'); ?>
                                                        </button>
                                                        <button v-on:click="cancelReport()" class="label label-danger" v-if="activity.id == editReportId" title="<?php echo trans('reports.cancel'); ?>">
                                                            <?php echo trans('reports.cancel'); ?>
                                                        </button>
                                                    </div>
                                                    <div v-bind:id="getModal(activity.id)" class="modal-content"
                                                        v-if="activity.id == editReportIdToBillable">
                                                        <div class="form-group">
                                                            <label class="control-label"><?php echo trans('reports.project'); ?></label>
                                                            <select class="form-control select-project">
                                                                <option></option>
                                                                <?php foreach ($activeProjects as $activeProject) {
                                                                    echo '<option value="' . $activeProject['id'] . '">' . $activeProject['fullName'] . '</option>';
                                                                } ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label"><?php echo trans('reports.meeting'); ?></label>
                                                            <input type="checkbox" class="checkbox">
                                                        </div>
                                                        <div class="project-filter">
                                                            <button v-on:click="saveModal(activity.id)" class="label label-success" title="<?php echo trans('reports.save_project'); ?>">
                                                                <?php echo trans('reports.save'); ?>
                                                            </button>
                                                            <button v-on:click="cancelModal()" class="label label-danger" title="<?php echo trans('reports.cancel'); ?>">
                                                                <?php echo trans('reports.cancel'); ?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="font-red">
                                                    <i v-if="item.editable" v-on:click="deleteReport(activity)" title="<?php echo trans('reports.remove'); ?>" class="fa fa-window-close cur-pointer"
                                                                        aria-hidden="true"></i></td>
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