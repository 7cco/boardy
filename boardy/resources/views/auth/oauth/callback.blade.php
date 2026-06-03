@extends('layouts.app')

@section('title', 'Обработка входа...')

@section('content')
<div style="text-align: center; padding: 3rem;">
    <h2>Завершение входа...</h2>
    <p>Пожалуйста, подождите</p>
    <div id="status">Обрабатываем ваш вход...</div>
</div>
@endsection

{{-- Подключаем auth.js для обработки callback --}}
@vite(['resources/js/auth.js', 'resources/js/oauth-callback.js'])