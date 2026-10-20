<?php

namespace App\Shared\Util;

use App\Shared\Util\PHPMailer;
use App\Shared\Util\Exception;
use App\Shared\Util\SMTP;

class Utilidades {
    public static function generarGUID() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        $hexData = bin2hex($data);
        $guid = substr($hexData, 0, 8) . '-' . substr($hexData, 8, 4) . '-' . substr($hexData, 12, 4) . '-' . substr($hexData, 16, 4) . '-' . substr($hexData, 20, 12);

        return $guid;
    }

    public static function enviarCorreo($destinatario, $asunto, $titulo, $contenidoHtml)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'mail.hacebwhirlpoolindustrial.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hwiverificacion@hacebwhirlpoolindustrial.com';
            $mail->Password   = 'HWI2023*';
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            $mail->setFrom('hwiverificacion@hacebwhirlpoolindustrial.com', 'Equipo BI');
            $mail->addAddress($destinatario);
            $mail->isHTML(true);
            $mail->Subject = $asunto;

            // URL del logo (Asegúrate que esta imagen tenga el fondo blanco/transparente como la foto)
            $logoUrl = "https://sistemaevaluacioncontratistas.hacebwhirlpoolindustrial.com/Evaluador_HWI/Imagenes/LogoBlancoHWI.png";

            $mail->Body = '
            <html>
            <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
                <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    
                    <div style="text-align: center; padding: 30px 20px; border-bottom: 4px solid #005691;">
                        <img src="' . $logoUrl . '" alt="Haceb Whirlpool" style="width: 200px; height: auto;">
                    </div>

                    <div style="padding: 30px; color: #333333; line-height: 1.6;">
                        <h2 style="text-align: center; color: #222222; margin-bottom: 25px; font-weight: bold;">
                            ' . $titulo . '
                        </h2>
                        
                        <div style="font-size: 15px;">
                            ' . $contenidoHtml . '
                        </div>
                    </div>

                    <div style="text-align: center; padding: 20px; background-color: #f9f9f9; color: #888888; font-size: 12px; border-top: 1px solid #eeeeee;">
                        <p style="margin: 0;">Copyright © Haceb Whirlpool Industrial S.A.S</p>
                    </div>
                </div>
            </body>
            </html>
        ';

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar el correo electrónico: {$mail->ErrorInfo}");
            return false;
        }
    }

    function generarPasswordTemporal($longitud = 8) {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $contrasena = '';
        for ($i = 0; $i < $longitud; $i++) {
            $contrasena .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        return $contrasena;
    }
}
?>
