<?php

/*
 * CachingActiveDataProvider extends the framework's CDataProvider class in three main ways.  The most important
 * is to offer the ability to cache the results using Yii's AR caching strategy, instead of wrapping the dataprovider
 * in a cache dependency.  The second is to set pagination to false by default.  Without this, you have to 
 * explicitly set it to false in order for a limit to be applied.  Lastly, we are adding functionality to set
 * a dataprovider's criteria in the class, giving the ability to extend this class into generic, model-specific
 * dataProviders.  This cleans up models quite a bit by separating dataProvider functionality from model/behavior functionality.
 */
class CachingActiveDataProvider extends CActiveDataProvider{
    
    public $isCaching = true;
    public $cacheTime = 300;
    
    public function __construct($modelClass,$config=array()){
    	//pagination to false by default, then we can limit without specifying
        if(!array_key_exists('pagination', $config)) $this->setPagination(false);
        parent::__construct($modelClass,$config);
		//offers a way to extend this class to generic dataproviders with their own criteria
        if(!array_key_exists('criteria', $config)) parent::setCriteria($this->baseCriteria());
    }
    
    protected function fetchData()
    {
        $criteria=clone $this->getCriteria();
    
        if(($pagination=$this->getPagination())!==false)
        {
            $pagination->setItemCount($this->getTotalItemCount());
            $pagination->applyLimit($criteria);
        }
    
        $baseCriteria=$this->model->getDbCriteria(false);
    
        if(($sort=$this->getSort())!==false)
        {
            // set model criteria so that CSort can use its table alias setting
            if($baseCriteria!==null)
            {
                $c=clone $baseCriteria;
                $c->mergeWith($criteria);
                $this->model->setDbCriteria($c);
            }
            else
                $this->model->setDbCriteria($criteria);
            $sort->applyOrder($criteria);
        }
    
        $this->model->setDbCriteria($baseCriteria!==null ? clone $baseCriteria : null);
		//if caching is set to true, then cache the results. else, proceed as normal
        $data = $this->isCaching ? $this->model->cache($this->cacheTime)->findAll($criteria) : $this->model->findAll($criteria);
        $this->model->setDbCriteria($baseCriteria);  // restore original criteria
        return $data;
    }

	/*
	 * override this method to set base/default criteria for a generic 
	 * 		    model-based dataProvider
	 * @return CDbCriteria
	 */
    protected function baseCriteria(){
        return new CDbCriteria;
    }
}
