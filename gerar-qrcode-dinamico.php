<?php
require __DIR__.'/vendor/autoload.php';

require __DIR__.'/config-pix.php';
use App\Pix\Api;
use App\Pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;


//INSTANCIA DA API PIX
$obApiPix = new Api(API_PIX_URL,
                    API_PIX_CLIENT_ID,
                    API_PIX_CLIENT_SECRET);

print 'AQUI geração do qrcode';


//CORPO DA REQUISIÇÃO
$request = [
  "calendario" =>  [
    "dataDeVencimento" => [
      "2023-12-31"
    ], 
  ],
  "devedor" => [
    "cpf" => "12345678909",
    "nome" => "Francisco da Silva"
  ],
  "valor" => [
    "original" => "22.50"
  ],
  "chave" => PIX_KEY,
  "solicitacaoPagador" => "Pagamento mês de agosto"
];

//RESPOSTA DA REQUISIÇÃO DE CRIAÇÃO
$response = $obApiPix->createCob('88ba8ec675e044178d434908d9b2a31a',$request);

//VERIFICA A EXISTÊNCIA DO ITEM 'LOCATION'
if(!isset($response['location'])){
  echo 'Problemas ao gerar Pix dinâmico';
  echo "<pre>";
  print_r($response);
  echo "</pre>"; exit;
}

//INSTANCIA PRINCIPAL DO PAYLOAD PIX
$obPayload = (new Payload)->setMerchantName(PIX_MERCHANT_NAME)
                          ->setMerchantCity(PIX_MERCHANT_CITY)
                          ->setAmount($response['valor']['original'])
                          ->setTxid('***')
                          ->setUrl($response['location'])
                          ->setUniquePayment(true);

//CÓDIGO DE PAGAMENTO PIX
$payloadQrCode = $obPayload->getPayload();


//QR CODE
$obQrCode = new QrCode($payloadQrCode);

//IMAGEM DO QRCODE
$image = (new Output\Png)->output($obQrCode,400);

?>

<h1>QR CODE DINÂMICO DO PIX</h1>

<br>

<img src="data:image/png;base64, <?=base64_encode($image)?>">

<br><br>

Código pix:<br>
<strong><?=$payloadQrCode?></strong>

