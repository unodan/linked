class List_Node {
	function __construct($data) {
		$this->id = key($data);
		$this->index = NULL;
		$this->data = $data[$this->id];
		$this->ln_Succ = NULL; 
		$this->ln_Pred = NULL;
	}
}

class Linked_List {
	private $length = 0;
	
	function __construct() {$this->new_list();}

	function size() {
		$count = 0;
		$node = $this->first();
		while ($node) {
			$count++;
			$node = $node->ln_Pred;
		}
		return $count;
	}
	
	function index_of(&$list_node) {
		$count = 0;
		$index = FALSE;
		$node = $this->first();
		while ($node) {
			$count++;
			if ($list_node->id === $node->id) {$index = ($count - 1); break;}
			$node = $node->ln_Pred;
		}
		return $index;
	}
	
	function first() {return $this->lh_Head;}
	
	function last() {return $this->lh_Tail;}
	
	function is_first(&$node) {return ($node === $this->first()) ? TRUE:FALSE;}
	
	function is_last(&$node) {return ($node === $this->last()) ? TRUE:FALSE;}
	
	function is_empty() {return ($this->first() === $this->lh_TailPred) ? TRUE:FALSE;}
	
	function has_node(&$node) {
		$list_node = $this->first();
		
		while ($list_node) {
			if ($list_node === $node) return TRUE ;
			$list_node = $list_node->ln_Pred;
		}
		return FALSE;
	}
	
	function get_node($id) {
		$node = $this->first();
		while ($node) {
			if ($node->id === $id) return $node;
			$node = $node->ln_Pred;
		}
		return FALSE;
	}
	
	function new_list() {
		$this->lh_Head = $this->lh_TailPred;
		$this->lh_Tail = $this->lh_Head;
		$this->lh_TailPred = NULL;
	}
	
	function add_head(&$node) {
		if ($this->has_node($node)) return FALSE;
		
		if ($this->is_empty()) { 
			$node->ln_Succ = NULL;
			$node->ln_Pred = NULL;
			$this->lh_Head = $node;
			$this->lh_Tail = $node;
		} else {
			$node->ln_Succ = NULL;
			$node->ln_Pred = $this->first();
			$node->ln_Pred->ln_Succ = $node;
			$this->lh_Head = $node;
		}
	}
	
	function add_tail(&$node) {
		if ($this->has_node($node)) return FALSE;
		
		if ($this->is_empty()) { 
			$node->ln_Succ = NULL;
			$node->ln_Pred = NULL;
			$this->lh_Head = $node;
			$this->lh_Tail = $node;
		}  else {
			$node->ln_Succ = $this->last();
			$this->lh_Tail = $node;
			$node->ln_Succ->ln_Pred = $node;
			$node->ln_Pred = NULL;
		}
	}
	
	function before(&$node1, &$node2) {
		if (!$this->has_node($node2) || $node1 === $node2) return FALSE;
		
		if ($this->has_node($node1)) $this->remove($node1);
		
		if ($this->is_first($node2)) {
			$this->add_head($node1);
		} else {
			$node1->ln_Succ = $node2->ln_Succ;
			$node1->ln_Succ->ln_Pred = $node1; 
			$node1->ln_Pred = $node2;
			$node2->ln_Succ = $node1;
		}
		$this->length++;
		return TRUE;
	}
	
	function after(&$node1, &$node2) {
		if (!$this->has_node($node2) || $node1 === $node2) return FALSE;
		
		if ($this->has_node($node1)) $this->remove($node1);
		
		if ($this->is_last($node2)) {
			$this->add_tail($node1);
		} else {
			$node1->ln_Pred = $node2->ln_Pred; 
			$node1->ln_Pred->ln_Succ = $node1; 
			$node1->ln_Succ = $node2;
			$node2->ln_Pred = $node1;
		}
		return TRUE;
	}
	
	function swap(&$node1, &$node2) {
	
		if (!$this->has_node($node1) || !$this->has_node($node2) || $node1 === $node2) return FALSE;
		
		$node1_index = $this->index_of($node1);
		$node2_index = $this->index_of($node2);
			
		if ($this->is_first($node1) || $this->is_first($node2)) {
			if ($this->is_first($node1)) {
				$ln_Succ = $node2->ln_Succ;
				$this->before($node2, $node1);
				$this->after($node1, $ln_Succ);
			} else {
				$ln_Succ = $node1->ln_Succ;
				$this->before($node1, $node2);
				$this->after($node2, $ln_Succ);
			}
			
		} else if ($this->is_last($node1) || $this->is_last($node2)) {
			if ($this->is_last($node2)) {
				$ln_Succ = $node1->ln_Succ;
				$this->after($node1, $node2);
				$this->after($node2, $ln_Succ);
			} else {
				$ln_Succ = $node2->ln_Succ;
				$this->after($node2, $node1);
				$this->after($node1, $ln_Succ);
			}
			
		} else {
			if ($node1_index == $node2_index - 1) { 
				$this->after($node1, $node2);
			} else if ($node2_index == $node1_index - 1) { 
				$this->after($node2, $node1);
			} else {
				if ($node1_index < $node2_index) {
					$tmp_node = $node2->ln_Succ;
					$this->after($node2, $node1);
					$this->after($node1, $tmp_node);
				} else {
					$tmp_node = $node1->ln_Succ;
					$this->after($node1, $node2);
					$this->after($node2, $tmp_node);
				}
			} 
		}
	}
	
	function remove(&$node) {
		if (!$this->has_node($node)) return FALSE;
		
		if ($this->is_first($node)) {
			if ($this->is_last($node)) $this->lh_Tail = NULL;
			
			$this->lh_Head = $this->lh_Head->ln_Pred;
			$this->lh_Head->ln_Succ = NULL;
			
		} else if ($this->is_last($node)) {
			$this->lh_Tail = $this->lh_Tail->ln_Succ;
			$this->lh_Tail->ln_Pred = NULL;
		} else {
			$node->ln_Succ->ln_Pred = $node->ln_Pred;
			$node->ln_Pred->ln_Succ = $node->ln_Succ;
		}
		
		return TRUE;
	}
	
	function replace(&$node1, &$node2) {
		if (!$this->has_node($node2) || $node1 === $node2) return FALSE;
		
		$this->before($node1, $node2);
		$this->remove($node2);
	}
	
	function walk($echo=TRUE) {
		$node = $this->first();
		
		$cnt = 0;
		$html = '
				List Size '.$this->size().'<br/>
				List Head Node ID: '.(($this->lh_Head->id !== NULL) ? $this->lh_Head->id:'NULL').'<br/>
				List Tail Node ID: '.(($this->lh_Tail->id !== NULL) ? $this->lh_Tail->id:'NULL').'<br/>
				List TailPred: '.(($this->lh_TailPred) ? $this->lh_TailPred->id:'NULL') . '<br/><br/><br/>';
		while ($node) {
			
			$html .= '
				Node#: '.($cnt++).'<br/>
				Node ID: '.$node->id.'<br>
				Node Succ ID: '.(($node->ln_Succ->id !== NULL) ? $node->ln_Succ->id:'NULL').'<br/>
				Node Pred ID: '.(($node->ln_Pred->id !== NULL) ? $node->ln_Pred->id:'NULL').'<br/>
				Node Data: '.$this->dump($node->data, FALSE);
				
			$node = $node->ln_Pred;
		
		}
		if ($echo) echo $html; else return $html; 
	}
	
	function dump($var, $echo=TRUE) {
		ob_start();
		echo ('<pre>');
		print_r($var);
		echo('</pre>');
		$html = ob_get_contents();
		ob_end_clean();
		
		if ($echo) echo $html; else return $html; 
	}
}


/** TEST DATA BELOW *************************************************************************************** **/
$node1 = new List_Node(array('s1' => array('var1' => 'HELLO-1', 'var2' => 'WORLD-1')));
$node2 = new List_Node(array('s2' => array('var1' => 'HELLO-2', 'var2' => 'WORLD-2')));
$node3 = new List_Node(array('s3' => array('var1' => 'HELLO-3', 'var2' => 'WORLD-3')));
$node4 = new List_Node(array('s4' => array('var1' => 'HELLO-4', 'var2' => 'WORLD-4')));
$node5 = new List_Node(array('s5' => array('var1' => 'HELLO-5', 'var2' => 'WORLD-5')));

$list = new Linked_List();
$list->add_head($node2);
$list->add_head($node1);
$list->add_tail($node4);
$list->after($node3, $node2);
$list->after($node5, $node4);
$list->walk();


echo '<br>* swap (1,5) (2,3) ***********************************************************<br><br>';
$list->swap($node1, $node5);
$list->swap($node2, $node3);
$list->walk();

echo '<br>* swap (5,1) (3,2) ***********************************************************<br><br>';
$list->swap($node5, $node1);
$list->swap($node3, $node2);
$list->walk();

echo '<br>* swap (1,2) (4,5) ************************************************************<br><br>';
$list->swap($node1, $node2);
$list->swap($node4, $node5);
$list->walk();

echo '<br>* swap (2,1) (5,4) ************************************************************<br><br>';
$list->swap($node2, $node1);
$list->swap($node5, $node4);
$list->walk();

echo '<br>* swap (2,4) ************************************************************<br><br>';
$list->swap($node2, $node4);
$list->walk();

echo '<br>* swap (4,2) ************************************************************<br><br>';
$list->swap($node2, $node4);
$list->walk();

echo '<br>* replace (1,2) (4,5) ************************************************************<br><br>';
$list->replace($node1, $node2);
$list->replace($node4, $node5);
$list->walk();

echo '<br>* remove (3) (1) (4) ************************************************************<br><br>';
$list->remove($node3);
$list->remove($node1);
$list->remove($node4);
$list->walk();

echo '<br>* add_tail (1) (2) (3) (4) (5) ***************************************************<br><br>';
$list->add_tail($node1);
$list->add_tail($node2);
$list->add_tail($node3);
$list->add_tail($node4);
$list->add_tail($node5);
$list->walk();

echo '<br>* before (1,5) (3,2)  ********************************************************<br><br>';
$list->before($node1 ,$node5);
$list->before($node3 ,$node2);
$list->walk();

echo '<br>* before (5,1) (2,3) ********************************************************<br><br>';
$list->before($node5 ,$node1);
$list->before($node2 ,$node3);
$list->walk();

echo '<br>* before (1,2) ********************************************************<br><br>';
$list->before($node1 ,$node2);
$list->walk();

echo '<br>* after (1,5) (2,3)  ********************************************************<br><br>';
$list->after($node1 ,$node5);
$list->after($node2 ,$node3);
$list->walk();

echo '<br>* after (5,1) (2,3)  ********************************************************<br><br>';
$list->after($node5 ,$node1);
$list->after($node3 ,$node2);
$list->walk();

echo '<br>* before (1,2)  ********************************************************<br><br>';
$list->before($node1 ,$node2);
$list->walk();

echo '<br>* remove (1) (2) (3) (4) (5) ***************************************************<br><br>';
$list->remove($node1);
$list->remove($node2);
$list->remove($node3);
$list->remove($node4);
$list->remove($node5);
$list->walk();

echo '<br>* add_head (1) (2) (3) (4) (5) ***************************************************<br><br>';
$list->add_head($node1);
$list->add_head($node2);
$list->add_head($node3);
$list->add_head($node4);
$list->add_head($node5);
$list->walk();
