@extends('emails.layout')

@section('title', 'Грешка при пратка')

@section('hero')
    <strong>Възникна проблем при създаване или обработка на пратка.</strong><br>
    Необходима е проверка от администратор.
@endsection

@section('content')
    <div class="section-title">Действие</div>

    <div class="info-box">
        Моля, проверете логовете, настройките на доставчика и състоянието на съответната поръчка в административния панел.
    </div>
@endsection
