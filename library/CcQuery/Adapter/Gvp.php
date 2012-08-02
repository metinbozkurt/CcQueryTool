<?php
class CcQuery_Adapter_Gvp extends CcQuery_Adapter_Abstract
{
    /**
     * returns xml data for order query.
     * @param Bob_Query_Request $request
     * @return string
     */
    protected function _getQueryRequestXml(CcQuery_Query_Request $request)
    {
        return "data=<GVPSRequest>
            <Mode>PROD</Mode>
            <Version>v0.01</Version>
            <Terminal>
            <ProvUserID>{$this->_config['username']}</ProvUserID>
            <HashData>{$this->_getHashData($request)}</HashData>
            <UserID>{$this->_config['username']}</UserID>
            <ID>{$this->_config['terminal_id']}</ID>
            <MerchantID>{$this->_config['client_id']}</MerchantID>
            </Terminal>
            <Customer>
            <IPAddress></IPAddress>
            <EmailAddress></EmailAddress>
            </Customer>
            <Order>
            <OrderID>{$request->orderId}</OrderID>
            <GroupID></GroupID>
            </Order>
            <Transaction>
            <Type>orderhistoryinq</Type>
            <InstallmentCnt></InstallmentCnt>
            <Amount>1</Amount>
            <CurrencyCode>949</CurrencyCode>
            <CardholderPresentCode>0</CardholderPresentCode>
            <MotoInd>N</MotoInd>
            </Transaction>
            </GVPSRequest>";
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
            if($bankResponse->Transaction->Response->Message == 'Approved') {
                $response->success = true;
            } else {
                $response->success = false;
            }
        } catch(Exception $e) {
            $response->success = false;
        }
        
        if($response->success) {
            if( isset($bankResponse->Order) &&
                    isset($bankResponse->Order->OrderHistInqResult) &&
                    isset($bankResponse->Order->OrderHistInqResult->OrderTxnList) &&
                    count($bankResponse->Order->OrderHistInqResult->OrderTxnList->OrderTxn) ) {

                $balanceData = $this->_calculateBalance($bankResponse->Order->OrderHistInqResult->OrderTxnList->OrderTxn);
                
                $response->paid     = $balanceData['paid'];
                $response->refunded = $balanceData['refunded'];
                $response->balance  = $balanceData['balance'];
            } else {
                $response->success = false;
            }
        }

                
        return $response;
    }

    /**
     * calculates total paid, refunded and balance data.
     * @param stdClass $transactions
     * @return array
     */
    private function _calculateBalance($transactions)
    {
        $paid       = 0;
        $refunded   = 0;
        foreach( $transactions as $transaction) {
            if( strtolower($transaction->Type) == 'sales' ) {
                $paid += (float) $this->_formatAmount($transaction->AuthAmount);
            } elseif(  strtolower($transaction->Type) == 'refund') {
                $refunded += (float) $this->_formatAmount($transaction->AuthAmount);

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
     * returns formatted (completed to 24 characters) order number 
     * @param string $orderId
     * @return string
     */
    private function _formatOrderId($orderId)
    {
        if(strlen($orderId) < 24) {
            return str_repeat('0', 24 - strlen($orderId) ) . $orderId;
        } else {
            return $orderId;
        }
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

    /**
     * generate and returns hash data.
     * @param PaymentExt_Model_Transaction_Request $request 
     */
    private function _getHashData( CcQuery_Query_Request $request )
    {
        $originalTerminalId   = $this->_config['terminal_id'];
        $terminalIdLength     = strlen( $originalTerminalId );
        $terminalId           = str_repeat("0", 9 - $terminalIdLength) . $originalTerminalId;

        $amount               = 1;

        $securityData         = array( $this->_config['password'], 
                $terminalId );



        $securityDataSha1     = strtoupper( sha1( implode('', $securityData) )  );

        $hashData             = array( $request->orderId,
                $originalTerminalId, 
                '',
                $amount,
                $securityDataSha1 );


        $hashSha1             = strtoupper( sha1( implode('', $hashData) ) );

        return $hashSha1;
    }
}
