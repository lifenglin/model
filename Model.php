<?php
namespace Tofu;
class Model
{
    private $_objMogoClient;

    private $_objCollection;

    private $_bolDataHasBeenLoaded = false;

    private $_arrData = array();

    const DELETE = 'D';

    const NOT_DELETE = 'N';

    public function __construct($strCollection, $strServer = null, $arrOptions = null)
    {
        //http://cn2.php.net/manual/zh/mongoclient.construct.php
        $this->_objMogoClient = new MongoClient($strServer, $arrOptions);
        $this->_objCollection = $this->_objMogoClient->$strCollection;
    }

    public function __toString()
    {
        $this->_checkDataHasBeenLoaded(true);
        return json_encode($this->_arrData);
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
            user_error('parameter is empty');
        }
        $this->_bolDataHasBeenLoaded = true;
    }

    private function _checkDataHasBeenLoaded($bolNeedLoaded = true)
    {
        if ($this->_bolDataHasBeenLoaded !== $bolNeedLoaded) {
            throw new \RuntimeException('check data has been loaded fail');
        }
    }

    public function findById($mixMongodbId)
    {
        if (empty($mixMongodbId)) {
            throw new \InvalidArgumentException("findById() expects parameter 1 not to be empty");
        }
        if (!is_string($mixMongodbId) && !is_a($mixMongodbId, 'MongoId')) {
            throw new \InvalidArgumentException("findById() expects parameter 1 to be string or MongId");
        }
        try {
            $objMongoId = new MongoId($mixMongodbId);
        } catch (MongoException $e) {
            throw new \InvalidArgumentException("findById() expects parameter 1 to be string or MongId");
        }
        $arrData = $this->_objCollection->findOne(array('_id' => $objMongoId));
        $objModel = new self();
        $objModel->_setData($arrData);
        return $objModel;
    }

    static public function find($arrQuery = null, $arrFields = null)
    {
        $arrData = $this->_objCollection->find($arrQuery, $arrFields);
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
        $arrData = $this->_objCollection->findOne($arrQuery, $arrFields);
        $objModel = new self();
        $objModel->_setData($arrData);
        return $objModel;
    }


    /**
     * save 
     * 保存数据模型对象
     * @param array $arrData 
     * @access public
     * @return void
     */
    public function save($arrData = array())
    {
        //如果输入的数据为空，表示对象已载入完成
        if (empty($arrData)) {
            $this->_checkDataHasBeenLoaded(true);
        } else {
            //如果输入的数据不为空，表示对象没有载入
            $this->_checkDataHasBeenLoaded(false);
            $this->_setData($arrData);
        }
        //保存到数据库
        $this->_objCollection->save($this->_arrData);
    }

    /**
     * delete 
     * 从数据库中删除该数据模型
     * @access public
     * @return void
     */
    public function delete()
    {
        //确认已经载入
        $this->_checkDataHasBeenLoaded(true);
        //将状态设置成删除状态
        $this->_arrData['status'] = self::DELETE;
        //保存到数据库
        $this->save();
    }

    /**
     * __get 
     * 从载入的数据模型中获取成员
     * @param mixed $strArgumentName 
     * @access public
     * @return void
     */
    public function __get($strArgumentName)
    {
        //确认已经载入
        $this->_checkDataHasBeenLoaded(true);
        return $this->_arrData[$strArgumentName];
    }

    /**
     * __set 
     * 设置数据模型中的成员
     * @param mixed $strArgumentName 
     * @param mixed $strArgument 
     * @access public
     * @return void
     */
    public function __set($strArgumentName, $strArgument)
    {
        //check
        $this->_arrData[$strArgumentName] = $strArgument;
        $this->_bolDataHasBeenLoaded = true;
        return true;
    }

    public function toArray()
    {
        //确认已经载入
        $this->_checkDataHasBeenLoaded(true);
        return $this->_arrData;
    }
}
