<?php

class TingClientObject {
	public $id;
	public $data;
	public $relationsData;

  /**
   * We do not want specific items in the review box on bibliotek.dk.
   * Therefore we need to be able to remove them. Such as 150005-anmeld reviews.
   *
   * @param array $needle
   * $needle must be an assoc array. ['field_name' => 'field_value'].
   * Ex: array('relationUri' => '150005-anmeld')
   *
   * @return array
   * The trimmed relationsData array.
   */
	public function getTrimmedRelationsData($needle) {
	  if (!empty($this->relationsData) && is_array($this->relationsData) &&
        !empty($needle) && is_array($needle)) {

	    foreach($this->relationsData as $index => $relationsData) {
        foreach ($needle AS $relation_data_name => $item_to_remove) {
          if (isset($relationsData->$relation_data_name)) {
            if (strpos($relationsData->$relation_data_name, $item_to_remove) !== FALSE) {
              unset($this->relationsData[$index]);
            }
          }
        }
      }
    }
	  return $this->relationsData;
  }
}

