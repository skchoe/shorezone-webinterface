<?php

require_once dirname(__FILE__).'/doublelist.class.php';
//require_once 'PHPUnit/Framework.php';

function testLinkList() {
        
        $totalNodes = 100;
        
        $theList = new DoublyLinkedList();
    
        for($i=1; $i <= $totalNodes; $i++) {
            $theList->insertLast($i, $i+1);
        }
        
        if($totalNodes == $theList->totalNodes()) echo "1";;

        for($i=1; $i <= $totalNodes; $i++) {
            $theList->insertFirst($i, $i+1);
        }
	
        $totalNodes = $totalNodes * 2;
        if($totalNodes ==  $theList->totalNodes()) echo "2";;
        
        $theList->deleteFirstNode(); $totalNodes--;
        if($totalNodes ==  $theList->totalNodes()) echo "3";;
        
        $theList->deleteLastNode(); $totalNodes--;
        if($totalNodes ==  $theList->totalNodes()) echo "4";;
        
        /* Delete node which has a key value of '50, 51' */
        $nd = $theList->deleteNode(50, 51); 
        echo "after delete = ".$nd->x.", ".$nd->y."</br>";
        $totalNodes--;
        if($totalNodes ==  $theList->totalNodes()) echo "5";;
        
        /* Insert a node at the end of the list with a value of '22, 23' */
        $nd = $theList->insertLast(22, 23); 
        echo "after insert = ".$nd->x.", ".$nd->y."</br>";
        $totalNodes++;
        if($totalNodes ==  $theList->totalNodes()) echo "6";;
    }

testLinkList();
?>
