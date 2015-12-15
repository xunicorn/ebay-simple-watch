<?php 

/**
 * Class of arr tree
 * require array('id' => '', 'parent_id' => '', 'title' => '')
 *
 * For using CategoryTree class use initialize() method, than - getOneDimTree()
 *
 * @property array $res_arr
 * @property string $delimiter
 * @property array $input_arr
 * @property array $start_arr
 * @property array $res_recurse_arr
 */
class CategoryTree
{
    public $res_arr = array();
    public $delimiters;
    public $input_arr = array();
    public $start_arr = array();
    public $res_recurse_arr = array();

    public function __construct($arr, $delimiters = array('-'))
    {
        $this->input_arr  = $arr;

        if(!is_array($delimiters)) {
            $delimiters = array($delimiters);
        }

        $this->delimiters = $delimiters;

        if(count($arr) == 1) {
            $this->res_arr = $arr;
        } else {
            $this->init();
            $this->buildTree();
            $this->buildOneDimensionTree($this->res_recurse_arr);
        }
    }

    public static function initialize($arr, $delimiter = '-')
    {
      return new CategoryTree($arr, $delimiter);
    }
    
    private function init()
    {
      foreach($this->input_arr as $k => $arr)
      {
        if($arr['parent_id'] == 0)    
        {   
          $arr['level'] = 0;
          $this->res_recurse_arr[] = $arr;
          unset($this->input_arr[$k]);                    
        }
      }
    }

    public function buildTree()
    {
        foreach($this->res_recurse_arr as $k => $arr)
        {
            $tmp = $this->buildBranch($arr, 0);

            $this->res_recurse_arr[$k] = $tmp;
        }
    }

    public function buildBranch($branch, $level)
    {
        $level++;
        $tmp = array(array());
        $tmp[0] = $branch;

        foreach($this->input_arr as $k => $arr)
        {
            if($branch['id'] == $arr['parent_id'])
            {   
                $arr['level'] = $level;
                $tmp[1][] = $arr;

                unset($this->input_arr[$k]);
            }
        }

        if(!empty($tmp[1]))
        {
            foreach($tmp[1] as $k => $tmp_branch)
                if(count($this->input_arr) != 0)
                    $tmp[1][$k] = $this->buildBranch($tmp_branch, $level);
        }

        return count($tmp) > 1 ? $tmp : $branch;
    }

    public function buildOneDimensionTree($arr)
    {
        foreach($arr as $k => $v)
        {                
            if(!empty($v[0]))
                $this->buildOneDimensionTree($v);
            else
            {
                if(isset($this->delimiters[$v['level'] - 1])) {
                    $delimiter = $this->delimiters[$v['level'] - 1];
                } else {
                    $delimiter = '';

                    for ($i = count($this->delimiters) - 1; $i < $v['level']; $i++) {
                        $delimiter .= $this->delimiters[count($this->delimiters) - 1];
                    }
                }

                if($v['level'] > 0) {
                    $v['title'] = $delimiter . ' ' . $v['title'];
                }

                $this->res_arr[] = $v;
            }
        }
    }

    public function printTree()
    {
        echo '<pre>'; print_r($this->res_recurse_arr); echo '</pre>';
    }

    public function printTitleTree()
    {
        echo '<pre>'; print_r($this->res_arr); echo '</pre>';
    }

    public function getTree()
    {
        return $this->res_arr;
    }
    
    public function getOneDimTree()
    {
      $return_arr = array();
      
      foreach($this->res_arr as $arr)
      {
        $return_arr[$arr['id']] = $arr['title'];
      }
      
      return $return_arr;
    }

    public function getElement($elem_id) {
        $element = false;

        foreach($this->res_arr as $leaf) {
            if($leaf['id'] == $elem_id) {
                $element = $leaf;
            }
        }

        return $element;
    }

    public function getElementBranch($elem_id) {
        $element = $this->getElement($elem_id);
        $results = array(
            $element,
        );

        foreach($this->res_arr as $leaf) {
            if($leaf['parent_id'] == $elem_id) {
                $results = array_merge($results, $this->getElementBranch($leaf['id']));
            }
        }

        return $results;
    }

    public function getElementBranchBefore($elem_id, $level = 0) {
        $element = $this->getElement($elem_id);
        $results = array(
            $element,
        );

        if(count($this->res_arr) > 1) {
            foreach ($this->res_arr as $leaf) {
                if ($leaf['id'] == $element['parent_id']) {
                    $results = array_merge($results, $this->getElementBranchBefore($leaf['id'], ($level + 1)));
                }
            }
        }

        if($level == 0) {
            $results = array_reverse($results);
        }

        return $results;
    }

    public function getElementBranchFull($elem_id) {
        $before = $this->getElementBranchBefore($elem_id);
        $after  = $this->getElementBranch($elem_id);

        if(!empty($after)) {
            unset($after[0]);
        }

        return array_merge($before, $after);
    }

    public function getParentBranchFull($elem_id) {
        $elements = $this->getElementBranchBefore($elem_id);

        if(!empty($elements)) {
            $first_elem_id = $elements[0]['id'];

            $elements = $this->getElementBranch($first_elem_id);
        }

        return $elements;
    }

    public function getParentBranchFullPartial($elem_id) {
        $before = $this->getParentBranchBeforePartial($elem_id);
        $after  = $this->getElementBranch($elem_id);

        if(isset($after[0])) {
            unset($after[0]);
        }

        return array_merge($before, $after);
    }

    public function getParentBranchBefore($elem_id) {
        $elements = array();

        if(count($this->res_arr) > 1) {
            $data = $this->res_arr;

            $data = array_reverse($data);

            $element_found = false;

            foreach ($data as $leaf) {
                if ($leaf['id'] == $elem_id) {
                    $element_found = true;
                }

                if ($element_found) {
                    $elements[] = $leaf;

                    if ($leaf['parent_id'] == 0) {
                        $element_found = false;
                    }
                }
            }
        }

        return array_reverse($elements);
    }

    public function getParentBranchBeforePartial($elem_id) {
        $element  = $this->getElement($elem_id);

        $elements = array();

        $elements_before = $this->getElementBranchBefore($elem_id);
        $elements_full   = $this->getParentBranchFull($elem_id);

        $elements_before_ids = array();

        foreach($elements_before as $leaf) {
            $elements_before_ids[] = $leaf['id'];
        }

        for($i=0; $i <= $element['level']; $i++) {
            foreach($elements_full as $indx => $leaf) {
                if($leaf['level'] == $i) {
                    if(in_array($leaf['parent_id'], $elements_before_ids) || $i == 0) {
                        $elements[] = $leaf;
                    }
                }
            }
        }

        return $elements;
    }
}
