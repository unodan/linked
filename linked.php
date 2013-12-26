<?php

/**
* Linked list node.
* Author: Dan Huckson
* Auther Email: DanHuckson@gmail.com
* 
* Version: 2.1
* Date: 2013/12/26
*
* Class: List_Node
*
* Methods: id(), is_first(), is_last(), is_zombie(), insert_before(), 
*          insert_after(), replace(), remove()
*
*
*****************************************************************************************/
class List_Node {
	function __construct(&$data=NULL) {
		$this->id = $this->id(&$data);
		$this->data = $data;
		$this->parent = NULL; 
		$this->ln_Succ = NULL; 
		$this->ln_Pred = NULL;
	}
	
	function id(&$data) {
		static $id = 0;
		$this->id = (isset($data['id']) && $data['id']) ? $data['id']:++$id;
		return $this->id;
	}
	
	function is_first() {return ($this === $this->parent->lh_Head) ? TRUE:FALSE;}
	
	function is_last() {return ($this === $this->parent->lh_Tail) ? TRUE:FALSE;}
	
	function is_zombie () {return (!$this->parent || !$this->parent->has_node($this)) ? TRUE:FALSE;}
	
	function insert_after(&$node) {
		if (!$this->parent) return FALSE;
		else return $this->parent->after($this, $node);
	}
	
	function insert_before(&$node) {
		if (!$this->parent) return FALSE;
		else return $this->parent->before($this, $node);
	}
	
	function replace(&$node) {$this->parent->replace($node, $this);}
	
	function remove() {if (!$this->is_zombie()) $this->parent->remove($this);}
}

/**
* Double linked list.
* Author: Dan Huckson
* Auther Email: DanHuckson@gmail.com
* 
* Version: 2.1
* Date: 2013/12/26
*
* Class: Linked List
*
* Methods: init(), add_head(), add_tail(), before(), after(), remove(), index_of()
*          size(), first(), last(), is_first(), is_last(), is_empty(), has_node(), get_node(), swap()
*          replace(), walk(), dump().
*
*****************************************************************************************/
class Linked_List {
	function __construct(&$list=NULL) {
		$this->lh_Head = $this->lh_TailPred;
		$this->lh_Tail = $this->lh_TailPred;
		$this->lh_TailPred = NULL;
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
		while ($node) {
			$count++;
			$node = $node->ln_Pred;
		}
		return $count;
	}
	
	function get_node($id, $list='') {
		$match = FALSE;
		if (empty($list)) $node = $this->lh_Head;
		else $node = $list->lh_Head;
		
		while ($node && !$match) {
			if ($node->data['content'] instanceof Linked_List) 
				$match = $this->get_node($id, &$node->data['content']);
			
			if ($node->id === $id) $match = $node;
		
			$node = $node->ln_Pred;
		}
		return $match;
	}
	
	function index_of(&$node) {
		$index = 0;
		$list_node = $this->first();
		while ($list_node) {
			if ($list_node->id === $node->id) break;
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
		if ($this->has_node($node)) return FALSE;
		
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
	}
	
	function add_tail(&$node) {
		if ($this->has_node($node)) return FALSE;
		
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
	}
	
	function before(&$node1, &$node2) {
		if ($node2->is_zombie() || $node1 === $node2) return FALSE;
		
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
		$parent2 = $node2->parent;
		$ln_Succ = $node2->ln_Succ;
		
		$node1->parent->after($node2, $node1);
		$node1->parent->remove($node1);
		
		if ($ln_Succ) $parent2->after($node1, $ln_Succ);
		else $parent2->add_head($node1);
	}
	
	function replace(&$node1, &$node2) {
		if ($node2->is_zombie() || $node1 === $node2) return FALSE;
		
		$node2->parent->after($node1, $node2);
		$node2->remove();
	}
	
	function remove(&$node) {
		$parent = $node->parent;
		if ($node->is_zombie()) return FALSE;
		
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
		$node = $this->lh_Head;
		
		$cnt = 0;
		$html = '
				List Size '.$this->size().'<br/>
				List Head Node ID: '.(($this->lh_Head->id !== NULL) ? $this->lh_Head->id:'NULL').'<br/>
				List Tail Node ID: '.(($this->lh_Tail->id !== NULL) ? $this->lh_Tail->id:'NULL').'<br/>
				List TailPred: '.(($this->lh_TailPred) ? $this->lh_TailPred->id:'NULL') . '<br/><br/><br/>';
		while ($node) {
			
			$html .= '<br/>
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


/**
* Text code.
* Author: Dan Huckson
* Auther Email: DanHuckson@gmail.com
* 
* Version: 2.1
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
$listd->add_head($node5);
$listd->after($node4, $node2);
$listd->before($node3, $node4);
$listd->add_tail($node1);
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
echo '<br>************************************************************<br>';
$listc->walk();




