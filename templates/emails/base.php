<?php
$siteName = SITE_NAME;
$siteUrl = SITE_URL;
$year = date("Y");
$preheader = $preheader ?? "";
$footer_extra = $footer_extra ?? "";
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333; }
        .header { background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%); padding: 40px 30px; text-align: center; }
        .header-danger { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); }
        .header-success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
        .logo { font-size: 32px; font-weight: bold; color: #fff; text-decoration: none; letter-spacing: 1px; }
        .header-subtitle { color: rgba(255,255,255,0.9); font-size: 14px; margin-top: 8px; text-transform: uppercase; letter-spacing: 2px; }
        .content { padding: 40px 30px; background: #fff; }
        .greeting { font-size: 22px; font-weight: 600; color: #1a5276; margin-bottom: 20px; }
        .text { color: #555; margin-bottom: 20px; }
        .button-container { text-align: center; margin: 30px 0; }
        .button { display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%); color: #fff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; }
        .button-danger { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); }
        .button-success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
        .info-box { background: #e8f4fc; border-left: 4px solid #2980b9; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .warning-box { background: #fef9e7; border-left: 4px solid #f39c12; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .danger-box { background: #fdedec; border-left: 4px solid #e74c3c; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .success-box { background: #eafaf1; border-left: 4px solid #27ae60; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .box-title { font-weight: 600; color: #333; margin-bottom: 8px; }
        .box-text { color: #666; font-size: 14px; margin: 0; }
        .details-table { width: 100%; margin: 20px 0; border-collapse: collapse; }
        .details-table td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        .details-table .label { font-weight: 600; color: #1a5276; width: 40%; }
        .details-table .value { color: #555; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #eee; }
        .footer-text { color: #888; font-size: 13px; margin: 5px 0; }
        .footer-links { margin: 15px 0; }
        .footer-links a { color: #1a5276; text-decoration: none; margin: 0 10px; font-size: 13px; }
        .text-muted { color: #888; }
        .text-small { font-size: 13px; }
        @media screen and (max-width: 600px) { .content, .header { padding: 30px 20px !important; } }
    </style>
</head>
<body>
    <?php if ($preheader): ?>
    <div style="display:none;max-height:0;overflow:hidden;"><?= htmlspecialchars($preheader) ?></div>
    <?php endif; ?>
    <table role="presentation" width="100%" style="background:#f4f4f4;">
        <tr><td style="padding:20px 10px;">
            <table role="presentation" style="margin:0 auto;max-width:600px;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                <tr><td class="header <?= $header_class ?? '' ?>">
                    <a href="<?= $siteUrl ?>" class="logo">🚗 <?= $siteName ?></a>
                    <div class="header-subtitle"><?= htmlspecialchars($title) ?></div>
                </td></tr>
                <tr><td class="content"><?= $content ?></td></tr>
                <tr><td class="footer">
                    <?php if ($footer_extra): ?><p class="footer-text"><?= $footer_extra ?></p><?php endif; ?>
                    <div class="footer-links">
                        <a href="<?= $siteUrl ?>">Site</a>
                        <a href="<?= $siteUrl ?>/veiculos.php">Veículos</a>
                        <a href="<?= $siteUrl ?>/contacto.php">Contacto</a>
                    </div>
                    <p class="footer-text"><?= $siteName ?> - O seu stand de confiança</p>
                    <p class="footer-text">&copy; <?= $year ?> <?= $siteName ?>. Todos os direitos reservados.</p>
                    <p class="footer-text text-small" style="margin-top:20px;">Email automático - não responda.</p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>