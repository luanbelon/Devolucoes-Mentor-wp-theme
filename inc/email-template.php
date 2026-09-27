<?php
/**
 * Template HTML para emails transacionais
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_email_brand_color(): string
{
    return '#04445f';
}

function devolucoes_mentor_email_logo_url(): string
{
    return 'https://devolucoes.mentor-company.pt/wp-content/uploads/2026/07/LOGO-MENTOR-VERTICAL-WHITE-e1699441070441-1-scaled-2048x430-1.png';
}

function devolucoes_mentor_email_company_name(): string
{
    return 'Mentor Company';
}

function devolucoes_mentor_email_company_url(): string
{
    return 'https://mentor-company.pt/';
}

function devolucoes_mentor_render_email_template(string $content, array $args = []): string
{
    $brand_color = devolucoes_mentor_email_brand_color();
    $logo_url    = esc_url($args['logo_url'] ?? devolucoes_mentor_email_logo_url());
    $company     = esc_html($args['company'] ?? devolucoes_mentor_email_company_name());
    $site_url    = esc_url($args['site_url'] ?? devolucoes_mentor_email_company_url());
    $year        = esc_html((string) gmdate('Y'));

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company; ?></title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .dm-email-content h1,
        .dm-email-content h2,
        .dm-email-content h3 {
            margin: 0 0 16px;
            color: <?php echo esc_attr($brand_color); ?>;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 700;
            line-height: 1.3;
        }
        .dm-email-content h2 { font-size: 20px; }
        .dm-email-content p {
            margin: 0 0 14px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            color: #333333;
        }
        .dm-email-content ul {
            margin: 0 0 14px 20px;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            color: #333333;
        }
        .dm-email-content li { margin-bottom: 6px; }
        .dm-email-content hr {
            border: 0;
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
        }
        .dm-email-content strong { color: #1a1a1a; }
        @media only screen and (max-width: 620px) {
            .dm-email-shell { width: 100% !important; }
            .dm-email-body { padding: 24px 20px !important; }
            .dm-email-header { padding: 24px 20px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f3f5f7;">
    <table id="dm-email-template" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f3f5f7;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table class="dm-email-shell" role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <tr>
                        <td class="dm-email-header" align="center" bgcolor="<?php echo esc_attr($brand_color); ?>" style="background-color:<?php echo esc_attr($brand_color); ?>;padding:32px 24px;">
                            <a href="<?php echo $site_url; ?>" target="_blank" style="text-decoration:none;">
                                <img src="<?php echo $logo_url; ?>" alt="<?php echo $company; ?>" width="200" height="42" style="display:block;margin:0 auto;width:200px;max-width:200px;height:auto;border:0;outline:none;">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="dm-email-body dm-email-content" style="padding:36px 32px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#333333;">
                            <?php echo $content; ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" bgcolor="#f8fafb" style="background-color:#f8fafb;padding:24px 32px;border-top:1px solid #e8ecef;">
                            <p style="margin:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.5;color:#6b7280;">
                                <a href="<?php echo $site_url; ?>" target="_blank" style="color:<?php echo esc_attr($brand_color); ?>;text-decoration:none;font-weight:600;"><?php echo $company; ?></a>
                            </p>
                            <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;color:#9ca3af;">
                                &copy; <?php echo $year; ?> <?php echo $company; ?>. Todos os direitos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
    <?php
    return (string) ob_get_clean();
}

function devolucoes_mentor_prepare_email_body(string $content): string
{
    $content = trim($content);

    if ($content === '') {
        return $content;
    }

    if (str_contains($content, 'id="dm-email-template"')) {
        return $content;
    }

    return devolucoes_mentor_render_email_template($content);
}
