@extends('layouts.app')

@section('title', 'Проекты')

@section('content')
<div class="container">
    <div class="row page-header">
        <div class="col-sm-8">
            <h1>Проекты</h1>
        </div>
        <div class="col-sm-4">
            <a href="{{ route('projects.create') }}">
                <button class="btn btn-primary h1" type="button">+ Добавить</button>
            </a>
        </div>
    </div>
    <form action="" id="fiter">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::text('name', '', ['class'=>'form-control', 'placeholder' => 'Имя']) }}
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary pull-right">Применить</button>
                </div>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr class="@if (!$project->is_active)danger @endif">
                            <td>{{ $project->name }}</td>
                            <td>
                                <a href="{{ route('projects.edit', ['id' => $project->id]) }}">Редактировать</a>
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
