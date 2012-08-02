<?php
class CcQuery_Adapter_Posnet extends CcQuery_Adapter_Abstract
{
    /**
     * returns xml data for order query.
     * @param Bob_Query_Request $request
     * @return string
     */
    protected function _getQueryRequestXml(CcQuery_Query_Request $request)
    {
        return "xmldata=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                    <posnetRequest>
                        <mid>{$this->_config['client_id']}</mid>
                        <tid>{$this->_config['terminal_id']}</tid>
                        <username>{$this->_config['username']}</username>
                        <password>{$this->_config['password']}</password>
                        <agreement>
                            <orderID>{$this->_formatOrderId($request->orderId)}</orderID>
                        </agreement>
                    </posnetRequest>";
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
            if($bankResponse->approved) {
                $response->success = true;
            } else {
                $response->success = false;
            }
        } catch(Exception $e) {
            $response->success = false;
        }
        
        if($response->success) {
            if( isset($bankResponse->transactions) &&
                    count($bankResponse->transactions) ) {
                $balanceData = $this->_calculateBalance($bankResponse->transactions);
                
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

        foreach((array) $transactions as $transaction) {
            if( strtolower($transaction->state) == 'sale' ) {
                $paid += (float) $transaction->amount;
            } elseif(  strtolower($transaction->state) == 'refund') {
                $refunded += (float) $transaction->amount;
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
}
