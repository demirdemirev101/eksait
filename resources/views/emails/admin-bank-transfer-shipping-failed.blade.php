@extends('emails.layout')

@section('title', 'Грешка при доставка')

@section('hero')
    <strong>Неуспешно изчисляване на доставка</strong><br>
    Възникна проблем при поръчка <strong>#{{ $orderId }}</strong>.
@endsection

@section('content')
    <div class="section-title">Детайли за грешката</div>

    <table class="dark-box" role="presentation">
        <tr><td class="dark-label">Поръчка</td><td>#{{ $orderId }}</td></tr>
        <tr><td class="dark-label">Грешка</td><td>{{ $errorMessage }}</td></tr>
        <tr><td colspan="2" class="dark-note">Моля, проверете настройките на Еконт и опитайте отново.</td></tr>
    </table>
@endsection
