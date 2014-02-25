<?php

/**
* Linked list node.
* Author: Dan Huckson
* Author Email: DanHuckson@gmail.com
* 
* Version: 2.5
* Date: 2013/12/29
*
* Class: List_Node
*
* Methods: id(), set_id(), is_first(), is_last(), is_zombie(), insert_before(), 
*          insert_after(), replace(), remove()
*
*
*****************************************************************************************/
class List_Node {
	private $id;
	protected $parent, $ln_Succ, $ln_Pred;
	
	function __construct(&$data=NULL) {
		$uid = (isset($data['id'])) ? $data['id']:'';
		
		$this->set_id($uid);
		$this->data = $data;
		$this->parent = NULL; 
		$this->ln_Succ = NULL; 
		$this->ln_Pred = NULL;
	}
	
	private function set_id($uid) {
		static $cnt = 0;
		$this->id = (empty($uid)) ? ++$cnt:$uid;
	}
	
	function id() {return $this->id;}
	
	function is_first() {return ($this === $this->parent->lh_Head) ? TRUE:FALSE;}
	
	function is_last() {return ($this === $this->parent->lh_Tail) ? TRUE:FALSE;}
	
	function is_zombie () {return (!$this->parent || !$this->parent->has_node($this)) ? TRUE:FALSE;}
	
	function insert_after(&$node) {
		if (!$this->parent) throw new Exception('this node ID: '.$node->id().' is not a member of a list.');
		else return $this->parent->after($node, $this);
	}
	
	function insert_before(&$node) {
		if (!$this->parent) throw new Exception('node ID: '.$node->id().' is not a member of a list.');
		else return $this->parent->before($node, $this);
	}
	
	function replace(&$node) {$this->parent->replace($node, $this);}
	
	function remove() {if (!$this->is_zombie()) $this->parent->remove($this);}
}

/**
* Double linked list.
* Author: Dan Huckson
* Author Email: DanHuckson@gmail.com
* 
* Version: 2.5
* Date: 2013/12/29
*
* Class: Linked List
*
* Methods: init(), add_head(), add_tail(), before(), after(), remove(), index_of()
*          size(), first(), last(), is_first(), is_last(), is_empty(), has_node(), get_node(), swap()
*          replace(), walk(), dump().
*
*****************************************************************************************/
class Linked_List extends List_Node {
	
	function __construct(&$list=NULL) {
		$this->lh_TailPred = NULL;
		$this->lh_Head = $this->lh_TailPred;
		$this->lh_Tail = $this->lh_TailPred;
		if (!empty($list)) $this->init($list);
	}
	
	function init(&$list_nodes) {
		if (!empty($list_nodes)) foreach ($list_nodes as $node) 
		$this->add_tail(new List_Node($node));
	}
	
	function first() {return $this->lh_Head;}
	
	function last() {return $this->lh_Tail;}
	
	function is_empty() {return ($this->lh_Head === $this->lh_TailPred) ? TRUE:FALSE;}
	
	function size() {
		$count = 0;
		$node = $this->lh_Head;
		while ($node) {$node = $node->ln_Pred; $count++;}
		return $count;
	}
	
	function get_node($id, $list='') {
		$match = FALSE;
		if (empty($list)) $node = $this->lh_Head;
		else $node = $list->lh_Head;
		
		while ($node && !$match) {
			if (isset($node->data['content']) && $node->data['content'] instanceof Linked_List) 
				$match = $this->get_node($id, $node->data['content']);
			
			if ($node->id() === $id) $match = $node;
		
			$node = $node->ln_Pred;
		}
		return $match;
	}
	
	function index_of(&$node) {
		if ($node->is_zombie()) throw new Exception('node ID: '.$node->id().' is not a member of a list.');
		
		$index = 0;
		$list_node = $this->first();
		while ($list_node) {
			if ($list_node->id() === $node->id()) break;
			$list_node = $list_node->ln_Pred;
			$index++;
		}
		return $index;
	}
	
	function has_node(&$node) {
		$list_node = $this->lh_Head;
		while ($list_node) {
			if ($list_node === $node) return TRUE;
			$list_node = $list_node->ln_Pred;
		}
		return FALSE;
	}
	
	function add_head(&$node) {
		if ($this->has_node($node)) throw new Exception('node ID: '.$node->id().' attempted to add itself again to the head of the list.');
		
		if (!$node->is_zombie()) $node->remove();
		
		if ($this->is_empty()) { 
			$node->ln_Succ = NULL;
			$node->ln_Pred = NULL;
			$this->lh_Head = $node;
			$this->lh_Tail = $node;
		} else {
			$node->ln_Succ = NULL;
			$node->ln_Pred = $this->lh_Head;
			$node->ln_Pred->ln_Succ = $node;
			$this->lh_Head = $node;
		}
		$node->parent = $this;
		
		return $this;
	}
	
	function add_tail(&$node) {
		if ($this->has_node($node)) throw new Exception('node ID: '.$node->id().' attempted to add itself again to the tail of the list.');
		
		if (!$node->is_zombie()) $node->remove();
		
		if ($this->is_empty()) { 
			$node->ln_Succ = NULL;
			$node->ln_Pred = NULL;
			$this->lh_Head = $node;
			$this->lh_Tail = $node;
		}  else {
			$node->ln_Succ = $this->lh_Tail;
			$this->lh_Tail = $node;
			$node->ln_Succ->ln_Pred = $node;
			$node->ln_Pred = NULL;
		}
		$node->parent = $this;
		
		return $this;
	}
	
	function before(&$node1, &$node2) {
		if ($node2->is_zombie()) throw new Exception('node ID: '.$node2->id().' is not a member of a list.');
		else if ($node1 === $node2) throw new Exception('node ID: '.$node2->id().' attempted to insert itself before it itself.');
		
		if ($node1->parent && $node1->parent->has_node($node1)) $node1->parent->remove($node1);
		
		if ($node2->is_first()) {
			$node2->parent->add_head($node1);
		} else {
			$node1->ln_Succ = $node2->ln_Succ;
			$node1->ln_Succ->ln_Pred = $node1; 
			$node1->ln_Pred = $node2;
			$node2->ln_Succ = $node1;
			$node1->parent = $node2->parent;
		}
		return TRUE;
	}
	
	function after(&$node1, &$node2) {
		if ($node2->is_zombie()) throw new Exception('node ID: '.$node2->id().' is not a member of a list.');
		else if ($node1 === $node2) throw new Exception('node ID: '.$node2->id().' attempted to insert itself after it itself.');
		
		if ($node2->is_zombie() || $node1 === $node2) return FALSE;
		
		if ($node1->parent && $node1->parent->has_node($node1)) $node1->parent->remove($node1);
		
		if ($node2->is_last()) {
			$node2->parent->add_tail($node1);
		} else {
			$node1->ln_Pred = $node2->ln_Pred; 
			$node1->ln_Pred->ln_Succ = $node1; 
			$node1->ln_Succ = $node2;
			$node2->ln_Pred = $node1;
			$node1->parent = $node2->parent;
		}
		return TRUE;
	}
	
	function swap(&$node1, &$node2) {
		if ($node1->is_zombie()) throw new Exception('node ID: '.$node1->id().' is not a member of a list.');
		else if ($node2->is_zombie()) throw new Exception('node ID: '.$node2->id().' is not a member of a list.');
		else if ($node1 === $node2) throw new Exception('node ID: '.$node1->id().' attempted to swap itself.');
		
		$parent2 = $node2->parent;
		$ln_Succ = $node2->ln_Succ;
		
		$node1->parent->after($node2, $node1);
		$node1->parent->remove($node1);
		
		if ($ln_Succ) $parent2->after($node1, $ln_Succ);
		else $parent2->add_head($node1);
	}
	
	function replace(&$node1, &$node2) {
		if ($node2->is_zombie()) throw new Exception('node ID: '.$node2->id().' is not a member of a list.');
		else if ($node1 === $node2) throw new Exception('node ID: '.$node2->id().' attempted to replace itself.');
		
		$node2->parent->after($node1, $node2);
		$node2->remove();
		
		return $node1;
	}
	
	function remove(&$node) {
		if ($node->is_zombie()) throw new Exception('node ID: '.$node->id().' is not a member of a list.');
		
		$parent = $node->parent;
		if ($node->is_first()) {
			if ($node->is_last()) {
				$parent->lh_Tail = NULL;
				$parent->lh_Head = $parent->lh_TailPred;
			} else {
				$parent->lh_Head = $parent->lh_Head->ln_Pred;
				$parent->lh_Head->ln_Succ = NULL;
			}
		} else if ($node->is_last()) {
			$parent->lh_Tail = $parent->lh_Tail->ln_Succ;
			$parent->lh_Tail->ln_Pred = NULL;
		} else {
			$node->ln_Succ->ln_Pred = $node->ln_Pred;
			$node->ln_Pred->ln_Succ = $node->ln_Succ;
		}
		$node->parent = NULL;
		
		return TRUE;
	}
	
	function walk($echo=TRUE) {
		$cnt = 0;
		$node = $this->lh_Head;
		
		$html = '
				List Size '.self::size().'<br/>
				List Head Node ID: '.(($this->lh_Head->id() !== NULL) ? $this->lh_Head->id():'NULL').'<br/>
				List Tail Node ID: '.(($this->lh_Tail->id() !== NULL) ? $this->lh_Tail->id():'NULL').'<br/>
				List TailPred: '.(($this->lh_TailPred) ? $this->lh_TailPred->id():'NULL') . '<br/><br/><br/>';
				
		while ($node) { 
			$succ_id = (isset($node->ln_Succ)) ? $node->ln_Succ->id():'NULL';
			$pred_id = (isset($node->ln_Pred)) ? $node->ln_Pred->id():'NULL';
			$html .= '
			<br/>
			Node ID: '.$node->id().'<br>
			Node Succ ID: '.$succ_id.'<br/>
			Node Pred ID: '.$pred_id.'<br/>
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

/**
* Text code.
* Author: Dan Huckson
* Auther Email: DanHuckson@gmail.com
* 
* Version: 2.4
* Date: 2013/12/26
*
*****************************************************************************************/
echo '<br>* List 1 ***********************************************************<br>';
$data1 = array(
	array('id' => 'a1', 'var1' => 'HELLO-b1', 'var2' => 'WORLD-b1'),
	array('id' => 'a2', 'var1' => 'HELLO-b2', 'var2' => 'WORLD-b2'),
	array('id' => 'a3', 'var1' => 'HELLO-b3', 'var2' => 'WORLD-b3'),
	array('id' => 'a4', 'var1' => 'HELLO-b4', 'var2' => 'WORLD-b4'),
	array('id' => 'a5', 'var1' => 'HELLO-b5', 'var2' => 'WORLD-b5')
);
$lista = new Linked_list($data1);
$lista->walk();

echo '<br>* List 2 ***********************************************************<br>';
$data2 = array(
	array('id' => 'b1', 'var1' => 'HELLO-b1', 'var2' => 'WORLD-b1'),
	array('id' => 'b2', 'var1' => 'HELLO-b2', 'var2' => 'WORLD-b2'),
	array('id' => 'b3', 'var1' => 'HELLO-b3', 'var2' => 'WORLD-b3'),
	array('id' => 'b4', 'var1' => 'HELLO-b4', 'var2' => 'WORLD-b4'),
	array('id' => 'b5', 'var1' => 'HELLO-b5', 'var2' => 'WORLD-b5')
);
$listb = new Linked_list();
$listb->init($data2);
$listb->walk();

echo '<br>* List 3 ***********************************************************<br>';
$data2 = array(
	array('var1' => 'HELLO-c1', 'var2' => 'WORLD-c1'),
	array('var1' => 'HELLO-c2', 'var2' => 'WORLD-c2'),
	array('var1' => 'HELLO-c3', 'var2' => 'WORLD-c3'),
	array('var1' => 'HELLO-c4', 'var2' => 'WORLD-c4'),
	array('var1' => 'HELLO-c5', 'var2' => 'WORLD-c5')
);
$listc = new Linked_list($data2);
$listc->walk();

echo '<br>* List 4 ***********************************************************<br>';
$data1 = array('id' => 'd1', 'var1' => 'HELLO-d1', 'var2' => 'WORLD-d1');
$data2 = array('id' => 'd2', 'var1' => 'HELLO-d2', 'var2' => 'WORLD-d2');
$data3 = array('id' => 'd3', 'var1' => 'HELLO-d3', 'var2' => 'WORLD-d3');
$data4 = array('id' => 'd4', 'var1' => 'HELLO-d4', 'var2' => 'WORLD-d4');
$data5 = array('id' => 'd5', 'var1' => 'HELLO-d5', 'var2' => 'WORLD-d5');

$node1 = new List_Node($data1);
$node2 = new List_Node($data2);
$node3 = new List_Node($data3);
$node4 = new List_Node($data4);
$node5 = new List_Node($data5);

$listd = new Linked_list();

$listd->add_tail($node2);
echo '<br/>NOTICE *** ERRORS BELOW WHERE MADE ON PURPOSE TO TEST EXCEPTIONS. ***<br/><br/>';
try {
	$listd->remove($node1);
} catch (Exception $e) {
	echo '<br/>ERROR: '.$e->getMessage().'</br>';
	echo "A node must first be added to a list before it can be removed.<br/><br/>";
}

try {
	$listd->replace($node2, $node2);
} catch (Exception $e) {
	echo '<br/>ERROR: '.$e->getMessage().'</br>';
	echo "Replacing a node with itself has no effect.<br/><br/>";
}

try {
	$listd->index_of($node5);
} catch (Exception $e) {
	echo '<br/>ERROR: '.$e->getMessage().'</br>';
	echo "A node must first be added to a list in order to have a index.<br/><br/>";
}

try {
	$listd->swap($node2, $node2);
} catch (Exception $e) {
	echo '<br/>ERROR: '.$e->getMessage().'</br>';
	echo "A node can not be swaped with itself, nodes must be different nodes.<br/><br/>";
}

$listd->add_head($node5);

try {
	$listd->add_head($node5);
} catch (Exception $e) {
	echo '<br/>ERROR: '.$e->getMessage().'</br>';
	echo "A node can not be added to the head of a list if already in the list.<br/>First remove the node then add it to the head of the list.<br/><br/>";
}

$listd->after($node4, $node2);
$listd->before($node3, $node4);

try {
	$listd->before($node5, $node5);
} catch (Exception $e) {
	echo '<br/>ERROR: '.$e->getMessage().'</br>';
	echo "A node can not be inserted before itself, nodes must be different nodes.<br/><br/>";
}

try {
	$listd->after($node5, $node5);
} catch (Exception $e) {
	echo '<br/>ERROR: '.$e->getMessage().'</br>';
	echo "A node can not be inserted after itself, nodes must be different nodes.<br/><br/>";
}

$listd->add_tail($node1);

try {
	$listd->add_tail($node1);
} catch (Exception $e) {
	echo '<br/>ERROR: '.$e->getMessage().'</br>';
	echo "A node can not be added to the tail of a list if already in the list.<br/>First remove the node then add it to the tail of the list.<br/><br/>";
}

echo '<br/>NOTICE *** ERRORS ABOVE WHERE MADE ON PURPOSE TO TEST EXCEPTIONS. ***<br/><br/>';

$listd->swap($node1, $node5);
$listd->walk();

echo '<br>************************************************************<br>';
$data = array('id' => 'X1', 'var1' => 'HELLO-x1', 'var2' => 'WORLD-x1');
$node = new List_Node($data);

$lista->replace($node, $lista->get_node('a1'));
$lista->walk();

echo '<br>************************************************************<br>';
$listb->swap($listb->get_node('b1'), $listc->get_node(1));
$listb->walk();

echo '<br>* Node Test ***********************************************************<br>';
$data = array(
	array('var1' => 'HELLO-e1', 'var2' => 'WORLD-e1'),
	array('var1' => 'HELLO-e2', 'var2' => 'WORLD-e2')
);

$data1 = array('var1' => 'HELLO-e3', 'var2' => 'WORLD-e3');
$data2 = array('var1' => 'HELLO-e4', 'var2' => 'WORLD-e4');
$data3 = array('var1' => 'HELLO-e5', 'var2' => 'WORLD-e5');

$node1 = new List_Node($data1);
$node2 = new List_Node($data2);
$node3 = new List_Node($data3);

$liste = new Linked_list($data);

$node = $liste->first();

$node->insert_after($node1);
$node1->insert_before($node2);
$node->replace($node3);
$liste->walk();
