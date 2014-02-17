<?php

/** 
* Title: Double linked list
* Description: Implementation of a double linked list in PHP 
* @author Sameer Borate | http://www.codediesel.com
* Reference : Data Structures & Algorithms in Java : Robert Lafore
* @version 1.0 20th June 2009

* Seungkeol Choe modified it to access by Node,  
* Nodes structure is specialized to fit to polygon clipping Alogirthm by Kim 2007.
*/


class ListNode {
    
    //current data part;
    public $id; // randomly generated integer
    public $x;
    public $y;
    public $isect; // boolean to see if it's on edge of other polygon
    public $flag; // as defined in paper.
    public $neighbor; // pointer to node of other polygon
    public $couple;
    public $cross_change;

    // linked list part
    public $next;
    public $prev;

    function __construct($x, $y) {
        $this->id = rand();
        $this->x = $x;
        $this->y = $y;
    }

    public function echoNode() {
        $arr = array($this->id, $this->x, $this->y, $this->isect, $this->neighbor, $this->couple, $this->cross_change);
        print_r($arr);
        echo "</br>";
        return FALSE;
    }

    public function echoXY()
    {
        $x = $this->x;
        $y = $this->y;
        $isect = $this->isect;
        $id = $this->id;
        $nid = $this->neighbor->id;
        echo "(".$x.", ".$y."-isct:".$isect.":".$id.", nbid:".$nid.")</br>";
    }
   
    public function echoTraversalFlag()
    {
        $isect = $this->isect;
        $id = $this->id;
        $tflg = $this->flag;
        echo "[".$id."] w isect=".$isect." flag = ".$tflg."</br>";
    }

    public function echoPrevNext()
    {
        $isect = $this->isect;
        $id = $this->id;
        $tflg = $this->flag;
        echo "[".$id."] w previd=".$this->prev->id." nextid = ".$this->next->id."</br>";
    }
}


class DoublyLinkedList {
    
    private $_firstNode;
    private $_lastNode;
    private $_count;

    function __construct() {
        $this->_firstNode = FALSE;
        $this->_lastNode = FALSE;
        $this->_count = 0;
    }

    public function isEmpty() {
        return ($this->_firstNode == FALSE);
    }

    public function insertNodeFirst($nd) {

        if($this->isEmpty()) {
            $this->_lastNode = $nd;
        } else {
            $this->_firstNode->prev = $nd;
        }
        
        $nd->next = $this->_firstNode;
        $this->_firstNode = $nd;
        $this->_count++;

        return $nd;
    }

    public function insertFirst($x, $y) {
        $newLink = new ListNode($x, $y);
 
        return $this->insertNodeFirst($newLink);        
    }
    
    public function insertNodeLast($nd) {

        if($this->isEmpty()) {
            $this->_firstNode = $nd;
        } else {
            $this->_lastNode->next = $nd;
        }
        
        $nd->prev = $this->_lastNode;
        $this->_lastNode = $nd;
        $this->_count++;

        return $nd;
    }
    
    public function insertLast($x, $y) 
    {
        $newLink = new ListNode($x, $y);
        
        return $this->insertNodeLast($newLink);
    }
    
    public function insertNodeAfter($prev_nd, $new_nd)
    {
      $new_nd->prev = $prev_nd;
      $new_nd->next = $prev_nd->next;

      $prev_nd->next = $new_nd;
      $next_nd->prev = $new_nd;
   
      $this->_count++;
        
      return true;
    }
    
    public function insertAfter($node, $x, $y) {
        $newLink = new ListNode($x, $y);

        return insertNodeAfter($node, $newLink);
    }

    
    public function deleteFirstNode() {
        
        $temp = $this->_firstNode;
        
        if($this->_firstNode->next == FALSE) {
            $this->_lastNode = FALSE;
        } else {
            $this->_firstNode->next->prev = FALSE;
        }
        
        $this->_firstNode = $this->_firstNode->next;
        $this->_count--;
        return $temp;
    }


    public function deleteLastNode() {
        
        $temp = $this->_lastNode;
        
        if($this->_firstNode->next == FALSE) {
            $this->firtNode = FALSE;
        } else {
            $this->_lastNode->prev->next = FALSE;
        }
        
        $this->_lastNode = $this->_lastNode->prev;
        $this->_count--;
        return $temp;
    }
    
    
    public function deleteNode($nd) {
        
        $prev_nd = $nd->prev;
        $next_nd = $nd->next;
        
        if($prev_nd != FALSE) {
          $prev_nd->next = $next_nd;
        } 
        
        if($next_nd != FALSE) {
          $next_nd->prev = $prev_nd;
        } 

        $this->_count--;
        return $nd;
    }

    public function replaceNode($ond, $nnd) {
        
        $nnd->prev = $ond->prev;
        $nnd->next = $ond->next;

        $ond->prev->next = $nnd;
        $ond->next->prev = $nnd;

        return $nnd;
    }

    public function displayForward() {
        
        $current = $this->_firstNode;
        
        while($current != FALSE) {
            echo $current->echoNode() . " ";
            $current = $current->next;
        }
    }
    
    
    public function displayBackward() {
        
        $current = $this->_lastNode;
        
        while($current != FALSE) {
            echo $current->echoNode() . " ";
            $current = $current->prev;
        }
    }
    
    public function totalNodes() {
        return $this->_count;
    }

    public function getFirstNode() {
        return $this->_firstNode;
    } 

    public function getLastNode() {
        return $this->_lastNode;
    } 

    public function echoXYs() {

        $current = $this->_firstNode;
        $cnt = 0;
        while($cnt != $this->totalNodes()) {
          echo "[".$cnt++."]";
          $current->echoXY();
          $current = $current->next;
        }
        echo "</br>";
    }

    public function echoFlags() {

        $current = $this->_firstNode;
        $cnt = 0;
        while($cnt != $this->totalNodes()) {
          echo "[".$cnt++."]";
          $current->echoTraversalFlag();
          $current = $current->next;
        }
        echo "</br>";
    }

    public function echoPrevNext() {

        $current = $this->_firstNode;
        $cnt = 0;
        while($cnt != $this->totalNodes()) {
          echo "[".$cnt++."]";
          $current->echoPrevNext();
          $current = $current->next;
        }
        echo "</br>";
    }
}


?>
