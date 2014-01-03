CachingActiveDataProvider
=========================

Yii DataProvider that caches the results, and sets pagination to false by default

To force no-cache
---------------------
        $dataProvider = new CachingActiveDataProvider('Post',array(
            'criteria'  => $criteria,
            'isCaching' => false, 
        ));


Extend baseCriteria
---------------------
This can be good for things like applying default scopes to model-specific criteria without having to set a defaultScope
on the model itself.

        class PostDataProvider extends CachingActiveDataProvider{
            public $category = false;
            public $limit    = false;
                
            public function __construct($config=array()){
                parent::__construct('Post',$config);
            }
               
            protected function baseCriteria(){
                $criteria = parent::baseCriteria();
                $criteria->alias  = 'post';
                $criteria->scopes = array('recent','published');
                if($this->category) $criteria->compare('post.category_id',$this->category->id);
                if($this->limit)    $criteria->limit = $this->limit;
                return $criteria;
            }
        }
        
        $posts = new PostDataProvider(array('category' => $category, 'limit' => 20));
        
        //In View
        $this->widget('CListView',array(
            'dataProvider' => $posts,
            'itemView'     => '/post/view',
        ));
