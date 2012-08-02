<?php
class CcQuery_Context
{
    private $_adapter;
    
    public function __construct($adapter, $config)
    {
        if( !$this->_isValidAdapter($adapter) ) {
            throw new Exception('Invalid adapter name ' . $adapter);
        }

        $adapterClass = $this->_getAdapterName($adapter);
        $this->_adapter = new $adapterClass($config);
    }

    private function _getAdapterName($adapter)
    {
        return 'CcQuery_Adapter_' . $adapter;
    }

    private function _isValidAdapter($adapter)
    {
        $adapterName = $this->_getAdapterName($adapter);
        if(class_exists( $adapterName ) ) {
            return true;
        } else {
            return false;
        }
    }

    public function sendQuery(CcQuery_Query_Request $request)
    {
        return $this->_adapter->sendQuery($request);
    }
}
