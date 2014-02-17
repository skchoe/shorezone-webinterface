<?php

require_once(dirname(__FILE__)."/doublelist.class.php");
require_once(dirname(__FILE__)."/../viz/dataUtils.inc.php");
require_once(dirname(__FILE__)."/../db2tile/db_credentials.php");


ini_set('memory_limit', '1024M');

// P1 - P2 are points of segment S 
// P3 - P4 are points of segment T
// output: array(x,y) for intersection point of S T if they intersect
//         FALSE if they don't intersect or overlap.
function intersect($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4)
{
  // parallel? check external product sin(theta) = 0
  $d = ($x1-$x2)*($y3-$y4) - ($y1-$y2)*($x3-$x4);
  if ($d == 0) { //echo "Parallel </br>"; 
    return FALSE; }
  
  $xi = (($x3-$x4)*($x1*$y2-$y1*$x2)-($x1-$x2)*($x3*$y4-$y3*$x4))/$d;
  $yi = (($y3-$y4)*($x1*$y2-$y1*$x2)-($y1-$y2)*($x3*$y4-$y3*$x4))/$d;
  
  echo "A pt intersect infinite line: ".$xi."(".min($x1,$x2).", ".max($x1,$x2)."), ".$yi."(".min($y1,$y2).",".max($y1,$y2).")</br>";
  echo "A pt intersect infinite line: ".$xi."(".min($x3,$x4).", ".max($x3,$x4)."), ".$yi."(".min($y3,$y4).",".max($y3,$y4).")</br>";
  ///////////////////////////////////////////////////////
  ///////////////////////////////////////////////////////
  ///////////////////////////////////////////////////////
  ///////////////////////////////////////////////////////
  ///////////////////////////////////////////////////////
  ///////////////////////////////////////////////////////
  ///////////////////////////////////////////////////////
  ///////////////////////////////////////////////////////
  ///////////////////////////////////////////////////////
  if ($xi < min($x1,$x2) || max($x1,$x2) < $xi) { 
    /*echo "x-outof range for seg ".$x1.", ".$x2."</br>";*/ return FALSE; }
  if ($xi < min($x3,$x4) || max($x3,$x4) < $xi) { 
    /*echo "x-outof range for seg ".$x3.", ".$x4."</br>";*/ return FALSE; }
  if ($yi < min($y1,$y2) || max($y1,$y2) < $yi) { 
    /*echo "y-outof range for seg ".$y1.", ".$y2."</br>";*/ return FALSE; }
  if ($yi < min($y3,$y4) || max($y3,$y4) < $yi) { 
    /*echo "y-outof range for seg ".$y3.", ".$y4."</br>";*/ return FALSE; }

  echo "INSIDE - XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXintersect ".$xi.", ".$yi."</br>";
  $p = array($xi,$yi);
  return $p;
}

// input : reference of $polylist, polynode
// output updated $polylist
// $polylist  Doubly linked list 
// $part  Geoarray (x y x' y' ...) 
function part2polylist($polylist, $part)
{
  $num_coord = count($part);
  $num_pt = $num_coord/2;

  // node 
  for($p = 0;$p < $num_pt;$p++) 
  { 
    $j = $p * 2;
    $x = $part[$j];
    $y = $part[$j+1];

    $polylist->insertLast($x, $y);
  }

  echo "part pt number = ".$num_pt."</br>";
  echo "node number = ".$polylist->totalNodes()."</br>";
  return $polylist;
}

// define doubly linked list for tile corners : same direction as $cwFlag 
// closed rectangle represented as 5 points
function range2cornerlist($tilelist, $cwFlag, $swlng, $nelng, $swlat, $nelat)
{
  // CW 
  if($cwFlag) {
    $tilelist->insertLast($nelng, $nelat);
    $tilelist->insertLast($nelng, $swlat);
    $tilelist->insertLast($swlng, $swlat);
    $tilelist->insertLast($swlng, $nelat);
    $tilelist->insertLast($nelng, $nelat);
  } 
  // CCW
  else {
    $tilelist->insertLast($nelng, $nelat);
    $tilelist->insertLast($swlng, $nelat);
    $tilelist->insertLast($swlng, $swlat);
    $tilelist->insertLast($nelng, $swlat);
    $tilelist->insertLast($nelng, $nelat);
  }
  return $tilelist;
}

function range2part($cwFlag, $swlng, $nelng, $swlat, $nelat)
{
  $part = array();
  // CW 
  if($cwFlag) {
    $part[] = $nelng; $part[] = $nelat;
    $part[] = $nelng; $part[] = $swlat;
    $part[] = $swlng; $part[] = $swlat;
    $part[] = $swlng; $part[] = $nelat;
    $part[] = $nelng; $part[] = $nelat;
    return $part;
  }
  // CCW
  else {
    $part[] = $nelng; $part[] = $nelat;
    $part[] = $swlng; $part[] = $nelat;
    $part[] = $swlng; $part[] = $swlat;
    $part[] = $nelng; $part[] = $swlat;
    $part[] = $nelng; $part[] = $nelat;
    return $part;
  }
}


function update_subject_clip_polygons($ix, $iy, &$slst, $sn_prev, &$clst, $cn_prev) 
{
  $proximity = 0.00001;
  //echo "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX<br>";
  //echo "ix = ".$ix.", iy = ".$iy."</br>";

  $sflag = FALSE;
  $cflag = FALSE;

  // New node or existing node to set isect
  // subject list 
  //echo "SN xy </br>";
  //$sn_prev->echoXY();
  //$sn_prev->next->echoXY();

  $dspx = abs($ix - $sn_prev->x);
  $dspy = abs($iy - $sn_prev->y);
  //echo "dspx,y = ".$dspx.", ".$dspy."</br>";
  if ($dspx < $proximity && $dspy < $proximity ) {
    //echo "3same</br>"; 
    $bs_prev = TRUE;
  }
  else { 
    //echo "3diff</br>"; 
    $bs_prev = FALSE; 
  }

  $dsnx = abs($ix - $sn_prev->next->x);
  $dsny = abs($iy - $sn_prev->next->y);
  //echo "dsnx,y = ".$dsnx.", ".$dsny."</br>";

  if ($dsnx < $proximity && $dsnx < $proximity) {
    //echo "4same</br>"; 
    $bs_next = TRUE;
  } 
  else {
    //echo "4diff</br>"; 
    $bs_next = FALSE;}

  //echo "bool pt-comparison for subject: ".$bs_prev.", ".$bs_next."...</br>";
  $new_snd = FALSE;
  if($bs_prev) {
    $new_snd = $sn_prev;
    $new_snd->isect = TRUE;
  }
  else if($bs_next) {
    $new_snd = $sn_prev->next;
    $new_snd->isect = TRUE;
  }
  else  {
    $sflag = TRUE;
    $new_snd = new ListNode($ix, $iy);
    $new_snd->isect = TRUE;
  }

  // clip list
  //echo "CN xy </br>";
  //$cn_prev->echoXY();
  //$cn_prev->next->echoXY();
  
  $dcpx = abs($ix - $cn_prev->x);
  $dcpy = abs($iy - $cn_prev->y);
  //echo "dcpx,y = ".$dcpx.", ".$dcpy."</br>";
  if ($dcpx < $proximity && $dcpy < $proximity ) {
    //echo "0same</br>"; 
    $bc_prev = TRUE; 
  }
  else {//echo "0diff</br>"; 
    $bc_prev = FALSE;
  }

  $dcnx = abs($ix - $cn_prev->next->x);
  $dcny = abs($iy - $cn_prev->next->y);
  if ($dcnx < $proximity && $dcny < $proximity) {
    //echo "1same</br>"; 
    $bc_next = TRUE;
  } 
  else {//echo "1diff</br>"; 
    $bc_next = FALSE;
  }

  //echo "bool pt-comparison for clip: ".$bc_prev.", ".$bc_next."...</br>";
  $new_cnd = FALSE;
  if($bc_prev) {
    $new_cnd = $cn_prev;
    $new_cnd->isect = TRUE;
  }
  else if($bc_next) {
    $new_cnd = $cn_prev->next;
    $new_cnd->isect = TRUE;
  }
  else  {
    $cflag = TRUE;
    $new_cnd = new ListNode($ix, $iy);
    $new_cnd->isect = TRUE;
  }

  // neighbor
  $new_snd->neighbor = $new_cnd;
  $new_cnd->neighbor = $new_snd;

  // insert/update nodes
  // subject polygon
  if($bs_prev)
    $slst->replaceNode($sn_prev, $new_snd);
  else if($bs_next) 
    $slst->replaceNode($sn_prev->next, $new_snd);
  else 
    $slst->insertNodeAfter($sn_prev, $new_snd);

  // clip polygon
  if($bc_prev)
    $clst->replaceNode($cn_prev, $new_cnd);
  else if($bc_next) 
    $clst->replaceNode($cn_prev->next, $new_cnd);
  else 
    $clst->insertNodeAfter($cn_prev, $new_cnd);
 
  echo "OUTPUT flags (s, c) = ".$sflag.", ".$cflag."</br>";
  return array($sflag, $cflag);
}

// input : same elts as draw_tile_clipped_polygons
// $part is a subject polygon with begin==end
// $bbx : boundingbox of $part
// $swlng, $nelng, $swlat, $nelat: tiles for clip polygon
// Output: array of simple polygon: part: whose end points coincide
function clipping_algorithm($part, $cwFlag, $bbx, $swlng, $nelng, $swlat, $nelat)
{
  // CASE 0. Disjoint by x/y direction
  if($bbx[1] < $swlng || $nelng < $bbx[0] || $bbx[3] < $swlat || $nelat < $bbx[2])
  {
    echo "CASE 0. DISJOINT : </br>";
    return array();
  }

  // CASE 1. inclusion case 
  // $bbx = array(minx, maxx, miny, maxx);
  //print_r($bbx); echo "</br>";
  else if($swlng <= $bbx[0] && $bbx[1] < $nelng && $swlat <= $bbx[2] && $bbx[3] < $nelat)
  {
    echo "CASE 1. INCLUSION : </br>";
    return array($part);
  }

  else 
  {
    echo "CASE 2. NON-INCLUSION-DISJOINT : </br>";
    // CASE 2. else of CASE 0,1.
    // doubly linked lists for clip polygon, subject polygon.
  
    // define liked list (container of fist/last nodes)
    $poly_list = new DoublyLinkedList();
    $poly_list = part2polylist($poly_list, $part);
  
    //echo "Total node for subject polygon = ".$poly_list->totalNodes()."</br>";
    //$poly_list->echoXYs();
    //echo "</br>";
  
    $tile_list = new DoublyLinkedList();
    $tile_list = range2cornerlist($tile_list, $cwFlag, $swlng, $nelng, $swlat, $nelat);
  
    //echo "Total node for clip polygon = ".$tile_list->totalNodes()."</br>";
    //$tile_list->echoXYs();
    //echo "</br>";
  
    // Phase 1. intersection points insertion
    $beginTileNode = $tile_list->getFirstNode();
    $endTileNode = $beginTileNode->next;

    $intersectFlag = 0;
    // need tileNode->nect to be not null to get segment
    while ($endTileNode != FALSE) {
  
      $tx1 = $beginTileNode->x;
      $ty1 = $beginTileNode->y;
  
      $beginPolyNode = $poly_list->getFirstNode();
      $endPolyNode = $beginPolyNode->next;
      // need polyNode->nect to be not null to get segment
      while ($endPolyNode != FALSE) {
  
        $tx2 = $endTileNode->x;
        $ty2 = $endTileNode->y;
  
        $px1 = $beginPolyNode->x;
        $py1 = $beginPolyNode->y;
        $px2 = $endPolyNode->x;
        $py2 = $endPolyNode->y;

        $p_isect = intersect($tx1, $ty1, $tx2, $ty2, $px1, $py1, $px2, $py2);
        if($p_isect != FALSE) {
  
          $intersectFlag++;

          $isect_x = $p_isect[0];
          $isect_y = $p_isect[1];
          echo "New Tile tile_segment (".$tx1.", ".$ty1.") - (".$tx2.", ".$ty2.")</br>";
          echo "New Poly poly_segment (".$px1.", ".$py1.") - (".$px2.", ".$py2.")</br>";
          echo "INTERSECT: ".$isect_x.", ".$isect_y." w/ begin tile -->";
          echo "(".$tx1.", ".$ty1.")----(".$tx2.", ".$ty2.") vs (".$px1.", ".$py1.")----(".$px2.", ".$py2.") </br>";
  
          list($pflag, $tflag) = update_subject_clip_polygons($isect_x, $isect_y, 
                                 $poly_list, $beginPolyNode, $tile_list, $beginTileNode);
       
        }
        if($pflag) 
          //echo "New poly begin ~ end: (".$beginPolyNode->x.", ".$beginPolyNode->y."), ~ (".$beginPolyNode->next->x.", ".$beginPolyNode->next->y.")</br>";
          $beginPolyNode = $beginPolyNode->next;
        else
          $beginPolyNode = $endPolyNode;
  
        $endPolyNode = $beginPolyNode->next;
      }
      if($tflag)
        //echo "New tile begin ~ end: (".$beginTileNode->x.", ".$beginTileNode->y."), ~ (".$beginTileNode->next->x.", ".$beginTileNode->next->y.")</br>";
        $beginTileNode = $beginTileNode->next;
      else
        $beginTileNode = $endTileNode;
  
      $endTileNode = $beginTileNode->next;
    }

    if($intersectFlag == 0) {
      echo "</br>CASE 2.1 Tile is included to Part=> Tile is return part ->></br>";
      $epsilon = 0.0000001;
      $tile_part = range2part($cwFlag, $swlng+$epsilon, $nelng-$epsilon, $swlat+$epsilon, $nelat-$epsilon);

      print_r($tile_part); echo "</br>";

      return array($tile_part);
    }

    echo "Tile_list_totalNodes: ".$tile_list->totalNodes()."</br>";
    $tile_list->echoXYs();
    echo "Poly_list_totalNodes: ".$poly_list->totalNodes()."</br>";
    $poly_list->echoXYs();

    // Phase 2. intersection points insertion, property setting
  
    // 2.1 Store out/in/on info for all point in $tile_list, $poly_list as hash table([id->'on'/'out'/'in')
    $tile_outinon = check_outinon($tile_list, $poly_list);
    $poly_outinon = check_outinon($poly_list, $tile_list);
  
    echo "</br>tile_outinon = </br>";
    print_r($tile_outinon);
    echo "</br>";
    $tile_list->echoXYs();
  
    echo "</br>poly_outinon = </br>";
    print_r($poly_outinon);
    echo "</br>";
    $poly_list->echoXYs();

    // 2.2 Edge property
    // case 1
    // 2.2.1 - on?: if (S0 = pt->prev->neighbor, S1 = pt->neighbor) is an edge of subject polygon(poly), for pt in tile polygon(clip)
    //              how? -> check (S0->id == S1->prev->id || S0->id == S1->next->id) => the edge is on S. => set the edge property to be 'on'
    // case 2
    // 2.2.2 - in/out?: edge property - for tile polygon
    //                  else, check in/out property of center of edge. -> should be just the two cases: in, out.
    // because the polygon's 2 end points are in same position (different id though), we only start from first to last-1 points and examine edges from each of points
    // gamma array
  
    $tile_edge_prop = edge_property($tile_list, $poly_list, $tile_outinon);
    $poly_edge_prop = edge_property($poly_list, $tile_list, $poly_outinon);
  
    echo "tile_edge-property -></br>";
    print_r ($tile_edge_prop);
    echo "</br>-------------------</br>";
    
    echo "poly_edge-property -></br>";
    print_r ($poly_edge_prop);
    echo "</br>-------------------</br>";
    
    // edge_property -> traversal flag setting(non, ex, en, ex/en, en/ex)
    echo "Call-traversal flag for tile</br>";
    set_traversal_flag($tile_list, $tile_edge_prop);
    echo "Call-traversal flag for polygon</br>";
    set_traversal_flag($poly_list, $poly_edge_prop);
  
    echo "tile_traversal-flags -></br>";
    $tile_list->echoFlags();
    echo "</br>-------------------</br>";

    echo "poly_traversal-flags -></br>";
    $poly_list->echoFlags();
    echo "</br>----------------------------------------------------------</br>";
  
    // Phase 3. Traverse, compose part from new path.
    // 3.0 Circlization of doublely linked list
    circlize($tile_list);
    circlize($poly_list);

    echo "TILE >>></br>";
    $tile_list->echoPrevNext();
    echo "POLY >>></br>";
    $poly_list->echoPrevNext();

    // array of Parts created by clipping polygon by tile.
    $arr_clipped_part = array();

    // 3.1 decide starting node
    // tile_list(clip), poly_list(subject) -> init_node in polynode.
    $fnode = $poly_list->getFirstNode();
    $currentNode = firstIntersectNode($fnode);

    //while ($currentNode) {
      list($result_part, $sNode2Btraversed) = clipping($currentNode);
      $arr_clipped_part[] = $result_part;
  
      echo "Lastnode's traversal flag = $sNode2Btraversed->id .</br>";
      $sNode2Btraversed->echoXY();
      $currentNode = firstIntersectNode($sNode2Btraversed);
    //}

    return $arr_clipped_part;
  }
}

// $currentNode: isect=True
// $initNode : doubley linked list with neighbor node.
function clipping ($currentNode)
{
  $onSnodeFlag = TRUE;
  $sNode2Btraversed = $currentNode;

  // 3.2 traverse following flag.
  $newPositionFlag = TRUE;
  $fromOtherListFlag = FALSE;
  $outPart = array(); // same structure as Part {x0 y0 x1 y1 ...}
  while ($currentNode->flag) {
    echo "---------------------CURRNODE: $currentNode->id </br>";
    $currentNode->echoXY();
    $currentNode->echoTraversalFlag();
    $currentNode->echoPrevNext();

    // position input
    if($newPositionFlag) {
      $outPart[] = $currentNode->x;
      $outPart[] = $currentNode->y;
    }
    // decide next visit
    if ($currentNode->isect) {
      if($currentNode->flag == 'ex') {
        echo "ex--- $currentNode->id ...</br>";
        $currentNode->flag = FALSE;
        $currentNode = $currentNode->neighbor;
        // flip onSnodeFlag
        if($onSnodeFlag) $onSnodeFlag = FALSE; else $onSnodeFlag = TRUE;

        echo "NEXT node'flag[$currentNode->id] = >  >>>> $currentNode->flag </br>";
        $newPositionFlag = FALSE;
      } 
      elseif ($currentNode->flag == 'en') {
        echo "en--- $currentNode->id ...</br>";
        $currentNode->flag = FALSE;
        $currentNode = $currentNode->next;
        echo "NEXT node'flag = >  >>>> $currentNode->flag </br>";
        if($onSnodeFlag) $sNode2Btraversed = $currentNode;
        $newPositionFlag = TRUE;
      }
      elseif ($currentNode->flag == 'en/ex') {
        echo "en/ex--- $currentNode->id ...</br>";
        $currentNode->flag = 'ex';
        echo "NEXT node'flag = >  >>>> $currentNode->flag </br>";
        $newPositionFlag = FALSE;
      }
      elseif ($currentNode->flag == 'ex/en') {
        echo "ex/en--- $currentNode->id ...</br>";
        $currentNode->flag = 'en';
        echo "NEXT node'flag = >  >>>> $currentNode->flag </br>";
        $newPositionFlag = FALSE; 
      }
      elseif ($currentNode->flag == 'none') { //en or ex when en-en-en, ex-ex-ex, respectively
        echo "none(isect-true)--- $currentNode->id ...</br>";
        $currentNode->flag = FALSE;
        $currentNode = $currentNode->next;
        if($onSnodeFlag) $sNode2Btraversed = $currentNode;
        echo "NEXT node'flag = >  >>>> $currentNode->flag </br>";
        $newPositionFlag = TRUE;
      }
      else echo "Current Node's flag is false -> will stop adding point to outPart</br>";
    }
    else {
      if ($currentNode->flag == 'none') {
        echo "none(isect-false)--- $currentNode->id ...</br>";
        $currentNode->flag = FALSE;
      }
      $currentNode = $currentNode->next;
      if($onSnodeFlag) $sNode2Btraversed = $currentNode;
      echo "NEXT node'flag = >  >>>> $currentNode->flag </br>";
      $newPositionFlag = TRUE;
    }
  }
  echo "Outpart  = </br>";
  print_r($outPart);

  return array($outPart, $sNode2Btraversed->next);
}


// double linked list with equal geometry at each ends -> circlized dbl lst without geometry duplication
// 1. Delete last node
// 2. upldate prev/next for firstNode and lastNode of updated list
function circlize($lst_in)
{
  $lst_in->deleteLastNode();
  $firstNode = $lst_in->getFirstNode();
  $lastNode = $lst_in->getLastNode();
  $firstNode->prev = $lastNode;
  $lastNode->next = $firstNode;
}

// subject node -> node w/ isect=TRUE after input node
function firstIntersectNode($node)
{
  while($node != FALSE) {

    if($node->isect != FALSE)
      return $node;

    $node = $node->next;
  }
  return FALSE;
}

function edge_property($geo_list, $counter_list, $node_outinon)
{
  $edge_prop = array();

  $currentNode = $geo_list->getFirstNode();
  $nextNode = $currentNode->next;
  $c_x   = $currentNode->x;
  $c_y   = $currentNode->y;
  $c_id  = $currentNode->id;
  $c_oio = $node_outinon[$c_id];

  while($nextNode != FALSE) {

    $n_x   = $nextNode->x;
    $n_y   = $nextNode->y;
    $n_id  = $nextNode->id;
    $n_oio = $node_outinon[$n_id];

    echo "(".$c_x.", ".$c_y.") [".$c_id."] = ".$c_oio."----------->(".$n_x.", ".$n_y.") [".$n_id."] = ".$n_oio."</br>";
    if($c_oio == 'on' && $n_oio == 'on') { // case 2.2.1
      
      $edge = array($currentNode->neighbor, $nextNode->neighbor);
      $b_edgeinpolygon = contains_edge($edge, $counter_list); 
      if($b_edgeinpolygon) { 
        $edge_prop[$c_id] = 'on';
      }
      else { // case 2.2.2
        $mid_x = ($c_x + $n_x) / 2.0; 
        $mid_y = ($c_y + $n_y) / 2.0; 
        $edge_prop[$c_id] = check_outin($mid_x, $mid_y, $counter_list); 
          //output is either 'in' or 'out'.
        echo "on-on but not on to other polygon: ".$edge_prop[$c_id]." at ".$n_x.", ".$n_y."</br>";
      }
    }
    else if($c_oio == 'on') {// n_oio = not 'on'
      $edge_prop[$c_id] = $n_oio;
    }
    else // $c_oio is either 'in' or 'out'
      $edge_prop[$c_id] = $c_oio;

    if($edge_prop[$c_id] == 'in') echo "Edge Inside!</br>";
    else if($edge_prop[$c_id] == 'out') echo "Edge Outside!</br>";
    else echo "on - degenerate edge overlap: On border</br>";

    $c_x   = $n_x;
    $c_y   = $n_y;
    $c_id  = $n_id;
    $c_oio = $n_oio;

    $nextNode = $nextNode->next;
  }
  return $edge_prop;
}

// $geom_list - doubly linked list as polygon with same geometry at its end
// $edge_prop - list of next-edge's property : none, ex, en, ex/en, en/ex: 1 less elt than $geom_list
function set_traversal_flag($geom_lst, $edge_prop)
{
  $fnode = $geom_lst->getFirstNode();
  $lnode = $geom_lst->getLastNode();

  $node = $geom_lst->getFirstNode();

  while($node!=FALSE) {
    $isect = $node->isect;
    if($isect) {
      echo "current id : $node->id </br>";
      // get edge_props
      $id = $node->id;
      if($id == $fnode->id){ // First node
        $prev_id = $lnode->prev->id;
        $next_id = $id;
      }
      else if($id == $lnode->id) {
        $prev_id = $node->prev->id;
        $next_id = $fnode->id;
      }
      else {
        $prev_id = $node->prev->id;
        $next_id = $id;
      }
      echo "prev-nd-id: ".$prev_id." , next-nd-id: $next_id </br>";
      $prev_prop = $edge_prop[$prev_id];
      $next_prop = $edge_prop[$next_id];
      
      edgeprop_to_traversal_flag($prev_prop, $next_prop, $node);
    } 
    else $node->flag = 'none';

    $node = $node->next;
  }
}

// string, string, listnode -> void
function edgeprop_to_traversal_flag($prev_prop, $next_prop, $node)
{
  echo "prev, next = (".$prev_prop.", ".$next_prop.")</br>";
  switch ($prev_prop) {
    case 'on':
      switch ($next_prop) {
        case 'on':
          $node->flag = 'none';
          break;
        case 'out':
          $node->flag = 'ex';
          break;
        case 'in':
          $node->flag = 'en';
          break;
      }
      break; 
    case 'out':
      switch ($next_prop) {
        case 'on':
          $node->flag = 'en';
          break;
        case 'out':
          $node->flag = 'en/ex';
          break;
        case 'in':
          $node->flag = 'en';
          break;
      }
      break; 
    case 'in':
      switch ($next_prop) {
        case 'on':
          $node->flag = 'ex';
          break;
        case 'out':
          $node->flag = 'ex';
          break;
        case 'in':
          $node->flag = 'ex/en';
          break;
      }
      break; 
  }
}

// doubly linked list of node, array(beginN, endN) -> boolean
// check if the edge is part of poly_list
// find beginN node from poly_list -> check =>prev, ->next to see them same as endN
// comparision is based on ID.
// no orientation involved.
function contains_edge($edgearr, $poly_list)
{
  $beginN = $edgearr[0];
  $endN = $edgearr[1];

  $beginID = $beginN->id;
  $endID = $endN->id;

  $fnode = $poly_list->getFirstNode();
  $lnode = $poly_list->getLastNode();

  // find same node in $poly_list
  $node = $poly_list->getFirstNode();
  while ($node != FALSE) {
    if($node->id == $beginID) break;
    else $node = $node->next;
  }

  // first node
  if($node->id == $fnode->id) {
    if($endID == $fnode->next->id || $endID == $lnode->prev->id) return TRUE;
    else return FALSE;
  }
  // last node
  else if($node->id == $lnode->id) {
    if($endID == $lnode->prev->id || $endID == $fnode->next->id) return TRUE;
    else return FALSE;
  }
  // interior node
  else {
    if($endID == $node->prev->id || $endID == $node->next->id) return TRUE;
    else return FALSE;
  }
  
}

// 0:even
// 1:odd
function is_odd($number) {
   $val = $number & 1; 
   if($val==0) return FALSE;
   else if($val==1) return TRUE;
   else {
    echo "Cannot find even or odd (is_odd)</br>";
    exit;
  }
}

function check_outin($x, $y, $target_poly_list)
{
  $v = array($x, $y);
  $wn = windingNumberDLL($v, $target_poly_list);
  //echo "WN(".$wn.") ";
  if ($wn == 0) $output = 'out'; // 'in' interms of geometry w/o considering orientation (visual).
  else $output = 'in'; 

  //echo "check ---- outin ($x, $y) : $output </br>";

  return $output;
}

function check_outinon_node($nd, $target_poly_list)
{
  $output = 0;
  if($nd->isect) // intersect to target poly list
    $output = 'on';
  else { // nd is either interior or exterior
    $output = check_outin($nd->x, $nd->y, $target_poly_list);
  }

  return $output;
}

// create hash table [id] -> either 'in', 'out', 'on'.
// if point in $given_poly_list to polygon $target_poly_list.
// point has id field 
// wn = the winding number (=0 only if P is outside target_poly_list)
function check_outinon($given_poly_list, $target_poly_list) {
  $outinon = array();
  
  $node = $given_poly_list->getFirstNode();
  while ($node != FALSE) {

    $id = $node->id;
    $outinon[$id] = check_outinon_node($node, $target_poly_list);
     
    echo "Winding # for (".$node->x.", ".$node->y.") =>".$wn.":".$outinon[$id]."</br>";
    $node = $node->next;
  }
  
  return $outinon;
}


function draw_tile_clipped_polygons($im, $part, $cwFlag, $bbx, $swlng, $nelng, $swlat, $nelat, $zoom, $interiorColor, $holeColor)
{
  
  $arr_subpolygons = clipping_algorithm($part, $cwFlag, $bbx, $swlng, $nelng, $swlat, $nelat);
  // Clipping Alg
  echo "_______________polynode from clipping algorithm: num sub-parts: ".count($arr_subpolygons). "</br>";
  
  foreach($arr_subpolygons as $polygon){
    echo "</br>";
    $major_cw = TRUE;
    $outnum = 0;
    draw_gd_polygon($polygon, $zoom, $im, $cwFlag, $interiorColor, $holeColor, $major_cw, $outnum);
  }
  print_r($poly_list);
  echo "</br>";
  return FALSE;

}

?>
