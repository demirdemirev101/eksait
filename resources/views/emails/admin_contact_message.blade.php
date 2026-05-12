@extends('emails.layout')

@section('title', 'Ново съобщение')

@section('hero')
    <strong>Получено е ново съобщение от контактната форма.</strong><br>
    Провери данните на клиента и отговори при първа възможност.
@endsection

@section('content')
    <div class="section-title">Контакт</div>

    <table class="dark-box" role="presentation">
        <tr><td class="dark-label">Име</td><td>{{ $name }}</td></tr>
        <tr><td class="dark-label">Email</td><td>{{ $email }}</td></tr>
        <tr><td class="dark-label">Телефон</td><td>{{ $phone }}</td></tr>
    </table>

    <div class="section-title">Съобщение</div>
    <div class="info-box">
        {{ $messageContent }}
    </div>
@endsection
