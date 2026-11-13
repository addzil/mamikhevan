<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $replySubject }}</title>
</head>
<body style="margin:0;padding:0;background-color:#fffdf4;font-family:Arial,Helvetica,sans-serif;color:#292524;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fffdf4;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border:1px solid #fef3c7;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#f59e0b;padding:24px 28px;">
                            <p style="margin:0;font-size:20px;font-weight:bold;color:#ffffff;">Mamikhevanshop</p>
                            <p style="margin:4px 0 0;font-size:12px;color:#fffbeb;">Pakaian anak lucu &amp; nyaman</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px;font-size:15px;line-height:24px;">Halo, <strong>{{ $contactMessage->name }}</strong>!</p>

                            <div style="font-size:14px;line-height:24px;color:#44403c;">{!! nl2br(e($replyBody)) !!}</div>

                            <p style="margin:28px 0 8px;font-size:13px;font-weight:bold;color:#78716c;">Pesan asli kamu:</p>
                            <div style="border-left:3px solid #fcd34d;background-color:#fffbeb;padding:12px 16px;border-radius:0 8px 8px 0;">
                                <p style="margin:0 0 4px;font-size:12px;font-weight:bold;color:#92400e;">{{ $contactMessage->subject }}</p>
                                <p style="margin:0;font-size:13px;line-height:22px;color:#57534e;white-space:pre-line;">{{ $contactMessage->message }}</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #fef3c7;padding:20px 28px;">
                            <p style="margin:0 0 6px;font-size:12px;font-weight:bold;color:#292524;">Mamikhevanshop</p>
                            <p style="margin:0;font-size:11px;line-height:18px;color:#a8a29e;">
                                Jl. Lapangan Kobra 1 No.118, Tambun Selatan, Kab. Bekasi<br>
                                WhatsApp 085782194580 &middot; adlipurna@gmail.com
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
