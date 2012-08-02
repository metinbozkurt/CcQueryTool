<?php
class CcQuery_Adapter_Est extends CcQuery_Adapter_Abstract
{
    /**
     * returns xml data for order query.
     * @param Bob_Query_Request $request
     * @return string
     */
    protected function _getQueryRequestXml(CcQuery_Query_Request $request)
    {
        return "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
            <CC5Request>
            <Name>{$this->_config['username']}</Name>
            <Password>{$this->_config['password']}</Password>
            <ClientId>{$this->_config['client_id']}</ClientId>
            <OrderId>{$request->orderId}</OrderId>
            <Extra>
            <ORDERHISTORY>QUERY</ORDERHISTORY>
            </Extra>
            </CC5Request>";
    }

    /**
     * processes returned result from bank.
     * @param string $data
     * @return Bob_Query_Response 
     */
    protected function _processResponseMessage($data)
    {
        $response = new CcQuery_Query_Response();
        try {
            $bankResponse = new SimpleXMLElement($data);
            if($bankResponse->Response == 'Approved') {
                $response->success = true;
            } else {
                $response->success = false;
            }
        } catch(Exception $e) {
            $response->success = false;
        }
        if($response->success) {
            if( isset($bankResponse->Extra->TRXCOUNT) && 
                    $bankResponse->Extra->TRXCOUNT > 0 ) {
                $balanceData = $this->_calculateBalance($bankResponse->Extra->TRXCOUNT, 
                                                        $bankResponse->Extra);
                $response->paid     = $balanceData['paid'];
                $response->refunded = $balanceData['refunded'];
                $respoonse->balance = $balanceData['balance'];
            } else {
                $response->success = false;
            }
        }
        
        return $response;
    }

    /**
     * calculates total paid, refunded and balance data.
     * @param integer $transactionCount
     * @param stdClass $rows
     * @return array
     */
    private function _calculateBalance($transactionCount, $rows)
    {
        $paid       = 0;
        $refunded   = 0;

        for($i = 1; $i <= $transactionCount; $i++) {
            if( isset($rows->{'TRX' . $i}) ) {
                $data           = $rows->{'TRX' . $i};
                $parsedRowData  = $this->_parseTransactionData($data);

                if( $parsedRowData['type'] == 'sale' ) {
                    $paid       += $parsedRowData['amount'];
                } else {
                    $refunded   += $parsedRowData['amount'];
                }
            }
        }

        $balance = $paid - $refunded;

        return array(
                'paid'      => $paid,
                'refunded'  => $refunded,
                'balance'   => $balance
                );
    }

    /**
     * returns parsed transaction data.
     * @param string $row
     * @param array
     */
    private function _parseTransactionData($row)
    {
        $data = explode("\t", $row);
        $data = array_map(function($value) {return trim($value); }, $data);
        $type = '';
        
        if($data[0] == 'S') {
            $type = 'sale';
        } elseif($data[0] == 'C') {
            $type = 'refund';
        }

        $amount = $this->_formatAmount($data[3]);

        return array(
                    'type'      => $type,
                    'amount'    => $amount
                );
    }

    /**
     * format number as float (for example: xxx.xx)
     * @param string 
     * @return float
     */
    private function _formatAmount($amount)
    {
        $number = substr($amount, 0, -2) . 
            '.' .
            substr($amount, -2);

        return (float) $number;
    }
}
