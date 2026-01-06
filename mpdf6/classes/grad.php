<?php

class grad {

var $mpdf = null;

function grad(&$mpdf) {
	$this->mpdf = $mpdf;
}

// mPDF 5.3.A1
function CoonsPatchMesh($x, $y, $w, $h, $patch_array=array(), $x_min=0, $x_max=1, $y_min=0, $y_max=1, $colspace='RGB', $return=false){
	$s=' q ';
	$s.=sprintf(' %.3F %.3F %.3F %.3F re W n ', $x*_MPDFK, ($this->mpdf->h-$y)*_MPDFK, $w*_MPDFK, -$h*_MPDFK);
	$s.=sprintf(' %.3F 0 0 %.3F %.3F %.3F cm ', $w*_MPDFK, $h*_MPDFK, $x*_MPDFK, ($this->mpdf->h-($y+$h))*_MPDFK);
	$n = count($this->mpdf->gradients)+1;
	$this->mpdf->gradients[$n]['type'] = 6; //coons patch mesh
	$this->mpdf->gradients[$n]['colorspace'] = $colspace; //coons patch mesh
	$bpcd=65535; //16 BitsPerCoordinate
	$trans = false;
	$this->mpdf->gradients[$n]['stream']='';
	for($i=0;$i<count($patch_array);$i++){
		$this->mpdf->gradients[$n]['stream'].=chr($patch_array[$i]['f']); //start with the edge flag as 8 bit
		for($j=0;$j<count($patch_array[$i]['points']);$j++){
			//each point as 16 bit
			if (($j % 2) == 1) {	// Y coordinate (adjusted as input is From top left)
				$patch_array[$i]['points'][$j]=(($patch_array[$i]['points'][$j]-$y_min)/($y_max-$y_min))*$bpcd;
				$patch_array[$i]['points'][$j]=$bpcd-$patch_array[$i]['points'][$j];
			}
			else {
				$patch_array[$i]['points'][$j]=(($patch_array[$i]['points'][$j]-$x_min)/($x_max-$x_min))*$bpcd;
			}
			if($patch_array[$i]['points'][$j]<0) $patch_array[$i]['points'][$j]=0;
			if($patch_array[$i]['points'][$j]>$bpcd) $patch_array[$i]['points'][$j]=$bpcd;
			$this->mpdf->gradients[$n]['stream'].=chr(floor($patch_array[$i]['points'][$j]/256));
			$this->mpdf->gradients[$n]['stream'].=chr(floor($patch_array[$i]['points'][$j]%256));
		}
		for($j=0;$j<count($patch_array[$i]['colors']);$j++){
			//each color component as 8 bit
			if ($colspace=='RGB') {
				$this->mpdf->gradients[$n]['stream'].=($patch_array[$i]['colors'][$j][1]);
				$this->mpdf->gradients[$n]['stream'].=($patch_array[$i]['colors'][$j][2]);
				$this->mpdf->gradients[$n]['stream'].=($patch_array[$i]['colors'][$j][3]);
				if (isset($patch_array[$i]['colors'][$j][4]) && ord($patch_array[$i]['colors'][$j][4])<100) { $trans = true; }
			}
			else if ($colspace=='CMYK') {
				$this->mpdf->gradients[$n]['stream'].=chr(ord($patch_array[$i]['colors'][$j][1])*2.55);
				$this->mpdf->gradients[$n]['stream'].=chr(ord($patch_array[$i]['colors'][$j][2])*2.55);
				$this->mpdf->gradients[$n]['stream'].=chr(ord($patch_array[$i]['colors'][$j][3])*2.55);
				$this->mpdf->gradients[$n]['stream'].=chr(ord($patch_array[$i]['colors'][$j][4])*2.55);
				if (isset($patch_array[$i]['colors'][$j][5]) && ord($patch_array[$i]['colors'][$j][5])<100) { $trans = true; }
			}
			else if ($colspace=='Gray') {
				$this->mpdf->gradients[$n]['stream'].=($patch_array[$i]['colors'][$j][1]);
				if ($patch_array[$i]['colors'][$j][2]==1) { $trans = true; }	// transparency converted from rgba or cmyka()
			}
		}
	}
	// TRANSPARENCY
	if ($trans) { 
		$this->mpdf->gradients[$n]['stream_trans']='';
		for($i=0;$i<count($patch_array);$i++){
			$this->mpdf->gradients[$n]['stream_trans'].=chr($patch_array[$i]['f']);
			for($j=0;$j<count($patch_array[$i]['points']);$j++){
				//each point as 16 bit
				$this->mpdf->gradients[$n]['stream_trans'].=chr(floor($patch_array[$i]['points'][$j]/256));
				$this->mpdf->gradients[$n]['stream_trans'].=chr(floor($patch_array[$i]['points'][$j]%256));
			}
			for($j=0;$j<count($patch_array[$i]['colors']);$j++){
				//each color component as 8 bit // OPACITY
				if ($colspace=='RGB') {
					$this->mpdf->gradients[$n]['stream_trans'].=chr(intval(ord($patch_array[$i]['colors'][$j][4])*2.55));
				}
				else if ($colspace=='CMYK') {
					$this->mpdf->gradients[$n]['stream_trans'].=chr(intval(ord($patch_array[$i]['colors'][$j][5])*2.55));
				}
				else if ($colspace=='Gray') {
					$this->mpdf->gradients[$n]['stream_trans'].=chr(intval(ord($patch_array[$i]['colors'][$j][3])*2.55));
				}
			}
		}
		$this->mpdf->gradients[$n]['trans'] = true;	
		$s .= ' /TGS'.$n.' gs ';
	}
	//paint the gradient
	$s .= '/Sh'.$n.' sh'."\n";
	//restore previous Graphic State
	$s .= 'Q'."\n";
	if ($return) { return $s; }
	else { $this->mpdf->_out($s); }
}


// type = linear:2; radial: 3;
// Linear: $coords - array of the form (x1, y1, x2, y2) which defines the gradient vector (see linear_gradient_coords.jpg). 
//    The default value is from left to right (x1=0, y1=0, x2=1, y2=0).
// Radial: $coords - array of the form (fx, fy, cx, cy, r) where (fx, fy) is the starting point of the gradient with color1, 
//    (cx, cy) is the center of the circle with color2, and r is the radius of the circle (see radial_gradient_coords.jpg). 
//    (fx, fy) should be inside the circle, otherwise some areas will not be defined
// $col = array(R,G,B/255); or array(G/255); or array(C,M,Y,K/100)
// $stops = array('col'=>$col [, 'opacity'=>0-1] [, 'offset'=>0-1])
function Gradient($x, $y, $w, $h, $type, $stops=array(), $colorspace='RGB', $coords='', $extend='', $return=false, $is_mask=false) {
	if (strtoupper(substr($type,0,1)) == 'L') { $type = 2; }	// linear
	else if (strtoupper(substr($type,0,1)) == 'R') { $type = 3; }	// radial
	if ($colorspace != 'CMYK' && $colorspace != 'Gray') {
		$colorspace = 'RGB';
	}
	$bboxw = $w;
	$bboxh = $h;
	$usex = $x;
	$usey = $y;
	$usew = $bboxw;
	$useh = $bboxh;

	if ($type < 1) { $type = 2; }
	if ($coords[0]!==false && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i',$coords[0],$m)) { 
		$tmp = $this->mpdf->ConvertSize($m[1],$this->mpdf->w,$this->mpdf->FontSize,false);
		if ($tmp) { $coords[0] = $tmp/$w; }
	}
	if ($coords[1]!==false && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i',$coords[1],$m)) { 
		$tmp = $this->mpdf->ConvertSize($m[1],$this->mpdf->w,$this->mpdf->FontSize,false);
		if ($tmp) { $coords[1] = 1-($tmp/$h); }
	}
	// LINEAR
	if ($type == 2) { 
		$angle = (isset($coords[4]) ? $coords[4] : false);
		$repeat = (isset($coords[5]) ? $coords[5] : false);
		// ALL POINTS SET (default for custom mPDF linear gradient) - no -moz
		if ($coords[0]!==false && $coords[1]!==false && $coords[2]!==false && $coords[3]!==false) {
			// do nothing - coords used as they are
		}

		// If both a <point> and <angle> are defined, the gradient axis starts from the point and runs along the angle. The end point is 
		// defined as before - in this case start points may not be in corners, and axis may not correctly fall in the right quadrant.
		// NO end points (Angle defined & Start points)
		else if ($angle!==false && $coords[0]!==false && $coords[1]!==false && $coords[2]===false && $coords[3]===false) {
		  if ($angle==0 || $angle==360) { $coords[3]=$coords[1]; if ($coords[0]==1) $coords[2]=2; else $coords[2]=1; }
		  else if ($angle==90) { $coords[2]=$coords[0]; $coords[3]=1; if ($coords[1]==1) $coords[3]=2; else $coords[3]=1; }
		  else if ($angle==180) { if ($coords[4]==0) $coords[2]=-1; else $coords[2]=0; $coords[3]=$coords[1]; }
		  else if ($angle==270) { $coords[2]=$coords[0]; if ($coords[1]==0) $coords[3]=-1; else $coords[3]=0; }
		  else {
			$endx=1; $endy=1; 
			if ($angle <=90) { 
				if ($angle <=45) { $endy=tan(deg2rad($angle)); }
				else { $endx=tan(deg2rad(90-$angle)); }
				$b = atan2(($endy*$bboxh), ($endx*$bboxw));
				$ny = 1 - $coords[1] - (tan($b) * (1-$coords[0]));
				$tx = sin($b) * cos($b) * $ny;
				$ty = cos($b) * cos($b) * $ny;
				$coords[2] = 1+$tx; $coords[3] = 1-$ty; 
			}
			else if ($angle <=180) { 
				if ($angle <=135) { $endx=tan(deg2rad($angle-90)); }
				else { $endy=tan(deg2rad(180-$angle)); }
				$b = atan2(($endy*$bboxh), ($endx*$bboxw));
				$ny = 1 - $coords[1] - (tan($b) * ($coords[0]));
				$tx = sin($b) * cos($b) * $ny;
				$ty = cos($b) * cos($b) * $ny;
				$coords[2] =  -$tx; $coords[3] = 1-$ty;
			}
			else if ($angle <=270) { 
				if ($angle <=225) { $endy=tan(deg2rad($angle-180)); }
				else { $endx=tan(deg2rad(270-$angle)); }
				$b = atan2(($endy*$bboxh), ($endx*$bboxw));
				$ny = $coords[1] - (tan($b) * ($coords[0]));
				$tx = sin($b) * cos($b) * $ny;
				$ty = cos($b) * cos($b) * $ny;
				$coords[2] = -$tx; $coords[3] = $ty; 
			}
			else { 
				if ($angle <=315) { $endx=tan(deg2rad($angle-270)); }
				else { $endy=tan(deg2rad(360-$angle));  }
				$b = atan2(($endy*$bboxh), ($endx*$bboxw));
				$ny = $coords[1] - (tan($b) * (1-$coords[0]));
				$tx = sin($b) * cos($b) * $ny;
				$ty = cos($b) * cos($b) * $ny;
				$coords[2] = 1+$tx; $coords[3] = $ty; 

			}
		  }
		}

		// -moz If the first parameter is only an <angle>, the gradient axis starts from the box's corner that would ensure the 
		// axis goes through the box. The axis runs along the specified angle. The end point of the axis is defined such that the 
		// farthest corner of the box from the starting point is perpendicular to the gradient axis at that point.
		// NO end points or Start points (Angle defined)
		else if ($angle!==false && $coords[0]===false && $coords[1]===false) {
		  if ($angle==0 || $angle==360) { $coords[0]=0; $coords[1]=0; $coords[2]=1; $coords[3]=0; }
		  else if ($angle==90) { $coords[0]=0; $coords[1]=0; $coords[2]=0; $coords[3]=1; }
		  else if ($angle==180) { $coords[0]=1; $coords[1]=0; $coords[2]=0; $coords[3]=0; }
		  else if ($angle==270) { $coords[0]=0; $coords[1]=1; $coords[2]=0; $coords[3]=0; }
		  else {
			if ($angle <=90) { 
				$coords[0]=0; $coords[1]=0; 
				if ($angle <=45) { $endx=1; $endy=tan(deg2rad($angle)); }
				else { $endx=tan(deg2rad(90-$angle)); $endy=1; }
			}
			else if ($angle <=180) { 
				$coords[0]=1; $coords[1]=0; 
				if ($angle <=135) { $endx=tan(deg2rad($angle-90)); $endy=1; }
				else { $endx=1; $endy=tan(deg2rad(180-$angle)); }
			}
			else if ($angle <=270) { 
				$coords[0]=1; $coords[1]=1; 
				if ($angle <=225) { $endx=1; $endy=tan(deg2rad($angle-180)); }
				else { $endx=tan(deg2rad(270-$angle)); $endy=1; }
			}
			else { 
				$coords[0]=0; $coords[1]=1; 
				if ($angle <=315) { $endx=tan(deg2rad($angle-270)); $endy=1; }
				else { $endx=1; $endy=tan(deg2rad(360-$angle));  }
			}
			$b = atan2(($endy*$bboxh), ($endx*$bboxw));
			$h2 = $bboxh - ($bboxh * tan($b));
			$px = $bboxh + ($h2 * sin($b) * cos($b));
			$py = ($bboxh * tan($b)) + ($h2 * sin($b) * sin($b));
			$x1 = $px / $bboxh;
			$y1 = $py / $bboxh;
			if ($angle <=90) { $coords[2] = $x1; $coords[3] = $y1; }
			else if ($angle <=180) { $coords[2] = 1-$x1; $coords[3] = $y1; }
			else if ($angle <=270) { $coords[2] = 1-$x1; $coords[3] = 1-$y1; }
			else { $coords[2] = $x1; $coords[3] = 1-$y1; }
		  }
		}
		// -moz If the first parameter to the gradient function is only a <point>, the gradient axis starts from the specified point, 
		// and ends at the point you would get if you rotated the starting point by 180 degrees about the center of the box that the 
		// gradient is to be applied to.
		// NO angle and NO end points (Start points defined)
		else if ((!isset($angle) || $angle===false) && $coords[0]!==false && $coords[1]!==false) { 	// should have start and end defined
		  $coords[2] = 1-$coords[0]; $coords[3] = 1-$coords[1];
		  $angle = rad2deg(atan2($coords[3]-$coords[1],$coords[2]-$coords[0]));
		  if ($angle < 0) { $angle += 360; }
		  else if ($angle > 360) { $angle -= 360; }
		  if ($angle!=0 && $angle!=360 && $angle!=90 && $angle!=180 && $angle!=270) { 
		    if ($w >= $h) {
			$coords[1] *= $h/$w ;
			$coords[3] *= $h/$w ;
			$usew = $useh = $bboxw;
			$usey -= ($w-$h);
		    }
		    else {
			$coords[0] *= $w/$h ;
			$coords[2] *= $w/$h ;
			$usew = $useh = $bboxh;
		    }
		  }
		}

		// -moz If neither a <point> or <angle> is specified, i.e. the entire function consists of only <stop> values, the gradient 
		// axis starts from the top of the box and runs vertically downwards, ending at the bottom of the box.
		else {	// default values T2B
			// All values are set in parseMozGradient - so won't appear here
			$coords = array(0,0,1,0);	// default for original linear gradient (L2R)
		}
		$s = ' q';
		$s .= sprintf(' %.3F %.3F %.3F %.3F re W n', $x*_MPDFK, ($this->mpdf->h-$y)*_MPDFK, $w*_MPDFK, -$h*_MPDFK)."\n";
		$s .= sprintf(' %.3F 0 0 %.3F %.3F %.3F cm', $usew*_MPDFK, $useh*_MPDFK, $usex*_MPDFK, ($this->mpdf->h-($usey+$useh))*_MPDFK)."\n";
	}

	// RADIAL
	else if ($type == 3) { 
		$radius = (isset($coords[4]) ? $coords[4] : false);
		$angle = (isset($coords[5]) ? $coords[5] : false);	// ?? no effect
		$shape = (isset($coords[6]) ? $coords[6] : false);
		$size = (isset($coords[7]) ? $coords[7] : false);
		$repeat = (isset($coords[8]) ? $coords[8] : false);
		// ALL POINTS AND RADIUS SET (default for custom mPDF radial gradient) - no -moz
		if ($coords[0]!==false && $coords[1]!==false && $coords[2]!==false && $coords[3]!==false && $coords[4]!==false) {
			// do nothing - coords used as they are
		}
		// If a <point> is defined
		else if ($shape!==false && $size!==false) {
		   if ($coords[2]==false) { $coords[2] = $coords[0]; }
		   if ($coords[3]==false) { $coords[3] = $coords[1]; }
		   // ELLIPSE
		   if ($shape=='ellipse') {
			$corner1 = sqrt(pow($coords[0],2) + pow($coords[1],2));
			$corner2 = sqrt(pow($coords[0],2) + pow((1-$coords[1]),2));
			$corner3 = sqrt(pow((1-$coords[0]),2) + pow($coords[1],2));
			$corner4 = sqrt(pow((1-$coords[0]),2) + pow((1-$coords[1]),2));
			if ($size=='closest-side') { $radius = min($coords[0], $coords[1], (1-$coords[0]), (1-$coords[1])); }
			else if ($size=='closest-corner') { $radius = min($corner1, $corner2, $corner3, $corner4); }
			else if ($size=='farthest-side') { $radius = max($coords[0], $coords[1], (1-$coords[0]), (1-$coords[1])); }
			else { $radius = max($corner1, $corner2, $corner3, $corner4); }	// farthest corner (default)
		   }
		   // CIRCLE
		   else if ($shape=='circle') {
		    if ($w >= $h) {
			$coords[1] = $coords[3] = ($coords[1] * $h/$w) ;
			$corner1 = sqrt(pow($coords[0],2) + pow($coords[1],2));
			$corner2 = sqrt(pow($coords[0],2) + pow((($h/$w)-$coords[1]),2));
			$corner3 = sqrt(pow((1-$coords[0]),2) + pow($coords[1],2));
			$corner4 = sqrt(pow((1-$coords[0]),2) + pow((($h/$w)-$coords[1]),2));
			if ($size=='closest-side') { $radius = min($coords[0], $coords[1], (1-$coords[0]), (($h/$w)-$coords[1])); }
			else if ($size=='closest-corner') { $radius = min($corner1, $corner2, $corner3, $corner4); }
			else if ($size=='farthest-side') { $radius = max($coords[0], $coords[1], (1-$coords[0]), (($h/$w)-$coords[1])); }
			else if ($size=='farthest-corner') { $radius = max($corner1, $corner2, $corner3, $corner4); }	// farthest corner (default)
			$usew = $useh = $bboxw;
			$usey -= ($w-$h);
		    }
		    else {
			$coords[0] = $coords[2] = ($coords[0] * $w/$h) ;
			$corner1 = sqrt(pow($coords[0],2) + pow($coords[1],2));
			$corner2 = sqrt(pow($coords[0],2) + pow((1-$coords[1]),2));
			$corner3 = sqrt(pow((($w/$h)-$coords[0]),2) + pow($coords[1],2));
			$corner4 = sqrt(pow((($w/$h)-$coords[0]),2) + pow((1-$coords[1]),2));
			if ($size=='closest-side') { $radius = min($coords[0], $coords[1], (($w/$h)-$coords[0]), (1-$coords[1])); }
			else if ($size=='closest-corner') { $radius = min($corner1, $corner2, $corner3, $corner4); }
			else if ($size=='farthest-side') { $radius = max($coords[0], $coords[1], (($w/$h)-$coords[0]), (1-$coords[1])); }
			else if ($size=='farthest-corner') { $radius = max($corner1, $corner2, $corner3, $corner4); }	// farthest corner (default)
			$usew = $useh = $bboxh;
		    }
		   }
		   if ($radius==0) { $radius=0.001; }	// to prevent error
		   $coords[4] = $radius; 
		}

		// -moz If entire function consists of only <stop> values
		else {	// default values 
			// All values are set in parseMozGradient - so won't appear here
			$coords = array(0.5,0.5,0.5,0.5);	// default for radial gradient (centred)
		}
		$s = ' q';
		$s .= sprintf(' %.3F %.3F %.3F %.3F re W n', $x*_MPDFK, ($this->mpdf->h-$y)*_MPDFK, $w*_MPDFK, -$h*_MPDFK)."\n";
		$s .= sprintf(' %.3F 0 0 %.3F %.3F %.3F cm', $usew*_MPDFK, $useh*_MPDFK, $usex*_MPDFK, ($this->mpdf->h-($usey+$useh))*_MPDFK)."\n";
	}

	$n = count($this->mpdf->gradients) + 1;
	$this->mpdf->gradients[$n]['type'] = $type;
	$this->mpdf->gradients[$n]['colorspace'] = $colorspace;
	$trans = false;
	$this->mpdf->gradients[$n]['is_mask'] = $is_mask;
	if ($is_mask) { $trans = true; }
	if (count($stops) == 1) { $stops[1] = $stops[0]; }
	if (!isset($stops[0]['offset'])) { $stops[0]['offset'] = 0; }
	if (!isset($stops[(count($stops)-1)]['offset'])) { $stops[(count($stops)-1)]['offset'] = 1; }

	// Fix stop-offsets set as absolute lengths
	if ($type==2) {
		$axisx = ($coords[2]-$coords[0])*$usew;
		$axisy = ($coords[3]-$coords[1])*$useh;
		$axis_length = sqrt(pow($axisx,2) + pow($axisy,2));
	}
	else { $axis_length = $coords[4]*$usew; }	// Absolute lengths are meaningless for an ellipse - Firefox uses Width as reference

	for($i=0;$i<count($stops);$i++) {
	  if (isset($stops[$i]['offset']) && preg_match('/([0-9.]+(px|em|ex|pc|pt|cm|mm|in))/i',$stops[$i]['offset'],$m)) { 
		$tmp = $this->mpdf->ConvertSize($m[1],$this->mpdf->w,$this->mpdf->FontSize,false);
		$stops[$i]['offset'] = $tmp/$axis_length;
	  }
	}


	if (isset($stops[0]['offset']) && $stops[0]['offset']>0) { 
		$firststop = $stops[0]; 
		$firststop['offset'] = 0;
		array_unshift($stops, $firststop); 
	}
	if (!$repeat && isset($stops[(count($stops)-1)]['offset']) && $stops[(count($stops)-1)]['offset']<1) {
		$endstop = $stops[(count($stops)-1)]; 
		$endstop['offset'] = 1;
		$stops[] = $endstop; 
	}
	if ($stops[0]['offset'] > $stops[(count($stops)-1)]['