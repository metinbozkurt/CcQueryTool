<?php
define('APPLICATION_PATH', dirname(__FILE__));
include('library/loader.php');

Loader::getInstance();

//banka apisi ile ilgili erisim bilgilerini asagidaki diziye doldurun.
$options = array(
            'url'           => 'https://sanalposprov.garanti.com.tr/VPServlet',
            'username'      => 'PROVAUT',
            'password'      => '123456',
            'client_id'     => '9990000',
            'terminal_id'   => '10100000'
        );

/*
bankanıza ait adaptor tanimlamasini yapin.

Adaptorler
----------
Est     : Isbank, Akbank, Finansbank, Hsbc
Posnet  : YapiKredi, AnadoluBank, VakifBank
Gvp     : Garanti Bankası
*/

$adapter = 'Gvp';

$bank       = new CcQuery_Context($adapter, $options);
$request    = new CcQuery_Query_Request();

$request->orderId = '200089721';

$result = $bank->sendQuery($request);
print_r($result);
?>
