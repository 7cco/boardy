@extends('layouts.app')

@section('title', 'Редактировать пост')

@section('content')
    <h1>Редактировать пост</h1>

    <form action="{{ route('posts.update', $post) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label for="title"><strong>Заголовок</strong></label>
            <input type="text" name="title" id="title" 
                   value="{{ old('title', $post->title) }}" 
                   required maxlength="255">
        </div>

        <div>
            <label for="body"><strong>Текст</strong></label>
            <textarea name="body" id="body" rows="10" required>{{ old('body', $post->body) }}</textarea>
        </div>

        <div>
            <button type="submit">Сохранить изменения</button>
            <a href="{{ route('posts.show', $post) }}" style="margin-left:0.5rem;">Отмена</a>
        </div>
    </form>
@endsection
