<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Excite Company')</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f4; color: #1a1a1a; font-family: Arial, Helvetica, sans-serif; }
        table { border-collapse: collapse; }
        .wrapper { width: 100%; background: #f4f4f4; padding: 32px 12px; }
        .container { width: 100%; max-width: 620px; margin: 0 auto; background: #ffffff; border: 1px solid #e6e6e6; }
        .header { background: #1a1a1a; border-bottom: 3px solid #cc0000; padding: 28px 36px; }
        .brand-label { color: #cc0000; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; }
        .header-title { margin: 6px 0 0; color: #ffffff; font-size: 22px; line-height: 1.25; }
        .hero { background: #cc0000; color: #ffffff; padding: 18px 36px; font-size: 14px; line-height: 1.6; }
        .body { padding: 32px 36px; }
        .section-title { margin: 0 0 14px; padding-bottom: 8px; border-bottom: 1px solid #e8e8e8; color: #cc0000; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; }
        .items-table { width: 100%; margin-bottom: 28px; font-size: 14px; }
        .items-table th { background: #1a1a1a; color: #ffffff; padding: 10px 14px; font-size: 11px; letter-spacing: 1px; text-align: left; text-transform: uppercase; }
        .items-table td { border-bottom: 1px solid #f0f0f0; padding: 12px 14px; color: #333333; vertical-align: top; }
        .item-name { color: #1a1a1a; font-weight: 700; }
        .item-meta, .muted { color: #888888; font-size: 12px; }
        .item-meta { margin-top: 3px; }
        .totals-table { width: 100%; margin-bottom: 28px; background: #f9f9f9; font-size: 14px; }
        .totals-table td { padding: 7px 20px; color: #555555; }
        .totals-table .first-row td { padding-top: 16px; }
        .totals-table .total-row td { border-top: 2px solid #1a1a1a; padding-top: 12px; padding-bottom: 16px; color: #1a1a1a; font-size: 16px; font-weight: 700; }
        .info-box { margin-bottom: 28px; background: #f9f9f9; padding: 16px 20px; color: #333333; font-size: 14px; line-height: 1.7; }
        .dark-box { width: 100%; margin-bottom: 28px; border-left: 4px solid #cc0000; background: #1a1a1a; color: #ffffff; }
        .dark-box td { padding: 6px 24px; font-size: 13px; line-height: 1.5; }
        .dark-title { padding-top: 20px !important; padding-bottom: 10px !important; color: #cc0000; font-size: 13px !important; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .dark-label { width: 120px; color: #aaaaaa; font-size: 12px; }
        .dark-note { padding-top: 14px !important; padding-bottom: 20px !important; border-top: 1px solid #333333; color: #aaaaaa; font-size: 12px !important; }
        .button { display: inline-block; background: #cc0000; color: #ffffff !important; padding: 12px 18px; text-decoration: none; font-weight: 700; font-size: 14px; }
        .footer { border-top: 3px solid #cc0000; background: #1a1a1a; padding: 24px 36px; color: #888888; font-size: 12px; line-height: 1.8; text-align: center; }
        .footer strong { display: block; margin-bottom: 6px; color: #ffffff; font-size: 14px; }
        .footer a { color: #cc0000; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="brand-label">Excite Company</div>
                <h1 class="header-title">@yield('title', 'Excite Company')</h1>
            </div>

            @hasSection('hero')
                <div class="hero">@yield('hero')</div>
            @endif

            <div class="body">
                @yield('content')
            </div>

            <div class="footer">
                <strong>Excite Company</strong>
                {{ __('orders.mail.footer.city_country') }}<br>
                {{ __('orders.mail.footer.phone') }}: 0988 335 555 &nbsp;·&nbsp;
                <a href="mailto:info@excitecompany.bg">info@excitecompany.bg</a>
            </div>
        </div>
    </div>
</body>
</html>
