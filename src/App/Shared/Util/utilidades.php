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

    public function enviarCorreo($destinatario, $asunto, $titulo, $contenidoHtml){
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'mail.hacebwhirlpoolindustrial.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hwiverificacion@hacebwhirlpoolindustrial.com';
            $mail->Password   = 'HWI2023*';
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->setFrom('hwiverificacion@hacebwhirlpoolindustrial.com', 'Equipo BI');
            $mail->addAddress($destinatario);
            $mail->isHTML(true);
            $mail->Subject = $asunto;

            $logoUrl = "https://sistemaevaluacioncontratistas.hacebwhirlpoolindustrial.com/Evaluador_HWI/Imagenes/LogoBlancoHWI.png";

            $mail->Body = '
                <div style="border-radius:10px; border: 1px solid #cccccc; max-width: 100%; max-height: 100%; margin-top: 50px; margin-left: auto; margin-right: auto; text-align: center; padding: 20px;">
                    <div style="text-align: center;">
                        <div style="display: inline-block; border-radius: 10px; max-width: 200px; padding: 10px;">
                            <img src="' . $logoUrl . '" alt="Logo Empresa" style="max-width: 100%; height: auto; border-radius: 50%; border: 1px solid #cccccc;">
                        </div>
                    </div>
                    <h4 style="margin-top: 20px;">' . $titulo . '</h4>
                    <hr style="background-color: #cccccc; border: none; height: 1px; width: 100%; margin-top: 20px; margin-bottom: 20px;">
                    <div style="width: 95%; text-align: justify; margin-left: auto; margin-right: auto;">
                        ' . $contenidoHtml . '
                    </div>
                </div>
                <div style="text-align: center;">
                    <p style="color: #999999;">Copyright © Haceb Whirlpool Industrial S.A.S</p>
                </div>
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
