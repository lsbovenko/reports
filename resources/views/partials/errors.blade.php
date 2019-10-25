@foreach (array_unique($errors->all()) as $message)
    <div class="alert alert-danger alert-dismissable">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {{ $message }}
    </div>
@endforeach