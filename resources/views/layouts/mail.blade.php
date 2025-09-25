<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f6f6;">
    <center style="width:100%; background:#f6f6f6; padding:20px 0; font-family:Arial,sans-serif;">

        <!-- Container -->
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:800px; background:#ffffff; border:1px solid #dee2e6; border-radius:4px;">
            <tr>
                <td style="padding:0;">

                    <!-- Header -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td bgcolor="#1d4e8f" style="padding:15px; color:#ffffff; font-size:18px; font-weight:bold;">
                                @hasSection('header')
                                    @yield('header')
                                @else
                                    {{ config('app.name') }}
                                @endif
                            </td>
                        </tr>
                    </table>

                    <!-- Intro -->
                    @hasSection('intro')
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:20px; font-size:14px; line-height:20px; color:#333;">
                                    @yield('intro')
                                </td>
                            </tr>
                        </table>
                    @endif

                    <!-- Body -->
                    @hasSection('body')
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:20px; font-size:14px; line-height:20px; color:#333;">
                                    @yield('body')
                                </td>
                            </tr>
                        </table>
                    @endif

                    <!-- Outro -->
                    @hasSection('outro')
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:20px; font-size:14px; line-height:20px; color:#555;">
                                    @yield('outro')
                                </td>
                            </tr>
                        </table>
                    @endif

                    <!-- Footer -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td bgcolor="#f1f3f5" style="padding:15px; font-size:12px; color:#6c757d; text-align:center;">
                                @hasSection('footer')
                                    @yield('footer')
                                @else
                                    <p style="margin:0; font-size:12px; line-height:18px;">
                                        <strong>Bitte antworte nicht auf diese E-Mail.</strong>
                                    </p>
                                    <p style="margin:0; font-size:12px; line-height:18px;">
                                        Versand: {{ now()->format('d.m.Y H:i') }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>

    </center>
</body>
</html>
