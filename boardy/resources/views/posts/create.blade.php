@extends('layouts.app')

@section('title', 'Новый пост')

@section('content')
    <h1>Новый пост</h1>

    <form action="{{ route('posts.store') }}" method="POST">
        @csrf

        <div>
            <label for="title"><strong>Заголовок</strong></label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255">
        </div>

        <div>
            <label for="body"><strong>Текст</strong></label>
            <textarea name="body" id="body" rows="10" required>{{ old('body') }}</textarea>
        </div>

        <div>
            <button type="submit">Опубликовать</button>
            <a href="{{ route('posts.index') }}" style="margin-left:0.5rem;">Отмена</a>
        </div>
    </form>
@endsection
