<?php 
/*
	This behavior will pick between multilingual information stored in a database and
	store it in a variable that can be used in a view. This is much less complicated than
	CakePHPs current default implementation of i18n functionality that requires storing
	extra information in separate tables.

	USAGE 

	Each column should have the same name followed by a suffix to describe the language, 
	for example:
		blogpost_eng
		blogpost_jpn
		blogpost_rus
		title_eng
		title_jpn
		title_rus
	
	The current language should also be stored inside of the $_SESSION['Config']['language']
	global variable.

	Attach the behaviour to your model using:

	public $actsAs = array (
		'Translatable' => array(
			'translatableFields' => array('blogpost', 'title')
		)
	); 

	When you perform a find on the database while the session is English you will get 
	something like this:

		blogpost_eng => 'English Text'
		blogpost_jpn => 'Japanese Text'
		blogpost_rus => 'Russian Text'
		title_eng    => 'English Title'
		title_jpn    => 'Japanese Title'
		title_rus    => 'Russian Title'
	
	After the behavior has run you will get this added to your results

		title    => 'English Title'
		blogpost => 'English Text'
	
	You can use $title and $blogpost in your view to enable simple multi-lingual funcitonality.

	KNOWN LIMITATIONS

	This behavior only supports recursive 1 finds. Anything lower than that will be ignored.
	This is good enough for most cases and my needs. Adding recursive translating would have
	reduced performance when it is not needed.

*/
	class TranslatableBehavior extends ModelBehavior {
                private $translatableFields;
                private $sessionLanguage;
		private $models;

		public function setup($model, $settingsArray){
			$modelName = get_class($model);//setup gets called for each model

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
			//prevent data from CakePHPs paginate() getting processed
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
