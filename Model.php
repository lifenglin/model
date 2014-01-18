<?php
class Model
{
    private $_objMogoClient;

    private $_objCollection;

    private $_bolDataHasBeenLoaded = false;

    private $_arrData = array();

    const DELETE = 'D';

    const NOT_DELETE = 'N';

    //http://cn2.php.net/manual/zh/mongoclient.construct.php
    public function init($strServer = null, $arrOptions = null)
    {
        /*
            $arrOptions = 
            array_merge(array('connect' => false), (array) $arrOptions);
         */
        $this->_objMogoClient = new MongoClient($strServer, $arrOptions);
    }

    /*
    public function __call($strMethodName, $arrArguments)
    {
        if (!method_exists($this->_objCollection, $strMethodName)) {
            //throw
        }
        if ($this->_bolDataHasBeenLoaded) {
            //throw
        }
        return call_user_func_array(array($this->_objCollection, $strMethodName), $arrArguments);
    }
    */

    private function _setData($arrData)
    {
        $this->_checkDataHasBeenLoaded(false);
        if (!empty($arrData)) {
            $this->_arrData = $arrData;
        } else {
            //notice
        }
        $this->_bolDataHasBeenLoaded = true;
    }

    private function _checkDataHasBeenLoaded($bolNeedLoaded = true)
    {
        if ($this->_bolDataHasBeenLoaded !== $bolNeedLoaded) {
            //throw
        }
    }

    static public function findById($mixMongodbId)
    {
        if (empty($mixMongodbId)) {
            //warning
            return array();
        }
        if (!is_string($mixMongodbId) && !is_a($mixMongodbId, 'MongoId')) {
            //waring
            return array();
        }
        $arrData = self::$_objCollection->findOne(array('_id' => new MongoId($mixMongodbId)));
        $objModel = new self();
        $objModel->_setData($arrData);
        return $objModel;
    }

    static public function find($arrQuery = null, $arrFields = null)
    {
        $arrData = self::$_objCollection->find($arrQuery, $arrFields);
        $arrModel = array();
        foreach ($arrData as $arrItem) {
            $objModel = new self();
            $objModel->_setData($arrData);
            $arrModel[] = $objModel;
        }
        return $arrModel;
    }

    static public function findFirst($arrQuery = null, $arrFields = null)
    {
        $arrData = self::$_objCollection->findOne($arrQuery, $arrFields);
        $objModel = new self();
        $objModel->_setData($arrData);
        return $objModel;
    }

    public function save($arrData = array())
    {
        if (empty($arrData)) {
            $this->_checkDataHasBeenLoaded(true);
        } else {
            $this->_checkDataHasBeenLoaded(false);
            $this->_setData($arrData);
        }
        self::$_objCollection->save($this->_arrData);
    }

    public function delete()
    {
        $this->_checkDataHasBeenLoaded(true);
        $this->_arrData['status'] = self::DELETE;
        $this->save();
    }

    public function __get($strArgumentName)
    {
        //check
        return $this->_arrData[$strArgumentName];
    }

    public function __set($strArgumentName, $strArgument)
    {
        //check
        $this->_arrData[$strArgumentName] = $strArgument;
        $this->_bolDataHasBeenLoaded = true;
        return true;
    }
}
