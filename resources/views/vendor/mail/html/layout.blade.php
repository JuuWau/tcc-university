<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>{{ config('app.name') }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta
        http-equiv="Content-Type"
        content="text/html; charset=UTF-8"
    />

    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">

    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            background-color: #f4f7fb;
            color: #334155;
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;
        }

        table {
            border-collapse: collapse;
        }

        .wrapper {
            width: 100%;
            margin: 0;
            padding: 40px 0;
            background-color: #f4f7fb;
        }

        .content {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            padding: 0 0 24px;
            text-align: center;
        }

        .logo {
            max-width: 180px;
            height: auto;
            border: 0;
        }

        .inner-body {
            width: 570px;
            max-width: 100%;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        }

        .content-cell {
            padding: 40px;
        }

        .footer {
            width: 570px;
            max-width: 100%;
            margin: 0 auto;
            padding: 24px 0 0;
            text-align: center;
        }

        .footer p {
            margin: 0;
            color: #94a3b8;
            font-size: 12px;
            line-height: 18px;
        }

        .footer .company-name {
            color: #64748b;
            font-weight: 600;
        }

        .footer .description {
            margin-top: 4px;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            background-color: #0284c7;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        @media only screen and (max-width: 600px) {
            .wrapper {
                padding: 20px 12px !important;
            }

            .content {
                width: 100% !important;
            }

            .inner-body {
                width: 100% !important;
                border-radius: 12px !important;
            }

            .content-cell {
                padding: 28px 22px !important;
            }

            .footer {
                width: 100% !important;
            }

            .header {
                padding-bottom: 18px !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
                box-sizing: border-box;
                text-align: center;
            }
        }
    </style>

    {!! $head ?? '' !!}
</head>

<body>

<table
    class="wrapper"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
>
    <tr>
        <td align="center">

            <table
                class="content"
                width="100%"
                cellpadding="0"
                cellspacing="0"
                role="presentation"
            >

                @if (isset($header))
                    {!! $header !!}
                @else
                    <tr>
                        <td class="header">
                            <img
                                src="{{ asset('images/logo-acadent.png') }}"
                                alt="{{ config('app.name') }}"
                                class="logo"
                            >
                        </td>
                    </tr>
                @endif

                <tr>
                    <td
                        class="body"
                        width="100%"
                        cellpadding="0"
                        cellspacing="0"
                        style="border: hidden !important;"
                    >

                        <table
                            class="inner-body"
                            align="center"
                            cellpadding="0"
                            cellspacing="0"
                            role="presentation"
                        >
                            <tr>
                                <td class="content-cell">

                                    {!! Illuminate\Mail\Markdown::parse($slot) !!}

                                    {!! $subcopy ?? '' !!}

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                @if (isset($footer))
                    {!! $footer !!}
                @else
                    <tr>
                        <td>
                            <table
                                class="footer"
                                align="center"
                                cellpadding="0"
                                cellspacing="0"
                                role="presentation"
                            >
                                <tr>
                                    <td>
                                        <p>
                                            <span class="company-name">
                                                {{ config('app.name') }}
                                            </span>
                                        </p>

                                        <p class="description">
                                            Sistema de Gestão Odontológica
                                        </p>

                                        <p class="description">
                                            © {{ date('Y') }} —
                                            Todos os direitos reservados.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

            </table>

        </td>
    </tr>
</table>

</body>

</html>
