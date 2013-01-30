<?php 
	class TranslatableBehavior extends ModelBehavior {
                private $translatableFields;
                private $sessionLanguage;
		private $models;

		public function setup($model, $settingsArray){
			$modelName = get_class($model);//setup() gets called for each model

			$this->models[$modelName]['translatableFields'] = $model->actsAs['Translatable']['translatableFields'];
			$this->models[$modelName]['name'] = $modelName;

                	$this->translatableFields = $settingsArray['translatableFields'];
			$this->sessionLanguage = $_SESSION['Config']['language'];
		}
		

		public function afterFind($model, $results, $primary = false){
		        $results = $this->setDefaultStringLanguage($results, $primary);
			return $results;
		} 

		public function setDefaultStringLanguage($results, $primary = false){
			//prevent data from CakePHP's paginate() getting processed
			if(isset($results[0][0]['count']) && !(isset($results[0][0]['id']))) return $results;
			
			foreach($this->models as $model) {
				$name = $model['name'];
				$translatableFields = $model['translatableFields'];

				$results =(array)$results;

				foreach($results as $key => &$result){//using references to update $results directly
					foreach($translatableFields as $translatableField) {
						$result[$name][$translatableField] = $result[$name][$translatableField."_".$this->sessionLanguage];
				}
			} 

			return $results;
		}	
	}
?>
