CcQueryTool belirli bir sipariş numarasına ait borç, alacak bakiye verisini sorgulamak için geliştirilmiş bir kütüphanedir.
Bu kütüphaneyi kullanarak Est, Posnet ve Garanti Sanal pos altyapıları üzerinde sorgulama gerçekleştirebilirsiniz.

Örnek:
-----------------
$options = array(
            'url'           => 'https://sanalposprov.garanti.com.tr/VPServlet',
            'username'      => 'PROVAUT',
            'password'      => '123456',
            'client_id'     => '9990000',
            'terminal_id'   => '10100000'
        );

$adapter = 'Gvp';

$bank       = new CcQuery_Context($adapter, $options);
$request    = new CcQuery_Query_Request();

$request->orderId = '200089721';

$result = $bank->sendQuery($request);

print_r($result);

Sonuç:
-------------------------
CcQuery_Query_Response Object
(
     [success] => 1
     [paid] => 184.93
     [refunded] => 46.15
     [balance] => 138.78
 )

