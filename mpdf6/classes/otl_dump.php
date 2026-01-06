<?php

/*******************************************************************************
* otl_dump class                                                             *
*******************************************************************************/

// Define the value used in the "head" table of a created TTF file
// 0x74727565 "true" for Mac
// 0x00010000 for Windows
// Either seems to work for a font embedded in a PDF file
// when read by Adobe Reader on a Windows PC(!)
if (!defined('_TTF_MAC_HEADER')) define("_TTF_MAC_HEADER", false);

// Recalculate correct metadata/profiles when making subset fonts (not SIP/SMP)
// e.g. xMin, xMax, maxNContours
if (!defined('_RECALC_PROFILE')) define("_RECALC_PROFILE", false);

// TrueType Font Glyph operators
define("GF_WORDS",(1 << 0));
define("GF_SCALE",(1 << 3));
define("GF_MORE",(1 << 5));
define("GF_XYSCALE",(1 << 6));
define("GF_TWOBYTWO",(1 << 7));

// mPDF 5.7.1
if(!function_exists('unicode_hex')){ 
	function unicode_hex($unicode_dec) {
		return (sprintf("%05s", strtoupper(dechex($unicode_dec))));
	}
}
class OTLdump {

var $GPOSFeatures;	// mPDF 5.7.1
var $GPOSLookups;		// mPDF 5.7.1
var $GPOSScriptLang;	// mPDF 5.7.1
var $ignoreStrings;	// mPDF 5.7.1
var $MarkAttachmentType; // mPDF 5.7.1
var $MarkGlyphSets;	// mPDF 7.5.1
var $GlyphClassMarks;	// mPDF 5.7.1
var $GlyphClassLigatures;	// mPDF 5.7.1
var $GlyphClassBases;	// mPDF 5.7.1
var $GlyphClassComponents;	// mPDF 5.7.1
var $GSUBScriptLang;	// mPDF 5.7.1
var $rtlPUAstr;	// mPDF 5.7.1
var $rtlPUAarr;	// mPDF 5.7.1
var $fontkey;	// mPDF 5.7.1
var $useOTL;	// mPDF 5.7.1
var $panose;
var $maxUni;
var $sFamilyClass;
var $sFamilySubClass;
var $sipset;
var $smpset;
var $_pos;
var $numTables;
var $searchRange;
var $entrySelector;
var $rangeShift;
var $tables;
var $otables;
var $filename;
var $fh;
var $glyphPos;
var $charToGlyph;
var $ascent;
var $descent;
var $name;
var $familyName;
var $styleName;
var $fullName;
var $uniqueFontID;
var $unitsPerEm;
var $bbox;
var $capHeight;
var $stemV;
var $italicAngle;
var $flags;
var $underlinePosition;
var $underlineThickness;
var $charWidths;
var $defaultWidth;
var $maxStrLenRead;
var $numTTCFonts;
var $TTCFonts;
var $maxUniChar;
var $kerninfo;

	function OTLdump(&$mpdf) {
		$this->mpdf = $mpdf;
		$this->maxStrLenRead = 200000;	// Maximum size of glyf table to read in as string (otherwise reads each glyph from file)
	}


	function getMetrics($file, $fontkey, $TTCfontID=0, $debug=false, $BMPonly=false, $kerninfo=false, $useOTL=0, $mode) {	// mPDF 5.7.1
		$this->mode = $mode;
		$this->useOTL = $useOTL;	// mPDF 5.7.1
		$this->fontkey = $fontkey;	// mPDF 5.7.1
		$this->filename = $file;
		$this->fh = fopen($file,'rb') or die('Can\'t open file ' . $file);
		$this->_pos = 0;
		$this->charWidths = '';
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->otables = array();
		$this->kerninfo = array();
		$this->ascent = 0;
		$this->descent = 0;
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->version = $version = $this->read_ulong();
		$this->panose = array();
		if ($version==0x4F54544F) 
			die("Postscript outlines are not supported");
		if ($version==0x74746366 && !$TTCfontID) 
			die("ERROR - You must define the TTCfontID for a TrueType Collection in config_fonts.php (". $file.")");
		if (!in_array($version, array(0x00010000,0x74727565)) && !$TTCfontID)
			die("Not a TrueType font: version=".$version);
		if ($TTCfontID > 0) {
			$this->version = $version = $this->read_ulong();	// TTC Header version now
			if (!in_array($version, array(0x00010000,0x00020000)))
				die("ERROR - Error parsing TrueType Collection: version=".$version." - " . $file);
			$this->numTTCFonts = $this->read_ulong();
			for ($i=1; $i<=$this->numTTCFonts; $i++) {
	      	      $this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong();	// TTFont version again now
		}
		$this->readTableDirectory($debug);
		$this->extractInfo($debug, $BMPonly, $kerninfo, $useOTL); 
		fclose($this->fh);
	}


	function readTableDirectory($debug=false) {
	    $this->numTables = $this->read_ushort();
            $this->searchRange = $this->read_ushort();
            $this->entrySelector = $this->read_ushort();
            $this->rangeShift = $this->read_ushort();
            $this->tables = array();	
            for ($i=0;$i<$this->numTables;$i++) {
                $record = array();
                $record['tag'] = $this->read_tag();
                $record['checksum'] = array($this->read_ushort(),$this->read_ushort());
                $record['offset'] = $this->read_ulong();
                $record['length'] = $this->read_ulong();
                $this->tables[$record['tag']] = $record;
		}
		if ($debug) $this->checksumTables();
	}

	function checksumTables() {
		// Check the checksums for all tables
		foreach($this->tables AS $t) {
		  if ($t['length'] > 0 && $t['length'] < $this->maxStrLenRead) {	// 1.02
            	$table = $this->get_chunk($t['offset'], $t['length']);
            	$checksum = $this->calcChecksum($table);
            	if ($t['tag'] == 'head') {
				$up = unpack('n*', substr($table,8,4));
				$adjustment[0] = $up[1];
				$adjustment[1] = $up[2];
            		$checksum = $this->sub32($checksum, $adjustment);
			}
            	$xchecksum = $t['checksum'];
            	if ($xchecksum != $checksum) 
            	    die(sprintf('TTF file "%s": invalid checksum %s table: %s (expected %s)', $this->filename,dechex($checksum[0]).dechex($checksum[1]),$t['tag'],dechex($xchecksum[0]).dechex($xchecksum[1])));
		  }
		}
	}

	function sub32($x, $y) {
		$xlo = $x[1];
		$xhi = $x[0];
		$ylo = $y[1];
		$yhi = $y[0];
		if ($ylo > $xlo) { $xlo += 1 << 16; $yhi += 1; }
		$reslo = $xlo-$ylo;
		if ($yhi > $xhi) { $xhi += 1 << 16;  }
		$reshi = $xhi-$yhi;
		$reshi = $reshi & 0xFFFF;
		return array($reshi, $reslo);
	}

	function calcChecksum($data)  {
		if (strlen($data) % 4) { $data .= str_repeat("\0",(4-(strlen($data) % 4))); }
		$len = strlen($data);
		$hi=0x0000;
		$lo=0x0000;
		for($i=0;$i<$len;$i+=4) {
			$hi += (ord($data[$i])<<8) + ord($data[$i+1]);
			$lo += (ord($data[$i+2])<<8) + ord($data[$i+3]);
			$hi += ($lo >> 16) & 0xFFFF;
			$lo = $lo & 0xFFFF;
		}
		return array($hi, $lo);
	}

	function get_table_pos($tag) {
		$offset = $this->tables[$tag]['offset'];
		$length = $this->tables[$tag]['length'];
		return array($offset, $length);
	}

	function seek($pos) {
		$this->_pos = $pos;
		fseek($this->fh,$this->_pos);
	}

	function skip($delta) {
		$this->_pos = $this->_pos + $delta;
		fseek($this->fh,$delta,SEEK_CUR);
	}

	function seek_table($tag, $offset_in_table = 0) {
		$tpos = $this->get_table_pos($tag);
		$this->_pos = $tpos[0] + $offset_in_table;
		fseek($this->fh, $this->_pos);
		return $this->_pos;
	}

	function read_tag() {
		$this->_pos += 4;
		return fread($this->fh,4);
	}

	function read_short() {
		$this->_pos += 2;
		$s = fread($this->fh,2);
		$a = (ord($s[0])<<8) + ord($s[1]);
		if ($a & (1 << 15) ) { 
			$a = ($a - (1 << 16)); 
		}
		return $a;
	}

	function unpack_short($s) {
		$a = (ord($s[0])<<8) + ord($s[1]);
		if ($a & (1 << 15) ) { 
			$a = ($a - (1 << 16)); 
		}
		return $a;
	}

	function read_ushort() {
		$this->_pos += 2;
		$s = fread($this->fh,2);
		return (ord($s[0])<<8) + ord($s[1]);
	}

	function read_ulong() {
		$this->_pos += 4;
		$s = fread($this->fh,4);
		// if large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0])*16777216) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function get_ushort($pos) {
		fseek($this->fh,$pos);
		$s = fread($this->fh,2);
		return (ord($s[0])<<8) + ord($s[1]);
	}

	function get_ulong($pos) {
		fseek($this->fh,$pos);
		$s = fread($this->fh,4);
		// iF large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0])*16777216) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function pack_short($val) {
		if ($val<0) { 
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		return pack("n",$val); 
	}

	function splice($stream, $offset, $value) {
		return substr($stream,0,$offset) . $value . substr($stream,$offset+strlen($value));
	}

	function _set_ushort($stream, $offset, $value) {
		$up = pack("n", $value);
		return $this->splice($stream, $offset, $up);
	}

	function _set_short($stream, $offset, $val) {
		if ($val<0) { 
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		$up = pack("n",$val); 
		return $this->splice($stream, $offset, $up);
	}

	function get_chunk($pos, $length) {
		fseek($this->fh,$pos);
		if ($length <1) { return ''; }
		return (fread($this->fh,$length));
	}

	function get_table($tag) {
		list($pos, $length) = $this->get_table_pos($tag);
		if ($length == 0) { return ''; }
		fseek($this->fh,$pos);
		return (fread($this->fh,$length));
	}

	function add($tag, $data) {
		if ($tag == 'head') {
			$data = $this->splice($data, 8, "\0\0\0\0");
		}
		$this->otables[$tag] = $data;
	}




/////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////

	function extractInfo($debug=false, $BMPonly=false, $kerninfo=false, $useOTL=0) {
		$this->panose = array();
		$this->sFamilyClass = 0;
		$this->sFamilySubClass = 0;
		///////////////////////////////////
		// name - Naming table
		///////////////////////////////////
			$name_offset = $this->seek_table("name");
			$format = $this->read_ushort();
			if ($format != 0 && $format != 1)
				die("Unknown name table format ".$format);
			$numRecords = $this->read_ushort();
			$string_data_offset = $name_offset + $this->read_ushort();
			$names = array(1=>'',2=>'',3=>'',4=>'',6=>'');
			$K = array_keys($names);
			$nameCount = count($names);
			for ($i=0;$i<$numRecords; $i++) {
				$platformId = $this->read_ushort();
				$encodingId = $this->read_ushort();
				$languageId = $this->read_ushort();
				$nameId = $this->read_ushort();
				$length = $this->read_ushort();
				$offset = $this->read_ushort();
				if (!in_array($nameId,$K)) continue;
				$N = '';
				if ($platformId == 3 && $encodingId == 1 && $languageId == 0x409) { // Microsoft, Unicode, US English, PS Name
					$opos = $this->_pos;
					$this->seek($string_data_offset + $offset);
					if ($length % 2 != 0)
						die("PostScript name is UTF-16BE string of odd length");
					$length /= 2;
					$N = '';
					while ($length > 0) {
						$char = $this->read_ushort();
						$N .= (chr($char));
						$length -= 1;
					}
					$this->_pos = $opos;
					$this->seek($opos);
				}
				else if ($platformId == 1 && $encodingId == 0 && $languageId == 0) { // Macintosh, Roman, English, PS Name
					$opos = $this->_pos;
					$N = $this->get_chunk($string_data_offset + $offset, $length);
					$this->_pos = $opos;
					$this->seek($opos);
				}
				if ($N && $names[$nameId]=='') {
					$names[$nameId] = $N;
					$nameCount -= 1;
					if ($nameCount==0) break;
				}
			}
			if ($names[6])
				$psName = $names[6];
			else if ($names[4])
				$psName = preg_replace('/ /','-',$names[4]);
			else if ($names[1])
				$psName = preg_replace('/ /','-',$names[1]);
			else
				$psName = '';
			if (!$psName)
				die("Could not find PostScript font name: ".$this->filename);
			if ($debug) {
			   for ($i=0;$i<count($psName);$i++) {
				$c = $psName[$i];
				$oc = ord($c);
				if ($oc>126 || strpos(' [](){}<>/%',$c)!==false)
					die("psName=".$psName." contains invalid character ".$c." ie U+".ord(c));
			   }
			}
			$this->name = $psName;
			if ($names[1]) { $this->familyName = $names[1]; } else { $this->familyName = $psName; }
			if ($names[2]) { $this->styleName = $names[2]; } else { $this->styleName = 'Regular'; }
			if ($names[4]) { $this->fullName = $names[4]; } else { $this->fullName = $psName; }
			if ($names[3]) { $this->uniqueFontID = $names[3]; } else { $this->uniqueFontID = $psName; }

			if ($names[6]) { $this->fullName = $names[6]; }

		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		if ($debug) { 
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1)
				die('Unknown head table version '. $ver_maj .'.'. $ver_min);
			$this->fontRevision = $this->read_ushort() . $this->read_ushort();

			$this->skip(4);
			$magic = $this->read_ulong();
			if ($magic != 0x5F0F3CF5) 
				die('Invalid head table magic ' .$magic);
			$this->skip(2);
		}
		else {
			$this->skip(18); 
		}
		$this->unitsPerEm = $unitsPerEm = $this->read_ushort();
		$scale = 1000 / $unitsPerEm;
		$this->skip(16);
		$xMin = $this->read_short();
		$yMin = $this->read_short();
		$xMax = $this->read_short();
		$yMax = $this->read_short();
		$this->bbox = array(($xMin*$scale), ($yMin*$scale), ($xMax*$scale), ($yMax*$scale));
		$this->skip(3*2);
		$indexToLocFormat = $this->read_ushort();
		$glyphDataFormat = $this->read_ushort();
		if ($glyphDataFormat != 0)
			die('Unknown glyph data format '.$glyphDataFormat);

		///////////////////////////////////
		// hhea metrics table
		///////////////////////////////////
		// ttf2t1 seems to use this value rather than the one in OS/2 - so put in for compatibility
		if (isset($this->tables["hhea"])) {
			$this->seek_table("hhea");
			$this->skip(4);
			$hheaAscender = $this->read_short();
			$hheaDescender = $this->read_short();
			$this->ascent = ($hheaAscender *$scale);
			$this->descent = ($hheaDescender *$scale);
		}

		///////////////////////////////////
		// OS/2 - OS/2 and Windows metrics table
		///////////////////////////////////
		if (isset($this->tables["OS/2"])) {
			$this->seek_table("OS/2");
			$version = $this->read_ushort();
			$this->skip(2);
			$usWeightClass = $this->read_ushort();
			$this->skip(2);
			$fsType = $this->read_ushort();
			if ($fsType == 0x0002 || ($fsType & 0x0300) != 0) {
				global $overrideTTFFontRestriction;
				if (!$overrideTTFFontRestriction) die('ERROR - Font file '.$this->filename.' cannot be embedded due to copyright restrictions.');
				$this->restrictedUse = true;
			}
			$this->skip(20);
			$sF = $this->read_short();
			$this->sFamilyClass = ($sF >> 8);
			$this->sFamilySubClass = ($sF & 0xFF);
			$this->_pos += 10;  //PANOSE = 10 byte length
			$panose = fread($this->fh,10);
			$this->panose = array();
			for ($p=0;$p<strlen($panose);$p++) { $this->panose[] = ord($panose[$p]); }
			$this->skip(26);
			$sTypoAscender = $this->read_short();
			$sTypoDescender = $this->read_short();
			if (!$this->ascent) $this->ascent = ($sTypoAscender*$scale);
			if (!$this->descent) $this->descent = ($sTypoDescender*$scale);
			if ($version > 1) {
				$this->skip(16);
				$sCapHeight = $this->read_short();
				$this->capHeight = ($sCapHeight*$scale);
			}
			else {
				$this->capHeight = $this->ascent;
			}
		}
		else {
			$usWeightClass = 500;
			if (!$this->ascent) $this->ascent = ($yMax*$scale);
			if (!$this->descent) $this->descent = ($yMin*$scale);
			$this->capHeight = $this->ascent;
		}
		$this->stemV = 50 + intval(pow(($usWeightClass / 65.0),2));

		///////////////////////////////////
		// post - PostScript table
		///////////////////////////////////
		$this->seek_table("post");
		if ($debug) { 
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj <1 || $ver_maj >4) 
				die('Unknown post table version '.$ver_maj);
		}
		else {
			$this->skip(4); 
		}
		$this->italicAngle = $this->read_short() + $this->read_ushort() / 65536.0;
		$this->underlinePosition = $this->read_short() * $scale;
		$this->underlineThickness = $this->read_short() * $scale;
		$isFixedPitch = $this->read_ulong();

		$this->flags = 4;

		if ($this->italicAngle!= 0) 
			$this->flags = $this->flags | 64;
		if ($usWeightClass >= 600)
			$this->flags = $this->flags | 262144;
		if ($isFixedPitch)
			$this->flags = $this->flags | 1;

		///////////////////////////////////
		// hhea - Horizontal header table
		///////////////////////////////////
		$this->seek_table("hhea");
		if ($debug) { 
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1)
				die('Unknown hhea table version '.$ver_maj);
			$this->skip(28);
		}
		else {
			$this->skip(32); 
		}
		$metricDataFormat = $this->read_ushort();
		if ($metricDataFormat != 0)
			die('Unknown horizontal metric data format '.$metricDataFormat);
		$numberOfHMetrics = $this->read_ushort();
		if ($numberOfHMetrics == 0) 
			die('Number of horizontal metrics is 0');

		///////////////////////////////////
		// maxp - Maximum profile table
		///////////////////////////////////
		$this->seek_table("maxp");
		if ($debug) { 
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1)
				die('Unknown maxp table version '.$ver_maj);
		}
		else {
			$this->skip(4); 
		}
		$numGlyphs = $this->read_ushort();


		///////////////////////////////////
		// cmap - Character to glyph index mapping table
		///////////////////////////////////
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i=0;$i<$cmapTableCount;$i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if (($platformID == 3 && $encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					if (!$unicode_cmap_offset) $unicode_cmap_offset = $cmap_offset + $offset;
					if ($BMPonly) break;
				}
			}
			// Microsoft, Unicode Format 12 table HKCS
			else if ((($platformID == 3 && $encodingID == 10) || $platformID == 0) && !$BMPonly) {
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 12) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos );
		}

		if (!$unicode_cmap_offset)
			die('Font ('.$this->filename .') does not have cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)');


		$sipset = false;
		$smpset = false;

		// mPDF 5.7.1
		$this->GSUBScriptLang = array();
		$this->rtlPUAstr = '';
		$this->rtlPUAarr = array();
		$this->GSUBFeatures = array();
		$this->GSUBLookups = array();
		$this->GPOSScriptLang = array();
		$this->GPOSFeatures = array();
		$this->GPOSLookups = array();
		$this->glyphIDtoUni = '';

		// Format 12 CMAP does characters above Unicode BMP i.e. some HKCS characters U+20000 and above
		if ($format == 12 && !$BMPonly) {
			$this->maxUniChar = 0;
			$this->seek($unicode_cmap_offset + 4);
			$length = $this->read_ulong();
			$limit = $unicode_cmap_offset + $length;
			$this->skip(4);

			$nGroups = $this->read_ulong();

			$glyphToChar = array();
			$charToGlyph = array();
			for($i=0; $i<$nGroups ; $i++) { 
				$startCharCode = $this->read_ulong(); 
				$endCharCode = $this->read_ulong(); 
				$startGlyphCode = $this->read_ulong(); 
				if ($endCharCode > 0x20000 && $endCharCode < 0x2FFFF) {
					$sipset = true; 
				}
				else if ($endCharCode > 0x10000 && $endCharCode < 0x1FFFF) {
					$smpset = true; 
				}
				$offset = 0;
				for ($unichar=$startCharCode;$unichar<=$endCharCode;$unichar++) {
					$glyph = $startGlyphCode + $offset ;
					$offset++;
					if ($unichar < 0x30000) {
						$charToGlyph[$unichar] = $glyph;
						$this->maxUniChar = max($unichar,$this->maxUniChar); 
						$glyphToChar[$glyph][] = $unichar;
					}
				}
			}
		}
		else {

			$glyphToChar = array();
			$charToGlyph = array();
			$this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph );

		}
		$this->sipset = $sipset ;
		$this->smpset = $smpset ;


		///////////////////////////////////
		// mPDF 5.7.1
		// Map Unmapped glyphs - from $numGlyphs
		if ($this->useOTL) {
			$bctr = 0xE000;
			for ($gid=1; $gid<$numGlyphs; $gid++) {
				if (!isset($glyphToChar[$gid])) {
					while(isset($charToGlyph[$bctr])) { $bctr++; }	// Avoid overwriting a glyph already mapped in PUA
					if (($bctr > 0xF8FF) && ($bctr < 0x2CEB0)) {
						if (!$BMPonly) {
							$bctr = 0x2CEB0; 	// Use unassigned area 0x2CEB0 to 0x2F7FF (space for 10,000 characters)
							$this->sipset = $sipset = true; // forces subsetting; also ensure charwidths are saved
							while(isset($charToGlyph[$bctr])) { $bctr++; }
						}
						else { die($names[1]." : WARNING - The font does not have enough space to map all (unmapped) included glyphs into Private Use Area U+E000 - U+F8FF"); }
					}
					$glyphToChar[$gid][] = $bctr;
					$charToGlyph[$bctr] = $gid;
					$this->maxUniChar = max($bctr,$this->maxUniChar); 
					$bctr++;
				}
			}
		}
		$this->glyphToChar = $glyphToChar;
		$this->charToGlyph = $charToGlyph;
		///////////////////////////////////
		// mPDF 5.7.1	OpenType Layout tables
		$this->GSUBScriptLang=array(); $this->rtlPUAstr = ''; $this->rtlPUAarr = array();
		if ($useOTL) {
			$this->_getGDEFtables();
			list($this->GSUBScriptLang, $this->GSUBFeatures, $this->GSUBLookups, $this->rtlPUAstr, $this->rtlPUAarr) = $this->_getGSUBtables();
			list($this->GPOSScriptLang, $this->GPOSFeatures, $this->GPOSLookups) = $this->_getGPOStables();
			$this->glyphIDtoUni = str_pad('', 256*256*3, "\x00");
			foreach($glyphToChar AS $gid=>$arr) {
				if (isset($glyphToChar[$gid][0])) {
					$char = $glyphToChar[$gid][0];
					if ($char != 0 && $char != 65535) {
						$this->glyphIDtoUni[$gid*3] = chr($char >> 16);
						$this->glyphIDtoUni[$gid*3 + 1] = chr(($char >> 8) & 0xFF);
						$this->glyphIDtoUni[$gid*3 + 2] = chr($char & 0xFF);
					}
				}
			}
		}
		///////////////////////////////////

		///////////////////////////////////
		// hmtx - Horizontal metrics table
		///////////////////////////////////
		$this->getHMTX($numberOfHMetrics, $numGlyphs, $glyphToChar, $scale);

		///////////////////////////////////
		// kern - Kerning pair table
		///////////////////////////////////
		if ($kerninfo) {
			// Recognises old form of Kerning table - as required by Windows - Format 0 only
			$kern_offset = $this->seek_table("kern");
			$version = $this->read_ushort();
			$nTables = $this->read_ushort();
			// subtable header
			$sversion = $this->read_ushort();
			$slength = $this->read_ushort();
			$scoverage = $this->read_ushort();
			$format = $scoverage >> 8;
 			if ($kern_offset && $version==0 && $format==0) {
				// Format 0
				$nPairs = $this->read_ushort();
				$this->skip(6);
				for ($i=0; $i<$nPairs; $i++) {
					$left = $this->read_ushort();
					$right = $this->read_ushort();
					$val = $this->read_short();
					if (count($glyphToChar[$left])==1 && count($glyphToChar[$right])==1) {
					  if ($left != 32 && $right != 32) {
						$this->kerninfo[$glyphToChar[$left][0]][$glyphToChar[$right][0]] = intval($val*$scale);
					  }
					}
				}
			}
		}
	}


/////////////////////////////////////////////////////////////////////////////////////////
	function _getGDEFtables() {
		///////////////////////////////////
		// GDEF - Glyph Definition
		///////////////////////////////////
		// http://www.microsoft.com/typography/otspec/gdef.htm
		if (isset($this->tables["GDEF"])) {
			if ($this->mode == 'summary') { $this->mpdf->WriteHTML('<h1>GDEF table</h1>');  }
			$gdef_offset = $this->seek_table("GDEF");
			// ULONG Version of the GDEF table-currently 0x00010000
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			// Version 0x00010002 of GDEF header contains additional Offset to a list defining mark glyph set definitions (MarkGlyphSetDef)
			$GlyphClassDef_offset = $this->read_ushort();
			$AttachList_offset = $this->read_ushort();
			$LigCaretList_offset = $this->read_ushort();
			$MarkAttachClassDef_offset = $this->read_ushort();
			if ($ver_min == 2) {
				$MarkGlyphSetsDef_offset = $this->read_ushort();
			}

			// GlyphClassDef
			$this->seek($gdef_offset+$GlyphClassDef_offset );
			/*
			1	Base glyph (single character, spacing glyph)
			2	Ligature glyph (multiple character, spacing glyph)
			3	Mark glyph (non-spacing combining glyph)
			4	Component glyph (part of single character, spacing glyph)
			*/
			$GlyphByClass = $this->_getClassDefinitionTable();

			if ($this->mode == 'summary') {
				$this->mpdf->WriteHTML('<h2>Glyph classes</h2>'); 
			}

			if (isset($GlyphByClass[1]) && count($GlyphByClass[1])>0) { 
				$this->GlyphClassBases = $this->formatClassArr($GlyphByClass[1]); 
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h3>Glyph class 1</h3>'); 
					$this->mpdf->WriteHTML('<h5>Base glyph (single character, spacing glyph)</h5>'); 
					$html = '';
					$html .= '<div class="glyphs">'; 
					foreach ($GlyphByClass[1] AS $g) {
						$html .= '&#x'.$g.'; '; 
					}
					$html .= '</div>';
					$this->mpdf->WriteHTML($html); 
				}
			}
			else { $this->GlyphClassBases = ''; }
			if (isset($GlyphByClass[2]) && count($GlyphByClass[2])>0) {
				$this->GlyphClassLigatures = $this->formatClassArr($GlyphByClass[2]); 
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h3>Glyph class 2</h3>'); 
					$this->mpdf->WriteHTML('<h5>Ligature glyph (multiple character, spacing glyph)</h5>'); 
					$html = '';
					$html .= '<div class="glyphs">'; 
					foreach ($GlyphByClass[2] AS $g) {
						$html .= '&#x'.$g.'; '; 
					}
					$html .= '</div>';
					$this->mpdf->WriteHTML($html); 
				}
			}
			else { $this->GlyphClassLigatures = ''; }
			if (isset($GlyphByClass[3]) && count($GlyphByClass[3])>0) {
				$this->GlyphClassMarks = $this->formatClassArr($GlyphByClass[3]); 
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h3>Glyph class 3</h3>'); 
					$this->mpdf->WriteHTML('<h5>Mark glyph (non-spacing combining glyph)</h5>'); 
					$html = '';
					$html .= '<div class="glyphs">'; 
					foreach ($GlyphByClass[3] AS $g) {
						$html .= '&#x25cc;&#x'.$g.'; '; 
					}
					$html .= '</div>';
					$this->mpdf->WriteHTML($html); 
				}
			}
			else { $this->GlyphClassMarks = ''; }
			if (isset($GlyphByClass[4]) && count($GlyphByClass[4])>0) {
				$this->GlyphClassComponents = $this->formatClassArr($GlyphByClass[4]); 
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h3>Glyph class 4</h3>'); 
					$this->mpdf->WriteHTML('<h5>Component glyph (part of single character, spacing glyph)</h5>'); 
					$html = '';
					$html .= '<div class="glyphs">'; 
					foreach ($GlyphByClass[4] AS $g) {
						$html .= '&#x'.$g.'; '; 
					}
					$html .= '</div>';
					$this->mpdf->WriteHTML($html); 
				}
			}
			else { $this->GlyphClassComponents = ''; }

			$Marks = $GlyphByClass[3]; // to use for MarkAttachmentType


/* Required for GPOS
			// Attachment List
			if ($AttachList_offset) {
				$this->seek($gdef_offset+$AttachList_offset );
			}
The Attachment Point List table (AttachmentList) identifies all the attachment points defined in the GPOS table and their associated glyphs so a client can quickly access coordinates for each glyph's attachment points. As a result, the client can cache coordinates for attachment points along with glyph bitmaps and avoid recalculating the attachment points each time it displays a glyph. Without this table, processing speed would be slower because the client would have to decode the GPOS lookups that define attachment points and compile the points in a list.

The Attachment List table (AttachList) may be used to cache attachment point coordinates along with glyph bitmaps.

The table consists of an offset to a Coverage table (Coverage) listing all glyphs that define attachment points in the GPOS table, a count of the glyphs with attachment points (GlyphCount), and an array of offsets to AttachPoint tables (AttachPoint). The array lists the AttachPoint tables, one for each glyph in the Coverage table, in the same order as the Coverage Index.
AttachList table
Type 	Name 	Description
Offset 	Coverage 	Offset to Coverage table - from beginning of AttachList table
uint16 	GlyphCount 	Number of glyphs with attachment points
Offset 	AttachPoint[GlyphCount] 	Array of offsets to AttachPoint tables-from beginning of AttachList table-in Coverage Index order

An AttachPoint table consists of a count of the attachment points on a single glyph (PointCount) and an array of contour indices of those points (PointIndex), listed in increasing numerical order.

AttachPoint table
Type 	Name 	Description
uint16 	PointCount 	Number of attachment points on this glyph
uint16 	PointIndex[PointCount] 	Array of contour point indices -in increasing numerical order

See Example 3 - http://www.microsoft.com/typography/otspec/gdef.htm
*/


			// Ligature Caret List 
			// The Ligature Caret List table (LigCaretList) defines caret positions for all the ligatures in a font. 
			// Not required for mDPF


			// MarkAttachmentType
			if ($MarkAttachClassDef_offset) {
				if ($this->mode == 'summary') { $this->mpdf->WriteHTML('<h1>Mark Attachment Types</h1>');  }
				$this->seek($gdef_offset+$MarkAttachClassDef_offset );
				$MarkAttachmentTypes = $this->_getClassDefinitionTable();
				foreach($MarkAttachmentTypes AS $class=>$glyphs) {

					if (is_array($Marks) && count($Marks)) {
						$mat = arr