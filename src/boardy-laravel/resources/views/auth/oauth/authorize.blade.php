{{-- resources/views/auth/oauth/authorize.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Авторизация приложения</h2>
    <p><strong>{{ $client->name }}</strong> запрашивает доступ к вашему аккаунту.</p>
    
    <form method="POST" action="{{ route('passport.authorizations.approve') }}">
        @csrf
        <input type="hidden" name="state" value="{{ $request->state }}">
        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        
        <button type="submit" class="btn btn-success">Разрешить</button>
    </form>
    
    <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="mt-2">
        @csrf
        @method('DELETE')
        <input type="hidden" name="state" value="{{ $request->state }}">
        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        
        <button type="submit" class="btn btn-secondary">Отклонить</button>
    </form>
</div>
@endsection