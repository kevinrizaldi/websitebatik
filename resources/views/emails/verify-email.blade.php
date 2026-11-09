<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verifikasi Email – Hamzah Style Official</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #FFF8F5;
            color: #110703;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            padding: 40px 16px;
            background-color: #FFF8F5;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border: 1px solid #EFE0D6;
            border-radius: 20px;
            overflow: hidden;
        }
        .header {
            background-color: #2B1E16;
            padding: 32px 40px;
            text-align: center;
        }
        .header-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .header-icon {
            width: 44px;
            height: 44px;
            background-color: #FFDBCE;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .header-title {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #FFF8F5;
        }
        .header-subtitle {
            font-size: 10px;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: #FFDBCE;
            margin-top: 2px;
        }
        .body {
            padding: 40px 40px 32px;
        }
        .email-icon-wrap {
            width: 56px;
            height: 56px;
            background-color: #FFF8F5;
            border: 1px solid #EFE0D6;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #110703;
            margin-bottom: 8px;
            font-family: Georgia, 'Times New Roman', serif;
        }
        .subtext {
            font-size: 14px;
            color: #4E4540;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .btn-wrap {
            text-align: center;
            margin-bottom: 32px;
        }
        .btn {
            display: inline-block;
            background-color: #2B1E16;
            color: #FFF8F5 !important;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.08em;
            padding: 14px 36px;
            border-radius: 12px;
        }
        .divider {
            border: none;
            border-top: 1px solid #EFE0D6;
            margin: 24px 0;
        }
        .fallback-label {
            font-size: 12px;
            color: #80756F;
            margin-bottom: 8px;
        }
        .fallback-url {
            font-size: 11px;
            color: #2B1E16;
            word-break: break-all;
            background-color: #FFF8F5;
            border: 1px solid #EFE0D6;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .expiry-note {
            font-size: 12px;
            color: #80756F;
            margin-top: 20px;
        }
        .footer {
            background-color: #FFF1E9;
            border-top: 1px solid #EFE0D6;
            padding: 20px 40px;
            text-align: center;
        }
        .footer-text {
            font-size: 11px;
            color: #80756F;
            line-height: 1.6;
        }
        .footer-brand {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #4E4540;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">

            {{-- Header --}}
            <div class="header">
                <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                    <tr>
                        <td style="vertical-align: middle; padding-right: 12px;">
                            <div class="header-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2B1E16" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                                </svg>
                            </div>
                        </td>
                        <td style="vertical-align: middle; text-align: left;">
                            <div class="header-title">Hamzah Style Official</div>
                            <div class="header-subtitle">Warisan Batik Nusantara Modern</div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Body --}}
            <div class="body">

                {{-- Email icon --}}
                <div class="email-icon-wrap">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#2B1E16" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>

                <h1 class="greeting">Verifikasi Email Anda</h1>

                <p class="subtext">
                    Halo, <strong>{{ $user->name }}</strong>! 👋<br><br>
                    Terima kasih telah mendaftar di <strong>Hamzah Style Official</strong>.
                    Untuk mengaktifkan akun Anda dan mulai berbelanja, silakan verifikasi alamat email Anda dengan menekan tombol di bawah ini.
                </p>

                <div class="btn-wrap">
                    <a href="{{ $url }}" class="btn">
                        ✉&nbsp;&nbsp;Verifikasi Email Sekarang
                    </a>
                </div>

                <p class="subtext" style="margin-bottom: 0;">
                    Jika Anda tidak mendaftar di Hamzah Style Official, abaikan email ini. Tidak ada tindakan yang diperlukan.
                </p>

                <hr class="divider">

                <p class="fallback-label">Atau salin tautan berikut ke browser Anda:</p>
                <div class="fallback-url">{{ $url }}</div>

                <p class="expiry-note">
                    ⏱ Tautan ini akan kedaluwarsa dalam <strong>60 menit</strong>.
                </p>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <p class="footer-brand">Hamzah Style Official</p>
                <p class="footer-text">
                    Email ini dikirim secara otomatis. Mohon tidak membalas email ini.<br>
                    © {{ date('Y') }} Hamzah Style Official. Conscious Craftsmanship.
                </p>
            </div>

        </div>
    </div>
</body>
</html>

