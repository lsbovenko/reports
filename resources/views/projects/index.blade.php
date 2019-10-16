@extends('layouts.app')

@section('title', trans('reports.projects'))

@section('content')
<div class="container">
    <div class="row page-header">
        <div class="col-sm-8">
            <h1>{{ trans('reports.projects') }}</h1>
        </div>
        <div class="col-sm-4">
            <a href="{{ route('projects.create') }}">
                <button class="btn btn-primary h1" type="button">+ {{ trans('reports.add') }}</button>
            </a>
        </div>
    </div>
    <form action="" id="fiter">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::text('name', '', ['class'=>'form-control', 'placeholder' => trans('reports.name')]) }}
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary pull-right">{{ trans('reports.apply') }}</button>
                </div>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('reports.name') }}</th>
                        <th>{{ trans('reports.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr class="@if (!$project->is_active)danger @endif">
                            <td>{{ $project->name }}</td>
                            <td>
                                <a href="{{ route('projects.edit', ['id' => $project->id]) }}">{{ trans('reports.edit') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <hr>
    {{ $projects->render() }}
</div>
@endsection
