@extends('layouts.app')

@section('title', $title)

@section('page_js')
    <script src="{{asset('js/vue' . (config('app.env') !== 'local' ? '.min' : '') . '.js' )}}"></script>
    <script src="{{asset('js/mvc/projects/edit.js?v=' . Config::get('app.version'))}}"></script>
@endsection

@section('content')
<div class="container" id="app" v-cloak>
    <div class="row page-header">
        <div class="col-sm-8">
            <h1>{{ $title }}</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <form method="POST" id="edit-form">
                {{ csrf_field() }}
                @verbatim
                    <div v-show="error" class="alert alert-danger alert-dismissable">
                        {{error}}
                    </div>
                    <div class="form-group">
                        <label for="name"><?php echo trans('reports.name'); ?></label>
                        <input class="form-control" name="name" type="text" v-model="project.name">
                    </div>

                    <div v-show="!(childProjects.length)" class="form-group">
                        <label for="name"><?php echo trans('reports.rate'); ?></label>
                        <input class="form-control" name="rate" type="text" v-model="project.rate">
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input name="is_active" type="hidden" value="0">
                                <input checked="checked" name="is_active" type="checkbox" v-model="project.is_active">
                                <?php echo trans('reports.activity'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input name="is_fixed_price" type="hidden" value="0">
                                <input checked="checked" name="is_fixed_price" type="checkbox" v-model="project.is_fixed_price">
                                <?php echo trans('reports.fixed_price'); ?>
                            </label>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h3><?php echo trans('reports.affiliated_projects'); ?></h3>
                    </div>
                    <div class="child-projects" v-for="(childProject, index) in childProjects">
                        <input class="form-control" :name="getChildInputName('id', index)" type="hidden" v-model="childProject.id">
                        <div class="form-group">
                            <label for="name"><?php echo trans('reports.name'); ?></label>
                            <input class="form-control" :name="getChildInputName('name', index)" type="text" v-model="childProject.name">
                        </div>

                        <div class="form-group">
                            <label for="name"><?php echo trans('reports.rate'); ?></label>
                            <input class="form-control" :name="getChildInputName('rate', index)" type="text" v-model="childProject.rate">
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input :name="getChildInputName('is_active', index)" type="hidden" value="0">
                                    <input checked="checked"  :name="getChildInputName('is_active', index)" type="checkbox" v-model="childProject.is_active">
                                    <?php echo trans('reports.activity'); ?>
                                </label>
                                <span v-if="!childProject.id" >
                                    <i v-on:click.prevent="removeChildProject(index)" title="<?php echo trans('reports.remove'); ?>" aria-hidden="true" class="fa fa-window-close cur-pointer pull-right"></i>
                                </span>
                            </div>
                        </div>
                        <hr>
                    </div>
                    <div class="form-group" v-on:click.prevent="addChildProject()">
                        <a href="#"><?php echo trans('reports.add'); ?></a>
                    </div>

                    <div class="form-group alert alert-warning alert-dismissable">
                        <div>
                            <?php echo trans('reports.main_project_name'); ?>
                        </div>
                    </div>
                    <div class="form-group alert alert-warning alert-dismissable" v-show="project.id">
                        <div>
                            <?php echo trans('reports.add_child_project'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <button :disabled="disableSubmitButton" v-on:click="submit()" type="button" class="btn btn-primary pull-right"><?php echo trans('reports.save'); ?></button>
                    </div>
                @endverbatim
            </form>
        </div>
    </div>
    <hr>
</div>
@endsection
