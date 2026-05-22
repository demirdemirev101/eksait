@extends('emails.layout')

@section('title', 'Възстановяване на парола')

@section('hero')
    <strong>Получихме заявка за смяна на паролата.</strong><br>
    Използвай бутона по-долу, за да зададеш нова парола за профила си.
@endsection

@section('content')
    <div class="section-title">Смяна на парола</div>

    <p style="margin: 0 0 18px; color: #555555; font-size: 14px; line-height: 1.7;">
        Здравей{{ $user->name ? ', ' . $user->name : '' }}.
    </p>

    <p style="margin: 0 0 22px; color: #555555; font-size: 14px; line-height: 1.7;">
        Линкът е валиден {{ $expiresInMinutes }} минути. Ако не си заявил/а смяна на парола, можеш спокойно да игнорираш този имейл.
    </p>

    <p style="margin: 0 0 26px;">
        <a href="{{ $resetUrl }}" class="button">Смени паролата</a>
    </p>
@endsection
