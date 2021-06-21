<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Content-language" content="no-bokmaal"/>
    <meta name="viewport" content="initial-scale=1.0">    <!-- So that mobile webkit will display zoomed in -->
    <meta name="format-detection" content="telephone=no"> <!-- disable auto telephone linking in iOS -->

    <title>Mitra Yatim</title>
    <style type="text/css">

        /* Resets: see reset.css for details */
        .ReadMsgBody {
            width: 100%;
            background-color: #edeff0;
        }

        .ExternalClass {
            width: 100%;
            background-color: #edeff0;
        }

        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
            line-height: 100%;
        }

        body {
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }

        body {
            margin: 0;
            padding: 0;
        }

        table {
            border-spacing: 0;
        }

        table td {
            border-collapse: collapse;
        }

        .yshortcuts a {
            border-bottom: none !important;
        }

        /* Constrain email width for small screens */
        @media screen and (max-width: 600px) {
            table[class="container"] {
                width: 95% !important;
            }
        }

        /* Give content more room on mobile */
        @media screen and (max-width: 480px) {
            td[class="container-padding"] {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
        }

    </style>
</head>
<body style="margin:0; padding:10px 0;" bgcolor="#edeff0" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<br>

<!-- 100% wrapper (grey background) -->
<table border="0" width="100%" height="100%" style="padding-top: 60px;padding-bottom: 60px;" cellpadding="0" cellspacing="0" bgcolor="#edeff0">
    <tr>
        <td align="center" valign="top" bgcolor="#edeff0" style="background-color: #edeff0;">

            <!-- 600px container (white background) -->
            <table border="0" width="600" cellpadding="0" cellspacing="0" class="container" bgcolor="#ffffff">
                <tr>
                    <td colspan="5" class="container-padding" bgcolor="#ffffff" style="background-color: #ffffff; font-size: 14px; font-family: Helvetica, sans-serif; color: #333;padding:20px;">
                        <img src="https://www.mitrayatim.org/resources/members/mitrayatim/org/logo.png" alt=""/>

                    </td>
                </tr>
                <tr>

                    <td colspan="5" class="container-padding" bgcolor="#ffffff" style="border-bottom: 1px solid #edeff0;padding-left: 20px;padding-right: 20px;padding-top: 20px;padding-bottom: 40px;background-color: #ffffff; font-size: 14px; font-family: Helvetica, sans-serif; color: #333;">

                        <p style="color:#6b6b6b;line-height: 24px;font-family: Helvetica, sans-serif;font-weight: 300;font-size: 14px;">
                            Halo sahabat <b>{{ $donation->donor_name }}</b>
                            <br/>
                            Berikut update dari campaign <span style="font-weight:bold;">{{ $campaignProgress->campaign->campaign_title }}.</span>

                        </p>

                        <p style="margin:40px 0 10px 0;color:#6b6b6b;line-height: 24px;font-family: Helvetica, sans-serif;font-weight: 300;font-size: 14px;">

                            @if($campaignProgress->news_image)
                            <img src="{{ 'https://core.mitrayatim.org/api/image/' . $campaignProgress->news_image }}">
                            @endif

                        </p>

                        <p style="margin:40px 0 10px 0;color:#6b6b6b;line-height: 24px;font-family: Helvetica, sans-serif;font-weight: 300;font-size: 14px;">
                            Story lengkapnya bisa dilihat di <a href="{{ 'https://www.mitrayatim.org/id/post/' . $campaignProgress->news_slug }}">link berikut</a>
                        </p>
                        <p style="mmargin:40px 0 10px 0;color:#6b6b6b;line-height: 24px;font-family: Helvetica, sans-serif;font-weight: 300;font-size: 14px;">
                            Terima kasih sudah menjadi bagian dari sahabat pedalaman.
                        </p>

                        <p style="margin:40px 0 10px 0;color:#6b6b6b;line-height: 24px;font-family: Helvetica, sans-serif;font-weight: 300;font-size: 14px;">
                            Bantu campaign lainnya https://www.mitrayatim.org/id/campaign
                        </p>


                        <p style="color:#6b6b6b;line-height: 24px;font-family: Helvetica, sans-serif;font-weight: 300;font-size: 14px;">
                            Salam Hangat,
                        <hr style="margin:10px 0; border:none;">
                        <hr style="margin:10px 0; border:none;">
                        <span style="color:#6b6b6b;">Muhammad Ibrahim Daud</span>
                        <hr style="margin:20px 0; border:none;">
                        <span style="color:#6b6b6b;font-style:italic;">Customer Service</span>
                        <hr style="margin:10px 0; border:none;">
                        <span style="color:#6b6b6b;">Mitra Yatim</span>
                        </p>


                    </td>
                </tr>
            </table>

            <table border="0" width="600" cellpadding="0" cellspacing="0" class="container" bgcolor="#edeff0">
                <tr>
                    <td class="container-padding" bgcolor="#edeff0" style="background-color: #edeff0; text-align: center; padding-top: 40px;font-size: 14px; font-family: Helvetica, sans-serif; color: #888d90;">
                        © Mitra Yatim {{ date('Y') }}
                    </td>
                </tr>

            </table>

            <!--/600px container -->

        </td>
    </tr>
</table>
<!--/100% wrapper-->
<br>
<br>
</body>
</html>
