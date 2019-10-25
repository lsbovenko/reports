<div class="form-group">
    @foreach (trans('reports.months') as $key => $month)
        <div class="form-group">
            {{ Form::label('planned_hours', $month, ['class' => 'required-star']) }}
            @php
                $isExistPlannedHour = false;
            @endphp
            @foreach ($plannedHours as $plannedHour)
                @if ($plannedHour->month === $key + 1)
                    {{ Form::text('planned_hours[]', $plannedHour->planned_hours, ['class'=>'form-control']) }}
                    @php
                        $isExistPlannedHour = true;
                        break;
                    @endphp
                @endif
            @endforeach
            @if (!$isExistPlannedHour)
                {{ Form::text('planned_hours[]', '', ['class'=>'form-control']) }}
            @endif
        </div>
    @endforeach
</div>