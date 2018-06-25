<?php
class SpecialPmaTree extends SpecialPage {
  var $dbr;
  var $pmas;
  var $output;
  function __construct() {
    parent::__construct( 'PmaTree' );
    $this->mIncludable = true;

  }

  function find_or_raise_exception($array, $field, $searched_value)
  {
     foreach($array as $key => $value)
     {
        if ( $value->$field === $searched_value )
           return $value;
     }
     throw new Exception('no value with this field');
  }

  function get_sub_array($array, $field, $searched_value)
  {
    $sub_array = [];
     foreach($array as $key => $value)
     {
        if ( $value->$field === $searched_value )
          $sub_array[]= $value;
     }
    return $sub_array;
  }

  function getGroupName() {
    return 'wiki';
  }

  function header($elem, $count)
  {
    return str_repeat('=',$count) . $this->render_with_type($elem) . str_repeat('=',$count) . "\n";
  }

  function pounds($elem, $count)
  {
    return str_repeat('#',$count) . $this->render_with_type($elem). "\n";
  }

  function render_with_type_russian($elem)
  {
    if ($elem->type === NULL){
      return '[[' . $this->remove_underscores($elem->ru_name) . ']]';
    }
    $type_readable = Pma::$type_maps[$elem->type];
    $output;
    switch ($type_readable) {
        case 'algorithm':
            $output = '[[File:A-chameleon-square-64x64.png|16px|link=Project:Уровни классификации|Уровень алгоритма]]';
            return $output.'[['. $this->remove_underscores($elem->ru_name).']]';
        case 'problem':
            $output = '[[File:З-orange-square-64x64.png|16px|link=Project:Уровни классификации|Уровень задачи]]';
            return $output.'[['. $this->remove_underscores($elem->ru_name).']]';
        case 'method':
            $output = '[[File:M-butter-square-64x64.png|16px|link=Project:Уровни классификации|Уровень метода]]';
            return $output.'[['. $this->remove_underscores($elem->ru_name).']]';
            break;
        case 'without_page':
            return $this->remove_underscores($elem->ru_name);
        case 'without_page_and_header':
            return $this->remove_underscores($elem->ru_name);

    }
  }


  function render_with_type($elem)
  {
    return $this->render_with_type_russian($elem);
  }

  function remove_underscores($arg)
  {
    return str_replace("_", " ", $arg);
  }


  function render_element($pma,$level,$last_without_page = 0,$parent = NULL){
    if($pma->type == '3'){
      $this->output.= $this->header($pma,$level);
      $last_without_page = $level;
    }
    elseif($pma->type == '4'){
      $this->output.= $this->pounds($pma,$level - $last_without_page);
    }

    else{
      $this->output.= $this->pounds($pma,$level - $last_without_page);
    }
    if($pma->childs_ids)
      foreach (explode(',',$pma->childs_ids) as $child_id ) {
        $this->render_element($this->find_or_raise_exception($this->pmas, 'id',$child_id),$level + 1,$last_without_page,$pma);
      }
  }

  function execute( $par ) {
    $this->output= '';
    $request = $this->getRequest();
    $this->setHeaders();

    $this->getOutput()->addHtml(Xml::submitButton( $this->msg( 'checkuser-check' )->text(),
    			[ 'id' => 'checkusersubmit', 'name' => 'checkusersubmit' ] ));

    $pmas_results = Pma::selectAllWithCategories();
    foreach($pmas_results as $pma)
      $this->pmas[]= $pma;
    $inits = $this->get_sub_array($this->pmas, 'parents_exists', NULL);
    foreach($inits as $pma){
      $this->render_element($pma,1);
    }
    $this-> getOutput()->addWikiText($this->output);
  }
}
