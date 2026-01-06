<?php
//	svg class modified for mPDF version 6.0 by Ian Back: based on -
//	svg2pdf fpdf class
//	sylvain briand (syb@godisaduck.com), modified by rick trevino (rtrevino1@yahoo.com)
//	http://www.godisaduck.com/svg2pdf_with_fpdf
//	http://rhodopsin.blogspot.com
//	
//	cette class etendue est open source, toute modification devra cependant etre repertoriée~


// If you wish to use Automatic Font selection within SVG's. change this definition to true.
// This selects different fonts for different scripts used in text.
// This can be enabled/disabled independently of the use of Automatic Font selection within mPDF generally.
// Choice of font is determined by the config_script2lang.php and config_lang2fonts.php files, the same as for mPDF generally.
if (!defined("_SVG_AUTOFONT")) { define("_SVG_AUTOFONT", false); }

// Enable a limited use of classes within SVG <text> elements by setting this to true.
// This allows recognition of a "class" attribute on a <text> element.
// The CSS style for that class should be outside the SVG, and cannot use any other selectors (i.e. only .class {} can be defined)
// <style> definitions within the SVG code will be recognised if the SVG is included as an inline item within the HTML code passed to mPDF.
// The style property should be pertinent to SVG e.g. use fill:red rather than color:red
// Only the properties currently supported for SVG text can be specified:
// fill, fill-opacity, stroke, stroke-opacity, stroke-linecap, stroke-linejoin, stroke-width, stroke-dasharray, stroke-dashoffset
// font-family, font-size, font-weight, font-variant, font-style, opacity, text-anchor
if (!defined("_SVG_CLASSES")) { define("_SVG_CLASSES", false); }




// NB UNITS - Works in pixels as main units - converting to PDF units when outputing to PDF string
// and on returning size

class SVG {

	var $svg_font;		//	array - holds content of SVG fonts defined in image	// mPDF 6
	var $svg_gradient;	//	array - contient les infos sur les gradient fill du svg classé par id du svg
	var $svg_shadinglist;	//	array - contient les ids des objet shading
	var $svg_info;		//	array contenant les infos du svg voulue par l'utilisateur
	var $svg_attribs;		//	array - holds all attributes of root <svg> tag
	var $svg_style;		//	array contenant les style de groupes du svg
	var $svg_string;		//	String contenant le tracage du svg en lui même.
	var $txt_data;		//    array - holds string info to write txt to image
	var $txt_style;		// 	array - current text style
	var $mpdf_ref;
	var $xbase;	
	var $ybase;
	var $svg_error;
	var $subPathInit;
	var $spxstart;
	var $spystart;
	var $kp;		// convert pixels to PDF units
	var $pathBBox;

	var $script2lang;
	var $viet;
	var $pashto;
	var $urdu;
	var $persian;
	var $sindhi;

	var $textlength;		// mPDF 5.7.4
	var $texttotallength;	// mPDF 5.7.4
	var $textoutput;		// mPDF 5.7.4
	var $textanchor;		// mPDF 5.7.4
	var $textXorigin;		// mPDF 5.7.4
	var $textYorigin;		// mPDF 5.7.4
	var $textjuststarted;	// mPDF 5.7.4
	var $intext;		// mPDF 5.7.4

	function SVG(&$mpdf){
		$this->svg_font = array();	// mPDF 6
		$this->svg_gradient = array();
		$this->svg_shadinglist = array();
		$this->txt_data = array();
		$this->svg_string = '';
		$this->svg_info = array();
		$this->svg_attribs = array();
		$this->xbase = 0;
		$this->ybase = 0;
		$this->svg_error = false;
		$this->subPathInit = false;
		$this->dashesUsed = false;
		$this->mpdf_ref =& $mpdf;

		$this->textlength = 0;		// mPDF 5.7.4
		$this->texttotallength = 0;	// mPDF 5.7.4
		$this->textoutput = '';		// mPDF 5.7.4
		$this->textanchor = 'start';	// mPDF 5.7.4
		$this->textXorigin = 0;		// mPDF 5.7.4
		$this->textYorigin = 0;		// mPDF 5.7.4
		$this->textjuststarted = false;	// mPDF 5.7.4
		$this->intext = false;			// mPDF 5.7.4

		$this->kp = 72 / $mpdf->img_dpi;	// constant To convert pixels to pts/PDF units
		$this->kf = 1;				// constant To convert font size if re-mapped
		$this->pathBBox = array();

		$this->svg_style = array(
			array(
			'fill'		=> 'black',
			'fill-opacity'	=> 1,				//	remplissage opaque par defaut
			'fill-rule'		=> 'nonzero',		//	mode de remplissage par defaut
			'stroke'		=> 'none',			//	pas de trait par defaut
			'stroke-linecap'	=> 'butt',			//	style de langle par defaut
			'stroke-linejoin'	=> 'miter',
			'stroke-miterlimit' => 4,			//	limite de langle par defaut
			'stroke-opacity'	=> 1,				//	trait opaque par defaut
			'stroke-width'	=> 1,	
			'stroke-dasharray' => 0,
			'stroke-dashoffset' => 0,
			'color' => ''
			)
		);

		$this->txt_style = array(
			array(
			'fill'		=> 'black',		//	pas de remplissage par defaut
			'font-family' 	=> $mpdf->default_font,
			'font-size'		=> $mpdf->default_font_size,		// 	****** this is pts
			'font-weight'	=> 'normal',	//	normal | bold
			'font-style'	=> 'normal',	//	italic | normal
			'text-anchor'	=> 'start',		// alignment: start, middle, end
			'fill-opacity'	=> 1,				//	remplissage opaque par defaut
			'fill-rule'		=> 'nonzero',		//	mode de remplissage par defaut
			'stroke'		=> 'none',			//	pas de trait par defaut
			'stroke-opacity'	=> 1,				//	trait opaque par defaut
			'stroke-width'	=> 1,
			'color' => ''
			)
		);



	}

	// mPDF 5.7.4 Embedded image
	function svgImage($attribs) {
		// x and y are coordinates
		$x = (isset($attribs['x']) ? $attribs['x'] : 0);
		$y = (isset($attribs['y']) ? $attribs['y'] : 0);
		// preserveAspectRatio
		$par = (isset($attribs['preserveAspectRatio']) ? $attribs['preserveAspectRatio'] : 'xMidYMid meet');
		// width and height are <lengths> - Required attributes
		$wset = (isset($attribs['width']) ? $attribs['width'] : 0);
		$hset = (isset($attribs['height']) ? $attribs['height'] : 0);
		$w = $this->mpdf_ref->ConvertSize($wset,$this->svg_info['w']*(25.4/$this->mpdf_ref->dpi),$this->mpdf_ref->FontSize,false);
		$h = $this->mpdf_ref->ConvertSize($hset,$this->svg_info['h']*(25.4/$this->mpdf_ref->dpi),$this->mpdf_ref->FontSize,false);
		if ($w==0 || $h==0) { return; }
		// Convert to pixels = SVG units
		$w *= 1/(25.4/$this->mpdf_ref->dpi);
		$h *= 1/(25.4/$this->mpdf_ref->dpi);

     		$srcpath = $attribs['xlink:href'];
		$orig_srcpath = '';
		if (trim($srcpath) != '' && substr($srcpath,0,4)=='var:') { 
			$orig_srcpath = $srcpath;
			$this->mpdf_ref->GetFullPath($srcpath); 
		}

		// Image file (does not allow vector images i.e. WMF/SVG)
		// mPDF 6 Added $this->mpdf_ref->interpolateImages
		$info = $this->mpdf_ref->_getImage($srcpath, true, false, $orig_srcpath, $this->mpdf_ref->interpolateImages);
		if(!$info) return;

		// x,y,w,h define the reference rectangle
		$img_h = $h;
		$img_w = $w;
		$img_x = $x;
		$img_y = $y;
		$meetOrSlice = 'meet';

		// preserveAspectRatio
		$ar = preg_split('/\s+/', strtolower($par));
		if ($ar[0]!='none') {	// If "none" need to do nothing
			//  Force uniform scaling
			if (isset($ar[1]) && $ar[1]=='slice') { $meetOrSlice = 'slice'; }
			else { $meetOrSlice = 'meet'; }
			if ($info['h']/$info['w'] > $h/$w) {
				if ($meetOrSlice == 'meet') { // the entire viewBox is visible within the viewport
					$img_w = $img_h * $info['w']/$info['h'];
				}
				else { // the entire viewport is covered by the viewBox
					$img_h = $img_w * $info['h']/$info['w'];
				}
			}
			else if ($info['h']/$info['w'] < $h/$w) {
				if ($meetOrSlice == 'meet') { // the entire viewBox is visible within the viewport
					$img_h = $img_w * $info['h']/$info['w'];
				}
				else { // the entire viewport is covered by the viewBox
					$img_w = $img_h * $info['w']/$info['h'];
				}
			}
			if ($ar[0]=='xminymin') {
				// do nothing to x
				// do nothing to y
			}
			else if ($ar[0]=='xmidymin') {
				$img_x += $w/2 - $img_w/2;	// xMid
				// do nothing to y
			}
			else if ($ar[0]=='xmaxymin') {
				$img_x += $w - $img_w;	// xMax
				// do nothing to y
			}
			else if ($ar[0]=='xminymid') {
				// do nothing to x
				$img_y += $h/2 - $img_h/2;	// yMid
			}
			else if ($ar[0]=='xmaxymid') {
				$img_x += $w - $img_w;	// xMax
				$img_y += $h/2 - $img_h/2;	// yMid
			}
			else if ($ar[0]=='xminymax') {
				// do nothing to x
				$img_y += $h - $img_h;	// yMax
			}
			else if ($ar[0]=='xmidymax') {
				$img_x += $w/2 - $img_w/2;	// xMid
				$img_y += $h - $img_h;	// yMax
			}
			else if ($ar[0]=='xmaxymax') {
				$img_x += $w - $img_w;	// xMax
				$img_y += $h - $img_h;	// yMax
			}
			else  {	// xMidYMid (the default)
				$img_x += $w/2 - $img_w/2;	// xMid
				$img_y += $h/2 - $img_h/2;	// yMid
			}
		}

		// Output
		if ($meetOrSlice == 'slice') { // need to add a clipping path to reference rectangle
			$s = ' q 0 w ';	// Line width=0
			$s .= sprintf('%.3F %.3F m ', ($x)*$this->kp, (-($y+$h))*$this->kp);	// start point TL before the arc
			$s .= sprintf('%.3F %.3F l ', ($x)*$this->kp, (-($y))*$this->kp);	// line to BL
			$s .= sprintf('%.3F %.3F l ', ($x+$w)*$this->kp, (-($y))*$this->kp);	// line to BR
			$s .= sprintf('%.3F %.3F l ', ($x+$w)*$this->kp, (-($y+$h))*$this->kp);	// line to TR
			$s .= sprintf('%.3F %.3F l ', ($x)*$this->kp, (-($y+$h))*$this->kp);	// line to TL
			$s .= ' W n ';	// Ends path no-op & Sets the clipping path
			$this->svgWriteString($s);
		}

		$outstring = sprintf(" q %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q ",$img_w*$this->kp, $img_h*$this->kp, $img_x*$this->kp, -($img_y+$img_h)*$this->kp, $info['i'] );
		$this->svgWriteString($outstring);

		if ($meetOrSlice == 'slice') { // need to end clipping path
			$this->svgWriteString(' Q ');
		}
	}


	function svgGradient($gradient_info, $attribs, $element){
		$n = count($this->mpdf_ref->gradients)+1;

		// Get bounding dimensions of element
		$w = 100;
		$h = 100;
		$x_offset = 0;
		$y_offset = 0;
		if ($element=='rect') {
			$w = $attribs['width'];
			$h = $attribs['height'];
			$x_offset = $attribs['x'];
			$y_offset = $attribs['y'];
		}
		else if ($element=='ellipse') {
			$w = $attribs['rx']*2;
			$h = $attribs['ry']*2;
			$x_offset = $attribs['cx']-$attribs['rx'];
			$y_offset = $attribs['cy']-$attribs['ry'];
		}
		else if ($element=='circle') {
			$w = $attribs['r']*2;
			$h = $attribs['r']*2;
			$x_offset = $attribs['cx']-$attribs['r'];
			$y_offset = $attribs['cy']-$attribs['r'];
		}
		else if ($element=='polygon') {
			$pts = preg_split('/[ ,]+/', trim($attribs['points']));
			$maxr=$maxb=0;
			$minl=$mint=999999;
			for ($i=0;$i<count($pts); $i++) {
				if ($i % 2 == 0) {	// x values
					$minl = min($minl,$pts[$i]);
					$maxr = max($maxr,$pts[$i]);
				}
				else {	// y values
					$mint = min($mint,$pts[$i]);
					$maxb = max($maxb,$pts[$i]);
				}
			}
			$w = $maxr-$minl;
			$h = $maxb-$mint;
			$x_offset = $minl;
			$y_offset = $mint;
		}
		else if ($element=='path') {
		  if (is_array($this->pathBBox) && $this->pathBBox[2]>0) {
			$w = $this->pathBBox[2];
			$h = $this->pathBBox[3];
			$x_offset = $this->pathBBox[0];
			$y_offset = $this->pathBBox[1];
		  }
		  else {
			preg_match_all('/([a-z]|[A-Z])([ ,\-.\d]+)*/', $attribs['d'], $commands, PREG_SET_ORDER);
			$maxr=$maxb=0;
			$minl=$mint=999999;
			foreach($commands as $c){
				if(count($c)==3){
					list($tmp, $cmd, $arg) = $c;
					if ($cmd=='M' || $cmd=='L' || $cmd=='C' || $cmd=='S' || $cmd=='Q' || $cmd=='T') {
						$pts = preg_split('/[ ,]+/', trim($arg));
						for ($i=0;$i<count($pts); $i++) {
							if ($i % 2 == 0) {	// x values
								$minl = min($minl,$pts[$i]);
								$maxr = max($maxr,$pts[$i]);
							}
							else {	// y values
								$mint = min($mint,$pts[$i]);
								$maxb = max($maxb,$pts[$i]);
							}
						}
					}
					if ($cmd=='H') { // sets new x
						$minl = min($minl,$arg);
						$maxr = max($maxr,$arg);
					}
					if ($cmd=='V') { // sets new y
						$mint = min($mint,$arg);
						$maxb = max($maxb,$arg);
					}
				}
			}
			$w = $maxr-$minl;
			$h = $maxb-$mint;
			$x_offset = $minl;
			$y_offset = $mint;
		  }
		}
		if (!$w || $w==-999999) { $w = 100; }
		if (!$h || $h==-999999) { $h = 100; }
		if ($x_offset==999999) { $x_offset = 0; }
		if ($y_offset==999999) { $y_offset = 0; }

		// TRANSFORMATIONS
		$transformations = '';
		if (isset($gradient_info['transform'])){
			preg_match_all('/(matrix|translate|scale|rotate|skewX|skewY)\((.*?)\)/is',$gradient_info['transform'],$m);
			if (count($m[0])) {
				for($i=0; $i<count($m[0]); $i++) {
					$c = strtolower($m[1][$i]);
					$v = trim($m[2][$i]);
					$vv = preg_split('/[ ,]+/',$v);
					if ($c=='matrix' && count($vv)==6) {
						// Note angle of rotation is reversed (from SVG to PDF), so vv[1] and vv[2] are negated
						// cf svgDefineStyle()
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F %.3F %.3F cm ', $vv[0], -$vv[1], -$vv[2], $vv[3], $vv[4]*$this->kp, -$vv[5]*$this->kp);	
					}
					else if ($c=='translate' && count($vv)) {
						$tm[4] = $vv[0];
						if (count($vv)==2) { $t_y = -$vv[1]; }
						else { $t_y = 0; }
						$tm[5] = $t_y;
						$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $tm[4]*$this->kp, $tm[5]*$this->kp);
					}
					else if ($c=='scale' && count($vv)) {
						if (count($vv)==2) { $s_y = $vv[1]; }
						else { $s_y = $vv[0]; }
						$tm[0] = $vv[0];
						$tm[3] = $s_y;
						$transformations .= sprintf(' %.3F 0 0 %.3F 0 0 cm ', $tm[0], $tm[3]);
					}
					else if ($c=='rotate' && count($vv)) {
						$tm[0] = cos(deg2rad(-$vv[0]));
						$tm[1] = sin(deg2rad(-$vv[0]));
						$tm[2] = -$tm[1];
						$tm[3] = $tm[0];
						if (count($vv)==3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $vv[1]*$this->kp, -$vv[2]*$this->kp);
						}
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F 0 0 cm ', $tm[0], $tm[1], $tm[2], $tm[3]);
						if (count($vv)==3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', -$vv[1]*$this->kp, $vv[2]*$this->kp);
						}
					}
					else if ($c=='skewx' && count($vv)) {
						$tm[2] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 0 %.3F 1 0 0 cm ', $tm[2]);
					}
					else if ($c=='skewy' && count($vv)) {
						$tm[1] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 %.3F 0 1 0 0 cm ', $tm[1]);
					}

				}
			}
		}


		$return = "";

		if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
			if ($transformations) { $return .= $transformations; }
		}
		$spread = 'P';  // pad
		if (isset($gradient_info['spread'])) {
			if (strtolower($gradient_info['spread'])=='reflect') { $spread = 'F'; } // reflect
			else if (strtolower($gradient_info['spread'])=='repeat') { $spread = 'R'; } // repeat
		}	


		for ($i=0; $i<(count($gradient_info['color'])); $i++) {
			if (stristr($gradient_info['color'][$i]['offset'], '%')!== false) { $gradient_info['color'][$i]['offset'] = ($gradient_info['color'][$i]['offset']+0)/100; }
			if (isset($gradient_info['color'][($i+1)]['offset']) && stristr($gradient_info['color'][($i+1)]['offset'], '%')!== false) { $gradient_info['color'][($i+1)]['offset'] = ($gradient_info['color'][($i+1)]['offset']+0)/100; }
			if ($gradient_info['color'][$i]['offset']<0) { $gradient_info['color'][$i]['offset'] = 0; }
			if ($gradient_info['color'][$i]['offset']>1) { $gradient_info['color'][$i]['offset'] = 1; }
			if ($i>0) {
				if ($gradient_info['color'][$i]['offset']<$gradient_info['color'][($i-1)]['offset']) { 
					$gradient_info['color'][$i]['offset']=$gradient_info['color'][($i-1)]['offset']; 
				}
			}
		}

		if (isset($gradient_info['color'][0]['offset']) && $gradient_info['color'][0]['offset']>0) { 
			array_unshift($gradient_info['color'], $gradient_info['color'][0]);
			$gradient_info['color'][0]['offset'] = 0; 
		}
		$ns = count($gradient_info['color']);
		if (isset($gradient_info['color'][($ns-1)]['offset']) && $gradient_info['color'][($ns-1)]['offset']<1) { 
			$gradient_info['color'][] = $gradient_info['color'][($ns-1)];
			$gradient_info['color'][($ns)]['offset'] = 1; 
		}
		$ns = count($gradient_info['color']);




		if ($gradient_info['type'] == 'linear'){
			// mPDF 4.4.003
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
				if (isset($gradient_info['info']['x1'])) { $gradient_info['info']['x1'] = ($gradient_info['info']['x1']-$x_offset) / $w; }
				if (isset($gradient_info['info']['y1'])) { $gradient_info['info']['y1'] = ($gradient_info['info']['y1']-$y_offset) / $h; }
				if (isset($gradient_info['info']['x2'])) { $gradient_info['info']['x2'] = ($gradient_info['info']['x2']-$x_offset) / $w; }
				if (isset($gradient_info['info']['y2'])) { $gradient_info['info']['y2'] = ($gradient_info['info']['y2']-$y_offset) / $h; }
			}
			if (isset($gradient_info['info']['x1'])) { $x1 = $gradient_info['info']['x1']; }
			else { $x1 = 0; }
			if (isset($gradient_info['info']['y1'])) { $y1 = $gradient_info['info']['y1']; }
			else { $y1 = 0; }
			if (isset($gradient_info['info']['x2'])) { $x2 = $gradient_info['info']['x2']; }
			else { $x2 = 1; }
			if (isset($gradient_info['info']['y2'])) { $y2 = $gradient_info['info']['y2']; }
			else { $y2 = 0; }	// mPDF 6

			if (stristr($x1, '%')!== false) { $x1 = ($x1+0)/100; }
			if (stristr($x2, '%')!== false) { $x2 = ($x2+0)/100; }
			if (stristr($y1, '%')!== false) { $y1 = ($y1+0)/100; }
			if (stristr($y2, '%')!== false) { $y2 = ($y2+0)/100; }

			// mPDF 5.0.042
			$bboxw = $w;
			$bboxh = $h;
			$usex = $x_offset;
			$usey = $y_offset;
			$usew = $bboxw;
			$useh = $bboxh;
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
				$angle = rad2deg(atan2(($gradient_info['info']['y2']-$gradient_info['info']['y1']), ($gradient_info['info']['x2']-$gradient_info['info']['x1'])));
				if ($angle < 0) { $angle += 360; }
				else if ($angle > 360) { $angle -= 360; }
				if ($angle!=0 && $angle!=360 && $angle!=90 && $angle!=180 && $angle!=270) { 
				    if ($w >= $h) {
					$y1 *= $h/$w ;
					$y2 *= $h/$w ;
					$usew = $useh = $bboxw;
				    }
				    else {
					$x1 *= $w/$h ;
					$x2 *= $w/$h ;
					$usew = $useh = $bboxh;
				    }
				}
			}
			$a = $usew;		// width
			$d = -$useh;	// height
			$e = $usex;		// x- offset
			$f = -$usey;	// -y-offset

			$return .= sprintf('%.3F 0 0 %.3F %.3F %.3F cm ', $a*$this->kp, $d*$this->kp, $e*$this->kp, $f*$this->kp);

			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='objectboundingbox') {
				if ($transformations) { $return .= $transformations; }
			}

			$trans = false;

			if ($spread=='R' || $spread=='F') {	// Repeat  /  Reflect
				$offs = array();
				for($i=0;$i<$ns;$i++) {
					$offs[$i] = $gradient_info['color'][$i]['offset'];
				}
				$gp = 0;
				$inside=true;
				while($inside) {
				   $gp++;
				   for($i=0;$i<$ns;$i++) {
					if ($spread=='F' && ($gp % 2) == 1) {	// Reflect
						$gradient_info['color'][(($ns*$gp)+$i)] = $gradient_info['color'][(($ns*($gp-1))+($ns-$i-1))];
						$tmp = $gp+(1-$offs[($ns-$i-1)]) ;
						$gradient_info['color'][(($ns*$gp)+$i)]['offset'] = $tmp; 
					}
					else {	// Reflect
						$gradient_info['color'][(($ns*$gp)+$i)] = $gradient_info['color'][$i];
						$tmp = $gp+$offs[$i] ;
						$gradient_info['color'][(($ns*$gp)+$i)]['offset'] = $tmp; 
					}
					// IF STILL INSIDE BOX OR STILL VALID 
					// Point on axis to test
					$px1 = $x1 + ($x2-$x1)*$tmp;
					$py1 = $y1 + ($y2-$y1)*$tmp;
					// Get perpendicular axis
					$alpha = atan2($y2-$y1, $x2-$x1);
					$alpha += M_PI/2;	// rotate 90 degrees
					// Get arbitrary point to define line perpendicular to axis
					$px2 = $px1+cos($alpha);
					$py2 = $py1+sin($alpha);

					$res1 = _testIntersect($px1, $py1, $px2, $py2, 0, 0, 0, 1);	// $x=0 vert axis
					$res2 = _testIntersect($px1, $py1, $px2, $py2, 1, 0, 1, 1);	// $x=1 vert axis
					$res3 = _testIntersect($px1, $py1, $px2, $py2, 0, 0, 1, 0);	// $y=0 horiz axis
					$res4 = _testIntersect($px1, $py1, $px2, $py2, 0, 1, 1, 1);	// $y=1 horiz axis
					if (!$res1 && !$res2 && !$res3 && !$res4) { $inside = false; }
				   }
				}

				$inside=true;
				$gp = 0;
				while($inside) {
				   $gp++;
				   $newarr = array();
				   for($i=0;$i<$ns;$i++) {
					if ($spread=='F') {	// Reflect
					    $newarr[$i] = $gradient_info['color'][($ns-$i-1)];
					    if (($gp % 2) == 1) {
						$tmp = -$gp+(1-$offs[($ns-$i-1)]);
							$newarr[$i]['offset'] = $tmp; 
					   }
					   else {
						$tmp = -$gp+$offs[$i];
						$newarr[$i]['offset'] = $tmp; 
					   }
					}
					else {	// Reflect
						$newarr[$i] = $gradient_info['color'][$i];
						$tmp = -$gp+$offs[$i];
						$newarr[$i]['offset'] = $tmp; 
					}

					// IF STILL INSIDE BOX OR STILL VALID 
					// Point on axis to test
					$px1 = $x1 + ($x2-$x1)*$tmp;
					$py1 = $y1 + ($y2-$y1)*$tmp;
					// Get perpendicular axis
					$alpha = atan2($y2-$y1, $x2-$x1);
					$alpha += M_PI/2;	// rotate 90 degrees
					// Get arbitrary point to define line perpendicular to axis
					$px2 = $px1+cos($alpha);
					$py2 = $py1+sin($alpha);

					$res1 = _testIntersect($px1, $py1, $px2, $py2, 0, 0, 0, 1);	// $x=0 vert axis
					$res2 = _testIntersect($px1, $py1, $px2, $py2, 1, 0, 1, 1);	// $x=1 vert axis
					$res3 = _testIntersect($px1, $py1, $px2, $py2, 0, 0, 1, 0);	// $y=0 horiz axis
					$res4 = _testIntersect($px1, $py1, $px2, $py2, 0, 1, 1, 1);	// $y=1 horiz axis
					if (!$res1 && !$res2 && !$res3 && !$res4) { $inside = false; }
				   }
				   for($i=($ns-1);$i>=0;$i--) { 
					if (isset($newarr[$i]['offset'])) array_unshift($gradient_info['color'], $newarr[$i]); 
				   }
				}
			}

			// Gradient STOPs
			$stops = count($gradient_info['color']);
			if ($stops < 2) { return ''; }

			$range = $gradient_info['color'][count($gradient_info['color'])-1]['offset']-$gradient_info['color'][0]['offset'];
			$min = $gradient_info['color'][0]['offset'];

			for ($i=0; $i<($stops); $i++) {
				if (!$gradient_info['color'][$i]['color']) { 
					if ($gradient_info['colorspace']=='RGB') $gradient_info['color'][$i]['color'] = '0 0 0'; 
					else if ($gradient_info['colorspace']=='Gray') $gradient_info['color'][$i]['color'] = '0'; 
					else if ($gradient_info['colorspace']=='CMYK') $gradient_info['color'][$i]['color'] = '1 1 1 1'; 
				}
				$offset = ($gradient_info['color'][$i]['offset'] - $min)/$range;
				$this->mpdf_ref->gradients[$n]['stops'][] = array(
					'col' => $gradient_info['color'][$i]['color'],
					'opacity' => $gradient_info['color'][$i]['opacity'],
					'offset' => $offset);
				if ($gradient_info['color'][$i]['opacity']<1) { $trans = true; }
			}
			$grx1 = $x1 + ($x2-$x1)*$gradient_info['color'][0]['offset'];
			$gry1 = $y1 + ($y2-$y1)*$gradient_info['color'][0]['offset'];
			$grx2 = $x1 + ($x2-$x1)*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];
			$gry2 = $y1 + ($y2-$y1)*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];

			$this->mpdf_ref->gradients[$n]['coords']=array($grx1, $gry1, $grx2, $gry2);

			$this->mpdf_ref->gradients[$n]['colorspace'] = $gradient_info['colorspace'];

			$this->mpdf_ref->gradients[$n]['type'] = 2;
			$this->mpdf_ref->gradients[$n]['fo'] = true;

			$this->mpdf_ref->gradients[$n]['extend']=array('true','true');
			if ($trans) { 
				$this->mpdf_ref->gradients[$n]['trans'] = true;	
				$return .= ' /TGS'.($n).' gs ';
			}
			$return .= ' /Sh'.($n).' sh ';
			$return .= " Q\n";
		}
		else if ($gradient_info['type'] == 'radial'){
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
				if ($w > $h) { $h = $w; }
				else { $w = $h; }
				if (isset($gradient_info['info']['x0'])) { $gradient_info['info']['x0'] = ($gradient_info['info']['x0']-$x_offset) / $w; }
				if (isset($gradient_info['info']['y0'])) { $gradient_info['info']['y0'] = ($gradient_info['info']['y0']-$y_offset) / $h; }
				if (isset($gradient_info['info']['x1'])) { $gradient_info['info']['x1'] = ($gradient_info['info']['x1']-$x_offset) / $w; }
				if (isset($gradient_info['info']['y1'])) { $gradient_info['info']['y1'] = ($gradient_info['info']['y1']-$y_offset) / $h; }
				if (isset($gradient_info['info']['r'])) { $gradient_info['info']['rx'] = $gradient_info['info']['r'] / $w; }
				if (isset($gradient_info['info']['r'])) { $gradient_info['info']['ry'] = $gradient_info['info']['r'] / $h; }
			}

			if (isset($gradient_info['info']['x0'])) { $x0 = $gradient_info['info']['x0']; }
			else { $x0 = 0.5; }
			if (isset($gradient_info['info']['y0'])) { $y0 = $gradient_info['info']['y0']; }
			else { $y0 = 0.5; }
			if (isset($gradient_info['info']['rx'])) { $rx = $gradient_info['info']['rx']; }
			else if (isset($gradient_info['info']['r'])) { $rx = $gradient_info['info']['r']; }
			else { $rx = 0.5; }
			if (isset($gradient_info['info']['ry'])) { $ry = $gradient_info['info']['ry']; }
			else if (isset($gradient_info['info']['r'])) { $ry = $gradient_info['info']['r']; }
			else { $ry = 0.5; }
			if (isset($gradient_info['info']['x1'])) { $x1 = $gradient_info['info']['x1']; }
			else { $x1 = $x0; }
			if (isset($gradient_info['info']['y1'])) { $y1 = $gradient_info['info']['y1']; }
			else { $y1 = $y0; }

			if (stristr($x1, '%')!== false) { $x1 = ($x1+0)/100; }
			if (stristr($x0, '%')!== false) { $x0 = ($x0+0)/100; }
			if (stristr($y1, '%')!== false) { $y1 = ($y1+0)/100; }
			if (stristr($y0, '%')!== false) { $y0 = ($y0+0)/100; }
			if (stristr($rx, '%')!== false) { $rx = ($rx+0)/100; }
			if (stristr($ry, '%')!== false) { $ry = ($ry+0)/100; }

			$bboxw = $w;
			$bboxh = $h;
			$usex = $x_offset;
			$usey = $y_offset;
			$usew = $bboxw;
			$useh = $bboxh;
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='userspaceonuse') {
				$angle = rad2deg(atan2(($gradient_info['info']['y0']-$gradient_info['info']['y1']), ($gradient_info['info']['x0']-$gradient_info['info']['x1'])));
				if ($angle < 0) { $angle += 360; }
				else if ($angle > 360) { $angle -= 360; }
				if ($angle!=0 && $angle!=360 && $angle!=90 && $angle!=180 && $angle!=270) { 
				    if ($w >= $h) {
					$y1 *= $h/$w ;
					$y0 *= $h/$w ;
					$rx *= $h/$w ;
					$ry *= $h/$w ;
					$usew = $useh = $bboxw;
				    }
				    else {
					$x1 *= $w/$h ;
					$x0 *= $w/$h ;
					$rx *= $w/$h ;
					$ry *= $w/$h ;
					$usew = $useh = $bboxh;
				    }
				}
			}
			$a = $usew;		// width
			$d = -$useh;	// height
			$e = $usex;		// x- offset
			$f = -$usey;	// -y-offset

			$r = $rx;


			$return .= sprintf('%.3F 0 0 %.3F %.3F %.3F cm ', $a*$this->kp, $d*$this->kp, $e*$this->kp, $f*$this->kp);

			// mPDF 5.0.039
			if (isset($gradient_info['units']) && strtolower($gradient_info['units'])=='objectboundingbox') {
				if ($transformations) { $return .= $transformations; }
			}

			// mPDF 5.7.4
			// x1 and y1 (fx, fy) should be inside the circle defined by x0 y0 (cx, cy)
			// "If the point defined by fx and fy lies outside the circle defined by cx, cy and r, then the user agent shall set 
			// the focal point to the intersection of the line from (cx, cy) to (fx, fy) with the circle defined by cx, cy and r."
			while (pow(($x1-$x0),2) + pow(($y1 - $y0),2) >= pow($r,2)) { 
				// Gradually move along fx,fy towards cx,cy in 100'ths until meets criteria
				$x1 -= ($x1-$x0)/100; 
				$y1 -= ($y1-$y0)/100; 
			}


			if ($spread=='R' || $spread=='F') {	// Repeat  /  Reflect
				$offs = array();
				for($i=0;$i<$ns;$i++) {
					$offs[$i] = $gradient_info['color'][$i]['offset'];
				}
				$gp = 0;
				$inside=true;
				while($inside) {
				   $gp++;
				   for($i=0;$i<$ns;$i++) {
					if ($spread=='F' && ($gp % 2) == 1) {	// Reflect
						$gradient_info['color'][(($ns*$gp)+$i)] = $gradient_info['color'][(($ns*($gp-1))+($ns-$i-1))];
						$tmp = $gp+(1-$offs[($ns-$i-1)]) ;
						$gradient_info['color'][(($ns*$gp)+$i)]['offset'] = $tmp; 
					}
					else {	// Reflect
						$gradient_info['color'][(($ns*$gp)+$i)] = $gradient_info['color'][$i];
						$tmp = $gp+$offs[$i] ;
						$gradient_info['color'][(($ns*$gp)+$i)]['offset'] = $tmp; 
					}
					// IF STILL INSIDE BOX OR STILL VALID 
					// TEST IF circle (perimeter) intersects with 
					// or is enclosed
					// Point on axis to test
					$px = $x1 + ($x0-$x1)*$tmp;
					$py = $y1 + ($y0-$y1)*$tmp;
					$pr = $r*$tmp;
					$res = _testIntersectCircle($px, $py, $pr);
					if (!$res) { $inside = false; }
				   }
				}
			}

			// Gradient STOPs
			$stops = count($gradient_info['color']);
			if ($stops < 2) { return ''; }

			$range = $gradient_info['color'][count($gradient_info['color'])-1]['offset']-$gradient_info['color'][0]['offset'];
			$min = $gradient_info['color'][0]['offset'];

			for ($i=0; $i<($stops); $i++) {
				if (!$gradient_info['color'][$i]['color']) { 
					if ($gradient_info['colorspace']=='RGB') $gradient_info['color'][$i]['color'] = '0 0 0'; 
					else if ($gradient_info['colorspace']=='Gray') $gradient_info['color'][$i]['color'] = '0'; 
					else if ($gradient_info['colorspace']=='CMYK') $gradient_info['color'][$i]['color'] = '1 1 1 1'; 
				}
				$offset = ($gradient_info['color'][$i]['offset'] - $min)/$range;
				$this->mpdf_ref->gradients[$n]['stops'][] = array(
					'col' => $gradient_info['color'][$i]['color'],
					'opacity' => $gradient_info['color'][$i]['opacity'],
					'offset' => $offset);
				if ($gradient_info['color'][$i]['opacity']<1) { $trans = true; }
			}
			$grx1 = $x1 + ($x0-$x1)*$gradient_info['color'][0]['offset'];
			$gry1 = $y1 + ($y0-$y1)*$gradient_info['color'][0]['offset'];
			$grx2 = $x1 + ($x0-$x1)*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];
			$gry2 = $y1 + ($y0-$y1)*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];
			$grir = $r*$gradient_info['color'][0]['offset'];
			$grr = $r*$gradient_info['color'][count($gradient_info['color'])-1]['offset'];

			$this->mpdf_ref->gradients[$n]['coords']=array($grx1, $gry1, $grx2, $gry2, abs($grr), abs($grir)  );

			$this->mpdf_ref->gradients[$n]['colorspace'] = $gradient_info['colorspace'];

			$this->mpdf_ref->gradients[$n]['type'] = 3;
			$this->mpdf_ref->gradients[$n]['fo'] = true;

			$this->mpdf_ref->gradients[$n]['extend']=array('true','true');
			if (isset($trans) && $trans) { 
				$this->mpdf_ref->gradients[$n]['trans'] = true;	
				$return .= ' /TGS'.($n).' gs ';
			}
			$return .= ' /Sh'.($n).' sh ';
			$return .= " Q\n";


		}

		return $return;
	}


	function svgOffset ($attribs){
		// save all <svg> tag attributes
		$this->svg_attribs = $attribs;
		if(isset($this->svg_attribs['viewBox'])) {
			$vb = preg_split('/\s+/is', trim($this->svg_attribs['viewBox']));
			if (count($vb)==4) {
				$this->svg_info['x'] = $vb[0];
				$this->svg_info['y'] = $vb[1];
				$this->svg_info['w'] = $vb[2];
				$this->svg_info['h'] = $vb[3];
//				return;
			}
		}
		$svg_w = 0;
		$svg_h = 0;
		if (isset($attribs['width']) && $attribs['width']) $svg_w = $this->mpdf_ref->ConvertSize($attribs['width']);	// mm (interprets numbers as pixels)
		if (isset($attribs['height']) && $attribs['height']) $svg_h = $this->mpdf_ref->ConvertSize($attribs['height']);	// mm

///*
		// mPDF 5.0.005
		if (isset($this->svg_info['w']) && $this->svg_info['w']) {	// if 'w' set by viewBox
			if ($svg_w) {	// if width also set, use these values to determine to set size of "pixel"
				$this->kp *= ($svg_w/0.2645) / $this->svg_info['w'];
				$this->kf = ($svg_w/0.2645) / $this->svg_info['w'];
			}
			else if ($svg_h) {
				$this->kp *= ($svg_h/0.2645) / $this->svg_info['h'];
				$this->kf = ($svg_h/0.2645) / $this->svg_info['h'];
			}
			return;
		}
//*/

		// Added to handle file without height or width specified
		if (!$svg_w && !$svg_h) { $svg_w = $svg_h = $this->mpdf_ref->blk[$this->mpdf_ref->blklvl]['inner_width'] ; }	// DEFAULT
		if (!$svg_w) { $svg_w = $svg_h; }
		if (!$svg_h) { $svg_h = $svg_w; }

		$this->svg_info['x'] = 0;
		$this->svg_info['y'] = 0;
		$this->svg_info['w'] = $svg_w/0.2645;	// mm->pixels
		$this->svg_info['h'] = $svg_h/0.2645;	// mm->pixels

	}


	//
	// check if points are within svg, if not, set to max
	function svg_overflow($x,$y)
	{
		$x2 = $x;
		$y2 = $y;
		if(isset($this->svg_attribs['overflow']))
		{
			if($this->svg_attribs['overflow'] == 'hidden')
			{
				// Not sure if this is supposed to strip off units