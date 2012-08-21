## CcQueryTool Nedir ?
CcQueryTool belirli bir sipariş numarasına ait borç, alacak bakiye verisini sorgulamak için geliştirilmiş bir kütüphanedir.
Bu kütüphaneyi kullanarak Est, Posnet ve Garanti Sanal pos altyapıları üzerinde sorgulama gerçekleştirebilirsiniz.

## Örnek Kullanım
```php

<?php
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
?>

```

## Sonuç
```php
CcQuery_Query_Response Object
(
     [success] => 1
     [paid] => 184.93
     [refunded] => 46.15
     [balance] => 138.78
 )
```
##Licence

Copyright (C) 2012 İbrahim Gündüz

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

