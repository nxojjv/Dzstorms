<?php

// Função para enviar emails reais pelo DZStorms
function enviar_email($para, $assunto, $mensagem) {
  $headers = "From: DZStorms <dzstorms7@gmail.com>\r\n";
  $headers .= "Reply-To: dzstorms7@gmail.com\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

  return mail($para, $assunto, $mensagem, $headers);
}