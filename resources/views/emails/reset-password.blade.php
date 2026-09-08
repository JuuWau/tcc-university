<!doctype html>
<html lang="pt-BR">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fb; color: #334155; font-family: Arial, Helvetica, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f4f7fb; padding: 40px 12px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; max-width: 600px;">
                <tr>
                    <td align="center" style="padding-bottom: 24px;">
                        <img src="{{ asset('images/logo-acadent.png') }}" width="180" alt="{{ config('app.name') }}" style="display: block; width: 180px; max-width: 100%; height: auto; border: 0;">
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #ffffff; border: 1px solid #e2e8f0; padding: 40px;">
                        <h1 style="margin: 0 0 24px; color: #0f172a; font-size: 24px; line-height: 32px;">Olá!</h1>
                        <p style="margin: 0 0 16px; font-size: 16px; line-height: 24px;">Recebemos uma solicitação para redefinir a senha da sua conta.</p>
                        <p style="margin: 0 0 28px; font-size: 16px; line-height: 24px;">Clique no botão abaixo para criar uma nova senha.</p>
                        <table cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto 28px;">
                            <tr>
                                <td style="border-radius: 8px; background-color: #0284c7;">
                                    <a href="{{ $url }}" target="_blank" rel="noopener" style="display: inline-block; padding: 14px 24px; color: #ffffff; font-size: 15px; font-weight: bold; line-height: 20px; text-decoration: none;">Redefinir minha senha</a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin: 0 0 16px; font-size: 14px; line-height: 22px;">Este link será válido por 60 minutos.</p>
                        <p style="margin: 0 0 24px; font-size: 14px; line-height: 22px;">Se você não solicitou a redefinição da senha, nenhuma ação é necessária.</p>
                        <p style="margin: 0; font-size: 14px; line-height: 22px;">Atenciosamente,<br><strong>{{ config('app.name') }}</strong></p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top: 24px; color: #64748b; font-size: 12px; line-height: 18px;">
                        <strong>{{ config('app.name') }}</strong><br>
                        Sistema de Gestão Odontológica<br>
                        © {{ date('Y') }} — Todos os direitos reservados.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
