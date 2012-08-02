<?php
abstract class CcQuery_Adapter_Abstract implements CcQuery_Adapter_Interface
{
    protected $_config;

    public function __construct($config)
    {
        $this->_config = $config;
    }

    protected function _post($url, $data)
    {
        $curl = curl_init();
        
        $options = array(
                    CURLOPT_URL             => $url,
                    CURLOPT_POST            => true,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_SSL_VERIFYPEER  => false,
                    CURLOPT_SSL_VERIFYHOST  => true,
                    CURLOPT_HEADER          => false,
                    CURLOPT_POSTFIELDS      => $data
                );

        curl_setopt_array($curl, $options);
        try {
            $returnData = curl_exec($curl);
            curl_close($curl);
            return $returnData;
        } catch(Exception $e) {
            return false;
        }
    }

    abstract protected function _getQueryRequestXml(CcQuery_Query_Request $request);
    abstract protected function _processResponseMessage($data);

    public function sendQuery(CcQuery_Query_Request $request)
    {
        $requestXml = $this->_getQueryRequestXml($request);
        $response = $this->_post($this->_config['url'], $requestXml);
        return $this->_processResponseMessage($response);
    }
}
