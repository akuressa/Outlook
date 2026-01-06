<?php

define("_OTL_OLD_SPEC_COMPAT_1", true);

define("_DICT_NODE_TYPE_SPLIT", 0x01);
define("_DICT_NODE_TYPE_LINEAR", 0x02);
define("_DICT_INTERMEDIATE_MATCH", 0x03);
define("_DICT_FINAL_MATCH", 0x04);



class otl {

var $mpdf;
var $arabLeftJoining;
var $arabRightJoining;
var $arabTransparentJoin;
var $arabTransparent;
var $GSUBdata;
var $GPOSdata;
var $GSUBfont;
var $fontkey;
var $ttfOTLdata;
var $glyphIDtoUni;
var $_pos;
var $GSUB_offset;
var $GPOS_offset;
var $MarkAttachmentType;
var $MarkGlyphSets;
var $GlyphClassMarks; 
var $GlyphClassLigatures; 
var $GlyphClassBases; 
var $GlyphClassComponents; 
var $Ignores;
var $LuCoverage;
var $OTLdata;
var $assocLigs;
var $assocMarks;
var $shaper;
var $restrictToSyllable;
var $lbdicts;	// Line-breaking dictionaries
var $LuDataCache;

var $debugOTL = false;

function otl(&$mpdf) {
	$this->mpdf = $mpdf;

	$this->arabic_initialise();
	$this->current_fh = '';

	$this->lbdicts = array();
	$this->LuDataCache = array();
}

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
//////////       APPLY OTL          ////////////////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

function applyOTL($str, $useOTL) {
	$this->OTLdata = array();
	if (trim($str)=='') { return $str; }
	if (!$useOTL) { return $str; }

	// 1. Load GDEF data
	//==============================
	$this->fontkey = $this->mpdf->CurrentFont['fontkey'];
	$this->glyphIDtoUni = $this->mpdf->CurrentFont['glyphIDtoUni'];
	if (!isset($this->GDEFdata[$this->fontkey])) {
		include(_MPDF_TTFONTDATAPATH.$this->fontkey.'.GDEFdata.php'); 
		$this->GSUB_offset = $this->GDEFdata[$this->fontkey]['GSUB_offset'] = $GSUB_offset;
		$this->GPOS_offset = $this->GDEFdata[$this->fontkey]['GPOS_offset'] = $GPOS_offset;
		$this->GSUB_length = $this->GDEFdata[$this->fontkey]['GSUB_length'] = $GSUB_length;
		$this->MarkAttachmentType = $this->GDEFdata[$this->fontkey]['MarkAttachmentType'] = $MarkAttachmentType;
		$this->MarkGlyphSets = $this->GDEFdata[$this->fontkey]['MarkGlyphSets'] = $MarkGlyphSets;
		$this->GlyphClassMarks = $this->GDEFdata[$this->fontkey]['GlyphClassMarks'] = $GlyphClassMarks; 
		$this->GlyphClassLigatures = $this->GDEFdata[$this->fontkey]['GlyphClassLigatures'] = $GlyphClassLigatures; 
		$this->GlyphClassComponents = $this->GDEFdata[$this->fontkey]['GlyphClassComponents'] = $GlyphClassComponents; 
		$this->GlyphClassBases = $this->GDEFdata[$this->fontkey]['GlyphClassBases'] = $GlyphClassBases;
	}
	else {
		$this->GSUB_offset = $this->GDEFdata[$this->fontkey]['GSUB_offset'];
		$this->GPOS_offset = $this->GDEFdata[$this->fontkey]['GPOS_offset'];
		$this->GSUB_length = $this->GDEFdata[$this->fontkey]['GSUB_length'];
		$this->MarkAttachmentType = $this->GDEFdata[$this->fontkey]['MarkAttachmentType'];
		$this->MarkGlyphSets = $this->GDEFdata[$this->fontkey]['MarkGlyphSets'];
		$this->GlyphClassMarks = $this->GDEFdata[$this->fontkey]['GlyphClassMarks']; 
		$this->GlyphClassLigatures = $this->GDEFdata[$this->fontkey]['GlyphClassLigatures']; 
		$this->GlyphClassComponents = $this->GDEFdata[$this->fontkey]['GlyphClassComponents']; 
		$this->GlyphClassBases = $this->GDEFdata[$this->fontkey]['GlyphClassBases'];
	}

	// 2. Prepare string as HEX string and Analyse character properties
	//=================================================================
	$earr = $this->mpdf->UTF8StringToArray($str, false);

	$scriptblock = 0;
	$scriptblocks = array();
	$scriptblocks[0] = 0;
	$vstr = '';
	$OTLdata = array();
	$subchunk = 0;
	$charctr = 0;
	foreach($earr as $char) {
		$ucd_record = UCDN::get_ucd_record($char);
		$sbl = $ucd_record[6];

		// Special case - Arabic End of Ayah
		if ($char==1757) { $sbl = UCDN::SCRIPT_ARABIC; }

		if ($sbl && $sbl != 40 && $sbl != 102) {
			if ($scriptblock == 0) { $scriptblock = $sbl; $scriptblocks[$subchunk] = $scriptblock; }
			else if ($scriptblock > 0 && $scriptblock != $sbl) {
				// *************************************************
				// NEW (non-common) Script encountered in this chunk. Start a new subchunk
				$subchunk++;
				$scriptblock = $sbl;
				$charctr = 0;
				$scriptblocks[$subchunk] = $scriptblock;
			}
		}

		$OTLdata[$subchunk][$charctr]['general_category'] = $ucd_record[0];
		$OTLdata[$subchunk][$charctr]['bidi_type'] = $ucd_record[2];

		//$OTLdata[$subchunk][$charctr]['combining_class'] = $ucd_record[1];
		//$OTLdata[$subchunk][$charctr]['bidi_type'] = $ucd_record[2];
		//$OTLdata[$subchunk][$charctr]['mirrored'] = $ucd_record[3];
		//$OTLdata[$subchunk][$charctr]['east_asian_width'] = $ucd_record[4];
		//$OTLdata[$subchunk][$charctr]['normalization_check'] = $ucd_record[5];
		//$OTLdata[$subchunk][$charctr]['script'] = $ucd_record[6];

		$charasstr = $this->unicode_hex($char); 

		if (strpos($this->GlyphClassMarks, $charasstr)!==false) { $OTLdata[$subchunk][$charctr]['group'] =  'M'; }
		else if ($char == 32 || $char == 12288) { $OTLdata[$subchunk][$charctr]['group'] =  'S'; }	// 12288 = 0x3000 = CJK space
		else { $OTLdata[$subchunk][$charctr]['group'] =  'C'; }

		$OTLdata[$subchunk][$charctr]['uni'] =  $char;
		$OTLdata[$subchunk][$charctr]['hex'] =  $charasstr;
		$charctr++;
	}

	/* PROCESS EACH SUBCHUNK WITH DIFFERENT SCRIPTS */
for($sch=0;$sch<=$subchunk;$sch++) {
	$this->OTLdata = $OTLdata[$sch];
	$scriptblock = $scriptblocks[$sch];

	// 3. Get Appropriate Scripts, and Shaper engine from analysing text and list of available scripts/langsys in font
	//==============================
	// Based on actual script block of text, select shaper (and line-breaking dictionaries)
	if (UCDN::SCRIPT_DEVANAGARI <= $scriptblock && $scriptblock <= UCDN::SCRIPT_MALAYALAM) { $this->shaper = "I"; }	// INDIC shaper
	else if ($scriptblock == UCDN::SCRIPT_ARABIC || $scriptblock == UCDN::SCRIPT_SYRIAC) { $this->shaper = "A"; }	// ARABIC shaper
	else if ($scriptblock == UCDN::SCRIPT_NKO || $scriptblock == UCDN::SCRIPT_MANDAIC) { $this->shaper = "A"; }	// ARABIC shaper
	else if ($scriptblock == UCDN::SCRIPT_KHMER) { $this->shaper = "K"; }	// KHMER shaper
	else if ($scriptblock == UCDN::SCRIPT_THAI) { $this->shaper = "T"; }	// THAI shaper
	else if ($scriptblock == UCDN::SCRIPT_LAO) { $this->shaper = "L"; }	// LAO shaper
	else if ($scriptblock == UCDN::SCRIPT_SINHALA) { $this->shaper = "S"; }	// SINHALA shaper
	else if ($scriptblock == UCDN::SCRIPT_MYANMAR) { $this->shaper = "M"; }	// MYANMAR shaper
	else if ($scriptblock == UCDN::SCRIPT_NEW_TAI_LUE) { $this->shaper = "E"; }	// SEA South East Asian shaper
	else if ($scriptblock == UCDN::SCRIPT_CHAM) { $this->shaper = "E"; }		// SEA South East Asian shaper
	else if ($scriptblock == UCDN::SCRIPT_TAI_THAM) { $this->shaper = "E"; }	// SEA South East Asian shaper
	else $this->shaper = "";
	// Get scripttag based on actual text script
	$scripttag = UCDN::$uni_scriptblock[$scriptblock];

	$GSUBscriptTag = '';
	$GSUBlangsys = '';
	$GPOSscriptTag = '';
	$GPOSlangsys = '';
	$is_old_spec = false;

	$ScriptLang = $this->mpdf->CurrentFont['GSUBScriptLang'];
	if (count($ScriptLang)) { 
		list($GSUBscriptTag,$is_old_spec) = $this->_getOTLscriptTag($ScriptLang, $scripttag, $scriptblock, $this->shaper, $useOTL, 'GSUB');
		if ($this->mpdf->fontLanguageOverride && strpos($ScriptLang[$GSUBscriptTag], $this->mpdf->fontLanguageOverride)!==false) {
			$GSUBlangsys = str_pad($this->mpdf->fontLanguageOverride,4);
		}
		else if ($GSUBscriptTag && isset($ScriptLang[$GSUBscriptTag]) && $ScriptLang[$GSUBscriptTag]!='') { 
			$GSUBlangsys = $this->_getOTLLangTag($this->mpdf->currentLang, $ScriptLang[$GSUBscriptTag]);
		}
	}
	$ScriptLang = $this->mpdf->CurrentFont['GPOSScriptLang'];

	// NB If after GSUB, the same script/lang exist for GPOS, just use these...
	if ($GSUBscriptTag && $GSUBlangsys && isset($ScriptLang[$GSUBscriptTag]) && strpos($ScriptLang[$GSUBscriptTag], $GSUBlangsys)!==false) {
		$GPOSlangsys = $GSUBlangsys;
		$GPOSscriptTag = $GSUBscriptTag;
	}

	// else repeat for GPOS
	// [Font XBRiyaz has GSUB tables for latn, but not GPOS for latn]
	else if (count($ScriptLang)) {
		list($GPOSscriptTag,$dummy) = $this->_getOTLscriptTag($ScriptLang, $scripttag, $scriptblock, $this->shaper, $useOTL, 'GPOS');
		if ($GPOSscriptTag && $this->mpdf->fontLanguageOverride && strpos($ScriptLang[$GPOSscriptTag], $this->mpdf->fontLanguageOverride)!==false) {
			$GPOSlangsys = str_pad($this->mpdf->fontLanguageOverride,4);
		}
		else if ($GPOSscriptTag && isset($ScriptLang[$GPOSscriptTag]) && $ScriptLang[$GPOSscriptTag]!='') { 
			$GPOSlangsys = $this->_getOTLLangTag($this->mpdf->currentLang, $ScriptLang[$GPOSscriptTag]);
		}
	}

	////////////////////////////////////////////////////////////////
	// This is just for the font_dump_OTL utility to set script and langsys override
	if (isset($this->mpdf->overrideOTLsettings) && isset($this->mpdf->overrideOTLsettings[$this->fontkey])) {
		$GSUBscriptTag = $GPOSscriptTag = $this->mpdf->overrideOTLsettings[$this->fontkey]['script'];
		$GSUBlangsys = $GPOSlangsys = $this->mpdf->overrideOTLsettings[$this->fontkey]['lang'];
	}
	////////////////////////////////////////////////////////////////

	if (!$GSUBscriptTag && !$GSUBlangsys && !$GPOSscriptTag && !$GPOSlangsys) {
		// Remove ZWJ and ZWNJ
		for ($i=0;$i<count($this->OTLdata);$i++) {
			if ($this->OTLdata[$i]['uni']==8204 || $this->OTLdata[$i]['uni']==8205) {
				array_splice($this->OTLdata, $i, 1);
			}
		}
		$this->schOTLdata[$sch] = $this->OTLdata;
		$this->OTLdata = array();
		continue; 
	}

	// Don't use MYANMAR shaper unless using v2 scripttag
	if ($this->shaper == 'M' && $GSUBscriptTag != 'mym2') { $this->shaper = ''; }

	$GSUBFeatures = (isset($this->mpdf->CurrentFont['GSUBFeatures'][$GSUBscriptTag][$GSUBlangsys]) ? $this->mpdf->CurrentFont['GSUBFeatures'][$GSUBscriptTag][$GSUBlangsys] : false);
	$GPOSFeatures = (isset($this->mpdf->CurrentFont['GPOSFeatures'][$GPOSscriptTag][$GPOSlangsys]) ? $this->mpdf->CurrentFont['GPOSFeatures'][$GPOSscriptTag][$GPOSlangsys] : false);

	$this->assocLigs = array();	// Ligatures[$posarr lpos] => nc
	$this->assocMarks = array(); 	// assocMarks[$posarr mpos] => array(compID, ligPos)

	if (!isset($this->GDEFdata[$this->fontkey]['GSUBGPOStables'])) {
		$this->ttfOTLdata = $this->GDEFdata[$this->fontkey]['GSUBGPOStables'] = file_get_contents(_MPDF_TTFONTDATAPATH.$this->fontkey.'.GSUBGPOStables.dat','rb') or die('Can\'t open file ' . _MPDF_TTFONTDATAPATH.$this->fontkey.'.GSUBGPOStables.dat');
	}
	else {
		$this->ttfOTLdata = $this->GDEFdata[$this->fontkey]['GSUBGPOStables'];
	}


	if ($this->debugOTL) { $this->_dumpproc('BEGIN', '-', '-', '-', '-', -1, '-', 0); }


////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
/////////  LINE BREAKING FOR KHMER, THAI + LAO /////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
	// Insert U+200B at word boundaries using dictionaries
	if ($this->mpdf->useDictionaryLBR && ($this->shaper == "K" || $this->shaper == "T" || $this->shaper == "L")) {
		// Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
		$this->SEAlineBreaking();
	}
	// Insert U+200B at word boundaries for Tibetan
	else if ($this->mpdf->useTibetanLBR && $scriptblock == UCDN::SCRIPT_TIBETAN ) {
		// Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
		$this->TibetanlineBreaking();
	}
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
//////////       GSUB          /////////////////////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
  if (($useOTL & 0xFF) && $GSUBscriptTag && $GSUBlangsys && $GSUBFeatures) {

	// 4. Load GSUB data, Coverage & Lookups
	//=================================================================

	$this->GSUBfont = $this->fontkey.'.GSUB.'.$GSUBscriptTag.'.'.$GSUBlangsys;

	if (!isset($this->GSUBdata[$this->GSUBfont])) {
		if (file_exists(_MPDF_TTFONTDATAPATH.$this->mpdf->CurrentFont['fontkey'].'.GSUB.'.$GSUBscriptTag.'.'.$GSUBlangsys.'.php')) {
			include_once(_MPDF_TTFONTDATAPATH.$this->mpdf->CurrentFont['fontkey'].'.GSUB.'.$GSUBscriptTag.'.'.$GSUBlangsys.'.php'); 
			$this->GSUBdata[$this->GSUBfont]['rtlSUB'] = $rtlSUB;
			$this->GSUBdata[$this->GSUBfont]['finals'] = $finals;
			if ($this->shaper=='I') {
				$this->GSUBdata[$this->GSUBfont]['rphf'] = $rphf;
				$this->GSUBdata[$this->GSUBfont]['half'] = $half;
				$this->GSUBdata[$this->GSUBfont]['pref'] = $pref;
				$this->GSUBdata[$this->GSUBfont]['blwf'] = $blwf;
				$this->GSUBdata[$this->GSUBfont]['pstf'] = $pstf;
			}
		}
		else { $this->GSUBdata[$this->GSUBfont] = array('rtlSUB'=>array(), 'rphf'=>array(), 'rphf'=>array(), 
			'pref'=>array(), 'blwf'=>array(), 'pstf'=>array(), 'finals'=>''
			);
		}
	}

	if (!isset($this->GSUBdata[$this->fontkey])) {
		include(_MPDF_TTFONTDATAPATH.$this->fontkey.'.GSUBdata.php'); 
		$this->GSLuCoverage = $this->GSUBdata[$this->fontkey]['GSLuCoverage'] = $GSLuCoverage;
	}
	else {
		$this->GSLuCoverage = $this->GSUBdata[$this->fontkey]['GSLuCoverage'];
	}

	$this->GSUBLookups = $this->mpdf->CurrentFont['GSUBLookups'];


	// 5(A). GSUB - Shaper - ARABIC
	//==============================
	if ($this->shaper == 'A') {
		//-----------------------------------------------------------------------------------
		// a. Apply initial GSUB Lookups (in order specified in lookup list but only selecting from certain tags)
		//-----------------------------------------------------------------------------------
		$tags = 'locl ccmp';
		$omittags = '';
		$usetags = $tags;
		if(!empty($this->mpdf->OTLtags)) { 
			$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, true) ;
		}
		$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);

		//-----------------------------------------------------------------------------------
		// b. Apply context-specific forms GSUB Lookups (initial, isolated, medial, final)
		//-----------------------------------------------------------------------------------
		// Arab and Syriac are the only scripts requiring the special joining - which takes the place of
		// isol fina medi init rules in GSUB (+ fin2 fin3 med2 in Syriac syrc)
		$tags = 'isol fina fin2 fin3 medi med2 init';
		$omittags = '';
		$usetags = $tags;
		if(!empty($this->mpdf->OTLtags)) { 
			$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, true) ;
		}

		$this->arabGlyphs = $this->GSUBdata[$this->GSUBfont]['rtlSUB'];

		$gcms = explode("| ",$this->GlyphClassMarks);
		$gcm = array();
		foreach($gcms AS $g) { $gcm[hexdec($g)] = 1; }
		$this->arabTransparentJoin = $this->arabTransparent + $gcm;
		$this->arabic_shaper($usetags, $GSUBscriptTag);

		//-----------------------------------------------------------------------------------
		// c. Set Kashida points (after joining occurred - medi, fina, init) but before other substitutions
		//-----------------------------------------------------------------------------------
		//if ($scriptblock == UCDN::SCRIPT_ARABIC ) {
		for ($i=0;$i<count($this->OTLdata);$i++) {
			// Put the kashida marker on the character BEFORE which is inserted the kashida
			// Kashida marker is inverse of priority i.e. Priority 1 => 7, Priority 7 => 1.

			// Priority 1	User-inserted Kashida 0640 = Tatweel
			// The user entered a Kashida in a position
			// Position: Before the user-inserted kashida
			if ($this->OTLdata[$i]['uni']==0x0640) {
				$this->OTLdata[$i]['GPOSinfo']['kashida'] = 8; // Put before the next character
			}

			// Priority 2	Seen (0633)  FEB3, FEB4; Sad (0635)  FEBB, FEBC
			// Initial or medial form
			// Connecting to the next character
			// Position: After the character
			else if ($this->OTLdata[$i]['uni']==0xFEB3 || $this->OTLdata[$i]['uni']==0xFEB4 || $this->OTLdata[$i]['uni']==0xFEBB || $this->OTLdata[$i]['uni']==0xFEBC) {
				$checkpos = $i+1;
				while (isset($this->OTLdata[$checkpos]) && strpos($this->GlyphClassMarks, $this->OTLdata[$checkpos]['hex'])!==false) {
					$checkpos++; 
				}
				if (isset($this->OTLdata[$checkpos])) {
					$this->OTLdata[$checkpos]['GPOSinfo']['kashida'] = 7; // Put after marks on next character
				}
			}

			// Priority 3	Taa Marbutah (0629) FE94; Haa (062D) FEA2; Dal (062F) FEAA
			// Final form
			// Connecting to previous character
			// Position: Before the character
			else if ($this->OTLdata[$i]['uni']==0xFE94 || $this->OTLdata[$i]['uni']==0xFEA2 || $this->OTLdata[$i]['uni']==0xFEAA) {
				$this->OTLdata[$i]['GPOSinfo']['kashida'] = 6;
			}

			// Priority 4	Alef (0627) FE8E; Tah (0637) FEC2; Lam (0644) FEDE; Kaf (0643)  FEDA; Gaf (06AF) FB93
			// Final form
			// Connecting to previous character
			// Position: Before the character
			else if ($this->OTLdata[$i]['uni']==0xFE8E || $this->OTLdata[$i]['uni']==0xFEC2 || $this->OTLdata[$i]['uni']==0xFEDE || $this->OTLdata[$i]['uni']==0xFEDA || $this->OTLdata[$i]['uni']==0xFB93) {
				$this->OTLdata[$i]['GPOSinfo']['kashida'] = 5;
			}

			// Priority 5	RA (0631) FEAE; Ya (064A)  FEF2 FEF4; Alef Maqsurah (0649) FEF0 FBE9
			// Final or Medial form
			// Connected to preceding medial BAA (0628) = FE92
			// Position: Before preceding medial Baa
			// Although not mentioned in spec, added Farsi Yeh (06CC) FBFD FBFF; equivalent to 064A or 0649
			else if ($this->OTLdata[$i]['uni']==0xFEAE || $this->OTLdata[$i]['uni']==0xFEF2 || $this->OTLdata[$i]['uni']==0xFEF0
				|| $this->OTLdata[$i]['uni']==0xFEF4 || $this->OTLdata[$i]['uni']==0xFBE9
				|| $this->OTLdata[$i]['uni']==0xFBFD || $this->OTLdata[$i]['uni']==0xFBFF
				) {
				$checkpos = $i-1;
				while (isset($this->OTLdata[$checkpos]) && strpos($this->GlyphClassMarks, $this->OTLdata[$checkpos]['hex'])!==false) {
					$checkpos--; 
				}
				if (isset($this->OTLdata[$checkpos]) && $this->OTLdata[$checkpos]['uni']==0xFE92) {
					$this->OTLdata[$checkpos]['GPOSinfo']['kashida'] = 4;	// ******* Before preceding BAA
				}
			}

			// Priority 6	WAW (0648) FEEE; Ain (0639) FECA; Qaf (0642) FED6; Fa (0641) FED2
			// Final form
			// Connecting to previous character
			// Position: Before the character
			else if ($this->OTLdata[$i]['uni']==0xFEEE || $this->OTLdata[$i]['uni']==0xFECA || $this->OTLdata[$i]['uni']==0xFED6 || $this->OTLdata[$i]['uni']==0xFED2) {
				$this->OTLdata[$i]['GPOSinfo']['kashida'] = 3;
			}

			// Priority 7	Other connecting characters
			// Final form
			// Connecting to previous character
			// Position: Before the character
			/* This isn't in the spec, but using MS WORD as a basis, give a lower priority to the 3 characters already checked 
			   in (5) above. Test case: 
			   &#x62e;&#x652;&#x631;&#x64e;&#x649;&#x670;
			   &#x641;&#x64e;&#x62a;&#x64f;&#x630;&#x64e;&#x643;&#x651;&#x650;&#x631;
			*/

			if (!isset($this->OTLdata[$i]['GPOSinfo']['kashida'])) {
				if (strpos($this->GSUBdata[$this->GSUBfont]['finals'], $this->OTLdata[$i]['hex'])!==false) {	// ANY OTHER FINAL FORM
					$this->OTLdata[$i]['GPOSinfo']['kashida'] = 2;
				}
				else if (strpos('0FEAE 0FEF0 0FEF2',$this->OTLdata[$i]['hex'])!==false) {	// not already included in 5 above
					$this->OTLdata[$i]['GPOSinfo']['kashida'] = 1;
				}
			}
		}

		//-----------------------------------------------------------------------------------
		// d. Apply Presentation Forms GSUB Lookups (+ any discretionary) - Apply one at a time in Feature order
		//-----------------------------------------------------------------------------------
		$tags = 'rlig calt liga clig mset';

		$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
		$usetags = $tags;
		if(!empty($this->mpdf->OTLtags)) { 
			$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false) ;
		}

		$ts = explode(' ',$usetags);
		foreach($ts AS $ut) {	//  - Apply one at a time in Feature order
			$this->_applyGSUBrules($ut, $GSUBscriptTag, $GSUBlangsys);
		}
		//-----------------------------------------------------------------------------------
		// e. NOT IN SPEC
		// If space precedes a mark -> substitute a &nbsp; before the Mark, to prevent line breaking Test: 
		//-----------------------------------------------------------------------------------
		for($ptr=1; $ptr<count($this->OTLdata); $ptr++) {
			if ($this->OTLdata[$ptr]['general_category'] == UCDN::UNICODE_GENERAL_CATEGORY_NON_SPACING_MARK && $this->OTLdata[$ptr-1]['uni'] == 32) {
				$this->OTLdata[$ptr-1]['uni'] =  0xa0;
				$this->OTLdata[$ptr-1]['hex'] =  '000A0';
			}
		}
	}

	// 5(I). GSUB - Shaper - INDIC and SINHALA and KHMER
	//===================================
	else if ($this->shaper == 'I' || $this->shaper == 'K' || $this->shaper == 'S') {
		$this->restrictToSyllable = true;
		//-----------------------------------------------------------------------------------
		// a. First decompose/compose split mattras
		// (normalize) ??????? Nukta/Halant order etc ??????????????????????????????????????????????????????????????????????????
		//-----------------------------------------------------------------------------------
		for($ptr=0; $ptr<count($this->OTLdata); $ptr++) {
			$char = $this->OTLdata[$ptr]['uni'];
			$sub = INDIC::decompose_indic($char);
			if ($sub) {
				$newinfo = array();
				for($i=0;$i<count($sub);$i++) {
					$newinfo[$i] = array();
					$ucd_record = UCDN::get_ucd_record($sub[$i]);
					$newinfo[$i]['general_category'] = $ucd_record[0];
					$newinfo[$i]['bidi_type'] = $ucd_record[2];
					$charasstr = $this->unicode_hex($sub[$i]); 
					if (strpos($this->GlyphClassMarks, $charasstr)!==false) { $newinfo[$i]['group'] =  'M'; }
					else { $newinfo[$i]['group'] =  'C'; }
					$newinfo[$i]['uni'] =  $sub[$i];
					$newinfo[$i]['hex'] =  $charasstr;
				}
				array_splice($this->OTLdata, $ptr, 1, $newinfo);
				$ptr += count($sub)-1;
			}
			/* Only Composition-exclusion exceptions that we want to recompose. */
			if ($this->shaper == 'I') {
			  if ($char == 0x09AF && isset($this->OTLdata[$ptr + 1]) && $this->OTLdata[$ptr + 1]['uni'] == 0x09BC) { 
				$sub = 0x09DF; 
				$newinfo = array();
				$newinfo[0] = array();
				$ucd_record = UCDN::get_ucd_record($sub);
				$newinfo[0]['general_category'] = $ucd_record[0];
				$newinfo[0]['bidi_type'] = $ucd_record[2];
				$newinfo[0]['group'] =  'C';
				$newinfo[0]['uni'] =  $sub;
				$newinfo[0]['hex'] =  $this->unicode_hex($sub);
				array_splice($this->OTLdata, $ptr, 2, $newinfo);
			  }
			}
		}
		//-----------------------------------------------------------------------------------
		// b. Analyse characters - group as syllables/clusters (Indic); invalid diacritics; add dotted circle
		//-----------------------------------------------------------------------------------
		$indic_category_string = '';
		foreach($this->OTLdata AS $eid=>$c) {
			INDIC::set_indic_properties($this->OTLdata[$eid], $scriptblock );	// sets ['indic_category'] and ['indic_position']
			//$c['general_category']
			//$c['combining_class']
			//$c['uni'] =  $char;

			$indic_category_string .= INDIC::$indic_category_char[$this->OTLdata[$eid]['indic_category']];
		}

		$broken_syllables = false;
		if ($this->shaper == 'I') {
			INDIC::set_syllables($this->OTLdata, $indic_category_string, $broken_syllables);
		}
		else if ($this->shaper == 'S') {
			INDIC::set_syllables_sinhala($this->OTLdata, $indic_category_string, $broken_syllables);
		}
		else if ($this->shaper == 'K') {
			INDIC::set_syllables_khmer($this->OTLdata, $indic_category_string, $broken_syllables);
		}
		$indic_category_string = '';

		//-----------------------------------------------------------------------------------
		// c. Initial Re-ordering (Indic / Khmer / Sinhala)
		//-----------------------------------------------------------------------------------
		// Find base consonant
		// Decompose/compose and reorder Matras
		// Reorder marks to canonical order

		$indic_config = INDIC::$indic_configs[$scriptblock];
		$dottedcircle = false; 
		if ($broken_syllables) { 
			if ($this->mpdf->_charDefined($this->mpdf->fonts[$this->fontkey]['cw'],0x25CC) ) { 
				$dottedcircle = array();
				$ucd_record = UCDN::get_ucd_record(0x25CC);
				$dottedcircle[0]['general_category'] = $ucd_record[0];
				$dottedcircle[0]['bidi_type'] = $ucd_record[2];
				$dottedcircle[0]['group'] =  'C';
				$dottedcircle[0]['uni'] =  0x25CC;
				$dottedcircle[0]['indic_category'] = INDIC::OT_DOTTEDCIRCLE;
				$dottedcircle[0]['indic_position'] = INDIC::POS_BASE_C;

				$dottedcircle[0]['hex'] =  '025CC';		// TEMPORARY *****
			}
		}
		INDIC::initial_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $broken_syllables, $indic_config, $scriptblock, $is_old_spec, $dottedcircle);

		//-----------------------------------------------------------------------------------
		// d. Apply initial and basic shaping forms GSUB Lookups (one at a time)
		//-----------------------------------------------------------------------------------
		if ($this->shaper == 'I' || $this->shaper == 'S') {
			$tags = 'locl ccmp nukt akhn rphf rkrf pref blwf half pstf vatu cjct';
		}
		else if ($this->shaper == 'K') {
			$tags = 'locl ccmp pref blwf abvf pstf cfar';
		}
		$this->_applyGSUBrulesIndic($tags, $GSUBscriptTag, $GSUBlangsys, $is_old_spec);

		//-----------------------------------------------------------------------------------
		// e. Final Re-ordering (Indic / Khmer / Sinhala)
		//-----------------------------------------------------------------------------------
		// Reorder matras
		// Reorder reph
		// Reorder pre-base reordering consonants: 

		INDIC::final_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $indic_config, $scriptblock, $is_old_spec);

		//-----------------------------------------------------------------------------------
		// f. Apply 'init' feature to first syllable in word (indicated by ['mask']) INDIC::FLAG(INDIC::INIT);
		//-----------------------------------------------------------------------------------
		if ($this->shaper == 'I' || $this->shaper == 'S') {
			$tags = 'init';
			$this->_applyGSUBrulesIndic($tags, $GSUBscriptTag, $GSUBlangsys, $is_old_spec);
		}

		//-----------------------------------------------------------------------------------
		// g. Apply Presentation Forms GSUB Lookups (+ any discretionary)
		//-----------------------------------------------------------------------------------
		$tags = 'pres abvs blws psts haln rlig calt liga clig mset';

		$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
		$usetags = $tags;
		if(!empty($this->mpdf->OTLtags)) { 
			$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false) ;
		}
		if ($this->shaper == 'K') { 	// Features are applied one at a time, working through each codepoint
			$this->_applyGSUBrulesSingly($usetags, $GSUBscriptTag, $GSUBlangsys);
		}
		else {
			$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);
		}
		$this->restrictToSyllable = false;
	}


	// 5(M). GSUB - Shaper - MYANMAR (ONLY mym2)
	//==============================
	// NB Old style 'mymr' is left to go through the default shaper
	else if ($this->shaper == 'M') {
		$this->restrictToSyllable = true;
		//-----------------------------------------------------------------------------------
		// a. Analyse characters - group as syllables/clusters (Myanmar); invalid diacritics; add dotted circle
		//-----------------------------------------------------------------------------------
		$myanmar_category_string = '';
		foreach($this->OTLdata AS $eid=>$c) {
			MYANMAR::set_myanmar_properties($this->OTLdata[$eid]);	// sets ['myanmar_category'] and ['myanmar_position']
			$myanmar_category_string .= MYANMAR::$myanmar_category_char[$this->OTLdata[$eid]['myanmar_category']];
		}
		$broken_syllables = false;
		MYANMAR::set_syllables($this->OTLdata, $myanmar_category_string, $broken_syllables);
		$myanmar_category_string = '';

		//-----------------------------------------------------------------------------------
		// b. Re-ordering (Myanmar mym2)
		//-----------------------------------------------------------------------------------
		$dottedcircle = false; 
		if ($broken_syllables) { 
			if ($this->mpdf->_charDefined($this->mpdf->fonts[$this->fontkey]['cw'],0x25CC) ) { 
				$dottedcircle = array();
				$ucd_record = UCDN::get_ucd_record(0x25CC);
				$dottedcircle[0]['general_category'] = $ucd_record[0];
				$dottedcircle[0]['bidi_type'] = $ucd_record[2];
				$dottedcircle[0]['group'] =  'C';
				$dottedcircle[0]['uni'] =  0x25CC;
				$dottedcircle[0]['myanmar_category'] = MYANMAR::OT_DOTTEDCIRCLE;
				$dottedcircle[0]['myanmar_position'] = MYANMAR::POS_BASE_C;
				$dottedcircle[0]['hex'] =  '025CC';
			}
		}
		MYANMAR::reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $broken_syllables, $dottedcircle);

		//-----------------------------------------------------------------------------------
		// c. Apply initial and basic shaping forms GSUB Lookups (one at a time)
		//-----------------------------------------------------------------------------------

		$tags = 'locl ccmp rphf pref blwf pstf';
		$this->_applyGSUBrulesMyanmar($tags, $GSUBscriptTag, $GSUBlangsys);

		//-----------------------------------------------------------------------------------
		// d. Apply Presentation Forms GSUB Lookups (+ any discretionary)
		//-----------------------------------------------------------------------------------
		$tags = 'pres abvs blws psts haln rlig calt liga clig mset';
		$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
		$usetags = $tags;
		if(!empty($this->mpdf->OTLtags)) { 
			$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false) ;
		}
		$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);
		$this->restrictToSyllable = false;
	}


	// 5(E). GSUB - Shaper - SEA South East Asian (New Tai Lue, Cham, Tai Tam)
	//==============================
	else if ($this->shaper == 'E') {
      /* HarfBuzz says: If the designer designed the font for the 'DFLT' script,
       * use the default shaper.  Otherwise, use the SEA shaper.
       * Note that for some simple scripts, there may not be *any*
       * GSUB/GPOS needed, so there may be no scripts found! */

		$this->restrictToSyllable = true;
		//-----------------------------------------------------------------------------------
		// a. Analyse characters - group as syllables/clusters (Indic); invalid diacritics; add dotted circle
		//-----------------------------------------------------------------------------------
		$sea_category_string = '';
		foreach($this->OTLdata AS $eid=>$c) {
			SEA::set_sea_properties($this->OTLdata[$eid], $scriptblock );	// sets ['sea_category'] and ['sea_position']
			//$c['general_category']
			//$c['combining_class']
			//$c['uni'] =  $char;

			$sea_category_string .= SEA::$sea_category_char[$this->OTLdata[$eid]['sea_category']];
		}

		$broken_syllables = false;
		SEA::set_syllables($this->OTLdata, $sea_category_string, $broken_syllables);
		$sea_category_string = '';

		//-----------------------------------------------------------------------------------
		// b. Apply locl and ccmp shaping forms - before initial re-ordering; GSUB Lookups (one at a time)
		//-----------------------------------------------------------------------------------
		$tags = 'locl ccmp';
		$this->_applyGSUBrulesSingly($tags, $GSUBscriptTag, $GSUBlangsys);

		//-----------------------------------------------------------------------------------
		// c. Initial Re-ordering
		//-----------------------------------------------------------------------------------
		// Find base consonant
		// Decompose/compose and reorder Matras
		// Reorder marks to canonical order

		$dottedcircle = false; 
		if ($broken_syllables) { 
			if ($this->mpdf->_charDefined($this->mpdf->fonts[$this->fontkey]['cw'],0x25CC) ) { 
				$dottedcircle = array();
				$ucd_record = UCDN::get_ucd_record(0x25CC);
				$dottedcircle[0]['general_category'] = $ucd_record[0];
				$dottedcircle[0]['bidi_type'] = $ucd_record[2];
				$dottedcircle[0]['group'] =  'C';
				$dottedcircle[0]['uni'] =  0x25CC;
				$dottedcircle[0]['sea_category'] = SEA::OT_GB;
				$dottedcircle[0]['sea_position'] = SEA::POS_BASE_C;

				$dottedcircle[0]['hex'] =  '025CC';		// TEMPORARY *****
			}
		}
		SEA::initial_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $broken_syllables, $scriptblock, $dottedcircle);

		//-----------------------------------------------------------------------------------
		// d. Apply basic shaping forms GSUB Lookups (one at a time)
		//-----------------------------------------------------------------------------------
		$tags = 'pref abvf blwf pstf';
		$this->_applyGSUBrulesSingly($tags, $GSUBscriptTag, $GSUBlangsys);

		//-----------------------------------------------------------------------------------
		// e. Final Re-ordering
		//-----------------------------------------------------------------------------------

		SEA::final_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $scriptblock);

		//-----------------------------------------------------------------------------------
		// f. Apply Presentation Forms GSUB Lookups (+ any discretionary)
		//-----------------------------------------------------------------------------------
		$tags = 'pres abvs blws psts';

		$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
		$usetags = $tags;
		if(!empty($this->mpdf->OTLtags)) { 
			$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false) ;
		}
		$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);
		$this->restrictToSyllable = false;
	}


	// 5(D). GSUB - Shaper - DEFAULT (including THAI and LAO and MYANMAR v1 [mymr] and TIBETAN)
	//==============================
	else {	// DEFAULT
		//-----------------------------------------------------------------------------------
		// a. First decompose/compose in Thai / Lao - Tibetan
		//-----------------------------------------------------------------------------------
		// Decomposition for THAI or LAO
		/* This function implements the shaping logic documented here:
		 *
		 *   http://linux.thai.net/~thep/th-otf/shaping.html
		 *
		 * The first shaping rule listed there is needed even if the font has Thai
		 * OpenType tables. 
		 *
		 *
		 * The following is NOT specified in the MS OT Thai spec, however, it seems
		 * to be what Uniscribe and other engines implement.  According to Eric Muller:
		 *
		 * When you have a SARA AM, decompose it in NIKHAHIT + SARA AA, *and* move the
		 * NIKHAHIT backwards over any tone mark (0E48-0E4B).
		 *
		 * <0E14, 0E4B, 0E33> -> <0E14, 0E4D, 0E4B, 0E32>
		 *
		 * This reordering is legit only when the NIKHAHIT comes from a SARA AM, not
		 * when it's there to start with. The string <0E14, 0E4B, 0E4D> is probably
		 * not what a user wanted, but the rendering is nevertheless nikhahit above
		 * chattawa.
		 *
		 * Same for Lao.
		 *
		 *			Thai		Lao
		 * SARA AM:		U+0E33	U+0EB3
		 * SARA AA:		U+0E32	U+0EB2
		 * Nikhahit:	U+0E4D	U+0ECD
		 *
		 * Testing shows that Uniscribe reorder the following marks:
		 * Thai:	<0E31,0E34..0E37,0E47..0E4E>
		 * Lao:	<0EB1,0EB4..0EB7,0EC7..0ECE>
		 *
		 * Lao versions are the same as Thai + 0x80.
		 */
		if ($this->shaper == 'T' || $this->shaper == 'L') {
			for($ptr=0; $ptr<count($this->OTLdata); $ptr++) {
				$char = $this->OTLdata[$ptr]['uni'];
    				if (($char & ~0x0080) == 0x0E33) {	// if SARA_AM (U+0E33 or U+0EB3)

					$NIKHAHIT = $char + 0x1A;
					$SARA_AA = $char - 1;
					$sub = array($SARA_AA, $NIKHAHIT);

					$newinfo = array();
					$ucd_record = UCDN::get_ucd_record($sub[0]);
					$newinfo[0]['general_category'] = $ucd_record[0];
					$newinfo[0]['bidi_type'] = $ucd_record[2];
					$charasstr = $this->unicode_hex($sub[0]); 
					if (strpos($this->GlyphClassMarks, $charasstr)!==false) { $newinfo[0]['group'] =  'M'; }
					else { $newinfo[0]['group'] =  'C'; }
					$newinfo[0]['uni'] =  $sub[0];
					$newinfo[0]['hex'] =  $charasstr;
					$this->OTLdata[$ptr] = $newinfo[0];	// Substitute SARA_AM => SARA_AA

					$ntones = 0;	// number of (preceding) tone marks
					// IS_TONE_MARK ((x) & ~0x0080, 0x0E34 - 0x0E37, 0x0E47 - 0x0E4E, 0x0E31)
					while (isset($this->OTLdata[$ptr - 1 - $ntones]) 
						&& (
							($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) == 0x0E31 ||

							(($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) >= 0x0E34 &&
							($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) <= 0x0E37) ||

							(($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) >= 0x0E47 &&
							($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) <= 0x0E4E)
						)
					)  { $ntones++; }

					$newinfo = array();
					$ucd_record = UCDN::get_ucd_record($sub[1]);
					$newinfo[0]['general_category'] = $ucd_record[0];
					$newinfo[0]['bidi_type'] = $ucd_record[2];
					$charasstr = $this->unicode_hex($sub[1]); 
					if (strpos($this->GlyphClassMarks, $charasstr)!==false) { $newinfo[0]['group'] =  'M'; }
					else { $newinfo[0]['group'] =  'C'; }
					$newinfo[0]['uni'] =  $sub[1];
					$newinfo[0]['hex'] =  $charasstr;
					// Insert NIKAHIT
					array_splice($this->OTLdata, $ptr - $ntones, 0, $newinfo);

					$ptr++;
				}
			}
		}

		if ($scriptblock == UCDN::SCRIPT_TIBETAN) {
			// =========================
			// Reordering TIBETAN
			// =========================
			// Tibetan does not need to need a shaper generally, as long as characters are presented in the correct order
			// so we will do one minor change here:
           		// From ICU: If the present character is a number, and the next character is a pre-number combining mark
           		 // then the two characters are reordered
			// From MS OTL spec the following are Digit modifiers (Md): 0F18–0F19, 0F3E–0F3F
			// Digits: 0F20–0F33
			// On testing only 0x0F3F (pre-based mark) seems to need re-ordering
			for($ptr=0; $ptr<count($this->OTLdata)-1; $ptr++) {
    				if (INDIC::in_range($this->OTLdata[$ptr]['uni'], 0x0F20, 0x0F33) && $this->OTLdata[$ptr+1]['uni'] == 0x0F3F ) {
					$tmp = $this->OTLdata[$ptr+1];
					$this->OTLdata[$ptr+1] = $this->OTLdata[$ptr];
					$this->OTLdata[$ptr] = $tmp;
				}
			}


			// =========================
			// Decomposition for TIBETAN
			// =========================
/* Recommended, but does not seem to change anything...
			for($ptr=0; $ptr<count($this->OTLdata); $ptr++) {
				$char = $this->OTLdata[$ptr]['uni'];
				$sub = INDIC::decompose_indic($char);
				if ($sub) {
					$newinfo = array();
					for($i=0;$i<count($sub);$i++) {
						$newinfo[$i] = array();
						$ucd_record = UCDN::get_ucd_record($sub[$i]);
						$newinfo[$i]['general_category'] = $ucd_record[0];
						$newinfo[$i]['bidi_type'] = $ucd_record[2];
						$charasstr = $this->unicode_hex($sub[$i]); 
						if (strpos($this->GlyphClassMarks, $charasstr)!==false) { $newinfo[$i]['group'] =  'M'; }
						else { $newinfo[$i]['group'] =  'C'; }
						$newinfo[$i]['uni'] =  $sub[$i];
						$newinfo[$i]['hex'] =  $charasstr;
					}
					array_splice($this->OTLdata, $ptr, 1, $newinfo);
					$ptr += count($sub)-1;
				}
			}
*/

		}


		//-----------------------------------------------------------------------------------
		// b. Apply all GSUB Lookups (in order specified in lookup list)
		//-----------------------------------------------------------------------------------
		$tags = 'locl ccmp pref blwf abvf pstf pres abvs blws psts haln rlig calt liga clig mset  RQD';
		// pref blwf abvf pstf required for Tibetan
		// " RQD" is a non-standard tag in Garuda font - presumably intended to be used by default ? "ReQuireD"
		// Being a 3 letter tag is non-standard, and does not allow it to be set by font-feature-settings


		/* ?Add these until shapers witten?
		Hangul: 	ljmo vjmo tjmo
		*/

		$omittags = '';
		$useGSUBtags = $tags;
		if(!empty($this->mpdf->OTLtags)) { 
			$useGSUBtags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false) ;
		}
		// APPLY GSUB rules (as long as not Latin + SmallCaps - but not OTL smcp)
		if (!(($this->mpdf->textvar & FC_SMALLCAPS) && $scriptblock == UCDN::SCRIPT_LATIN && strpos($useGSUBtags, 'smcp')===false)) {
			$this->_applyGSUBrules($useGSUBtags, $GSUBscriptTag, $GSUBlangsys);
		}
	}


  }

	// Shapers - KHMER & THAI & LAO - Replace Word boundary marker with U+200B
	// Also TIBETAN (no shaper)
	//=======================================================
	if (($this->shaper == "K" || $this->shaper == "T" || $this->shaper == "L") || $scriptblock == UCDN::SCRIPT_TIBETAN ) {
		// Set up properties to insert a U+200B character
		$newinfo = array();
		//$newinfo[0] = array('general_category' => 1, 'bidi_type' => 14, 'group' => 'S', 'uni' => 0x200B, 'hex' => '0200B');
		$newinfo[0] = array(
			'general_category' => UCDN::UNICODE_GENERAL_CATEGORY_FORMAT, 
			'bidi_type' => UCDN::BIDI_CLASS_BN, 
			'group' => 'S', 'uni' => 0x200B, 'hex' => '0200B');
		// Then insert U+200B at (after) all word end boundaries
		for ($i=count($this->OTLdata)-1;$i>0;$i--) {
			// Make sure after GSUB that wordend has not been moved - check next char is not in the same syllable
			if (isset($this->OTLdata[$i]['wordend']) && $this->OTLdata[$i]['wordend'] &&
					isset($this->OTLdata[$i+1]['uni']) && (!isset($this->OTLdata[$i+1]['syllable']) || !isset($this->OTLdata[$i+1]['syllable']) || $this->OTLdata[$i+1]['syllable']!=$this->OTLdata[$i]['syllable'])) { 
				array_splice($this->OTLdata, $i+1, 0, $newinfo);
				$this->_updateLigatureMarks($i, 1);
			}
			else if ($this->OTLdata[$i]['uni']==0x2e) {	// Word end if Full-stop.
				array_splice($this->OTLdata, $i+1, 0, $newinfo);
				$this->_updateLigatureMarks($i, 1);
			}
		}
	}


	// Shapers - INDIC & ARABIC & KHMER & SINHALA  & MYANMAR - Remove ZWJ and ZWNJ
	//=======================================================
	if ($this->shaper == 'I' || $this->shaper == 'S' || $this->shaper == 'A' || $this->shaper == 'K' || $this->shaper == 'M') {
		// Remove ZWJ and ZWNJ
		for ($i=0;$i<count($this->OTLdata);$i++) {
			if ($this->OTLdata[$i]['uni']==8204 || $this->OTLdata[$i]['uni']==8205) {
				array_splice($this->OTLdata, $i, 1);
				$this->_updateLigatureMarks($i, -1);
			}
		}
	}

//print_r($this->OTLdata); echo '<br />';
//print_r($this->assocMarks);  echo '<br />';
//print_r($this->assocLigs); exit;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
//////////       GPOS          /////////////////////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

  if (($useOTL & 0xFF) && $GPOSscriptTag && $GPOSlangsys && $GPOSFeatures) {
	$this->Entry = array();
	$this->Exit = array();

	// 6. Load GPOS data, Coverage & Lookups
	//=================================================================
	if (!isset($this->GPOSdata[$this->fontkey])) {
		include(_MPDF_TTFONTDATAPATH.$this->mpdf->CurrentFont['fontkey'].'.GPOSdata.php'); 
		$this->LuCoverage = $this->GPOSdata[$this->fontkey]['LuCoverage'] = $LuCoverage;
	}
	else {
		$this->LuCoverage = $this->GPOSdata[$this->fontkey]['LuCoverage'];
	}

	$this->GPOSLookups = $this->mpdf->CurrentFont['GPOSLookups'];


	// 7. Select Feature tags to use (incl optional)
	//==============================
	$tags = 'abvm blwm mark mkmk curs cpsp dist requ';	// Default set
	/* 'requ' is not listed in the Microsoft registry of Feature tags
		Found in Arial Unicode MS, it repositions the baseline for punctuation in Kannada script */

	// ZZZ96
	// Set kern to be included by default in non-Latin script (? just when shapers used)
	// Kern is used in some fonts to reposition marks etc. and is essential for correct display
	//if ($this->shaper) {$tags .= ' kern'; }
	if ($scriptblock != UCDN::SCRIPT_LATIN) { $tags .= ' kern'; }

	$omittags = '';
	$usetags = $tags;
	if(!empty($this->mpdf->OTLtags)) { 
		$usetags = $this->_applyTagSettings($tags, $GPOSFeatures, $omittags, false) ;
	}



	// 8. Get GPOS LookupList from Feature tags
	//==============================
	$LookupList = array();
	foreach($GPOSFeatures AS $tag=>$arr) {
		if (strpos($usetags, $tag)!==false) {
			foreach($arr AS $lu) { $LookupList[$lu] = $tag; }
		}
	}
	ksort($LookupList);


	// 9. Apply GPOS Lookups (in order specified in lookup list but selecting from specified tags)
	//==============================

	// APPLY THE GPOS RULES (as long as not Latin + SmallCaps - but not OTL smcp)
	if (!(($this->mpdf->textvar & FC_SMALLCAPS) && $scriptblock == UCDN::SCRIPT_LATIN && strpos($useGSUBtags, 'smcp')===false)) {
		$this->_applyGPOSrules($LookupList, $is_old_spec);
		// (sets: $this->OTLdata[n]['GPOSinfo'] XPlacement YPlacement XAdvance Entry Exit )
	}

	// 10. Process cursive text
	//==============================
	if (count($this->Entry) || count($this->Exit)) {
		// RTL
		$incurs = false;
		for ($i=(count($this->OTLdata)-1);$i>=0;$i--) {
			if (isset($this->Entry[$i]) && isset($this->Entry[$i]['Y']) && $this->Entry[$i]['dir']=='RTL') {
				$nextbase = $i-1;	// Set as next base ignoring marks (next base reading RTL in logical oder
				while(isset($this->OTLdata[$nextbase]['hex']) && strpos($this->GlyphClassMarks, $this->OTLdata[$nextbase]['hex'])!==false) { $nextbase--; }
				if (isset($this->Exit[$nextbase]) && isset($this->Exit[$nextbase]['Y']) ) {
					$diff = $this->Entry[$i]['Y'] - $this->Exit[$nextbase]['Y'];
					if ($incurs===false) { $incurs = $diff; }
					else { $incurs += $diff; }
					for ($j=($i-1);$j>=$nextbase;$j--) {
						if (isset($this->OTLdata[$j]['GPOSinfo']['YPlacement'])) { $this->OTLdata[$j]['GPOSinfo']['YPlacement'] += $incurs; }
						else { $this->OTLdata[$j]['GPOSinfo']['YPlacement'] = $incurs; }
					}
					if (isset($this->Exit[$i]['X']) && isset($this->Entry[$nextbase]['X']) ) {
						$adj = -($this->Entry[$i]['X'] - $this->Exit[$nextbase]['X']);
						// If XAdvance is aplied - in order for PDF to position the Advance correctly need to place it on:
						// in RTL - the current glyph or the last of any associated marks
						if (isset($this->OTLdata[$nextbase+1]['GPOSinfo']['XAdvance'])) { $this->OTLdata[$nextbase+1]['GPOSinfo']['XAdvance'] += $adj; }
						else { $this->OTLdata[$nextbase+1]['GPOSinfo']['XAdvance'] = $adj; }
					}
				}
				else { $incurs = false; }
			}
			else if (strpos($this->GlyphClassMarks, $this->OTLdata[$i]['hex'])!==false) { continue; } // ignore Marks
			else { $incurs = false; }
		}
		// LTR
		$incurs = false;
		for ($i=0;$i<count($this->OTLdata);$i++) {
			if (isset($this->Exit[$i]) && isset($this->Exit[$i]['Y']) && $this->Exit[$i]['dir']=='LTR') {
				$nextbase = $i+1;	// Set as next base ignoring marks
				while(strpos($this->GlyphClassMarks, $this->OTLdata[$nextbase]['hex'])!==false) { $nextbase++; }
				if (isset($this->Entry[$nextbase]) && isset($this->Entry[$nextbase]['Y']) ) {

					$diff = $this->Exit[$i]['Y'] - $this->Entry[$nextbase]['Y'];
					if ($incurs===false) { $incurs = $diff; }
					else { $incurs += $diff; }
					for ($j=($i+1);$j<=$nextbase;$j++) {
						if (isset($this->OTLdata[$j]['GPOSinfo']['YPlacement'])) { $this->OTLdata[$j]['GPOSinfo']['YPlacement'] += $incurs; }
						else { $this->OTLdata[$j]['GPOSinfo']['YPlacement'] = $incurs; }
					}
					if (isset($this->Exit[$i]['X']) && isset($this->Entry[$nextbase]['X']) ) {
						$adj = -($this->Exit[$i]['X'] - $this->Entry[$nextbase]['X']);
						// If XAdvance is aplied - in order for PDF to position the Advance correctly need to place it on:
						// in LTR - the next glyph, ignoring marks
						if (isset($this->OTLdata[$nextbase]['GPOSinfo']['XAdvance'])) { $this->OTLdata[$nextbase]['GPOSinfo']['XAdvance'] += $adj; }
						else { $this->OTLdata[$nextbase]['GPOSinfo']['XAdvance'] = $adj; }
					}
				}
				else { $incurs = false; }
			}
			else if (strpos($this->GlyphClassMarks, $this->OTLdata[$i]['hex'])!==false) { continue; } // ignore Marks
			else { $incurs = false; }
		}
	}




  }	// end GPOS

	if ($this->debugOTL) { $this->_dumpproc('END', '-', '-', '-', '-', 0, '-', 0); exit; }

	$this->schOTLdata[$sch] = $this->OTLdata;
	$this->OTLdata = array();
}	// END foreach subchunk


	// 11. Re-assemble and return text string
	//==============================
	$newGPOSinfo = array();
	$newOTLdata = array();
	$newchar_data = array();
	$newgroup = '';
	$e = '';
	$ectr = 0;

	for($sch=0;$sch<=$subchunk;$sch++) {
		for ($i=0;$i<count($this->schOTLdata[$sch]);$i++) {
			if (isset($this->schOTLdata[$sch][$i]['GPOSinfo'])) {
				$newGPOSinfo[$ectr] = $this->schOTLdata[$sch][$i]['GPOSinfo'];
			}
			$newchar_data[$ectr] = array('bidi_class' => $this->schOTLdata[$sch][$i]['bidi_type'], 'uni' => $this->schOTLdata[$sch][$i]['uni']);
			$newgroup .= $this->schOTLdata[$sch][$i]['group'];
			$e.=code2utf($this->schOTLdata[$sch][$i]['uni']);
			if (isset($this->mpdf->CurrentFont['subset'])) {
				$this->mpdf->CurrentFont['subset'][$this->schOTLdata[$sch][$i]['uni']] = $this->schOTLdata[$sch][$i]['uni'];
			}
			$ectr++;
		}

	}
	$this->OTLdata['GPOSinfo'] = $newGPOSinfo;
	$this->OTLdata['char_data'] = $newchar_data ;
	$this->OTLdata['group'] = $newgroup ;


	// This leaves OTLdata::GPOSinfo, ::bidi_type, & ::group

	return $e;

}

function _applyTagSettings($tags, $Features, $omittags='', $onlytags=false) {
		if (empty($this->mpdf->OTLtags['Plus']) && empty($this->mpdf->OTLtags['Minus']) && empty($this->mpdf->OTLtags['FFPlus']) && empty($this->mpdf->OTLtags['FFMinus'])) { return $tags; }

		// Use $tags as starting point
		$usetags = $tags;

		// Only set / unset tags which are in the font
		// Ignore tags which are in $omittags
		// If $onlytags, then just unset tags which are already in the Tag list

		$fp = $fm = $ffp = $ffm = '';

		// Font features to enable - set by font-variant-xx
		if (isset($this->mpdf->OTLtags['Plus'])) $fp = $this->mpdf->OTLtags['Plus'];
		preg_match_all('/([a-zA-Z0-9]{4})/',$fp,$m);
		for($i=0;$i<count($m[0]);$i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if(isset($Features[$t]) && strpos($omittags,$t)===false && (!$onlytags || strpos($tags,$t)!==false )) {
				$usetags .= ' '.$t;
			}
		}

		// Font features to disable - set by font-variant-xx
		if (isset($this->mpdf->OTLtags['Minus'])) $fm = $this->mpdf->OTLtags['Minus'];
		preg_match_all('/([a-zA-Z0-9]{4})/',$fm,$m);
		for($i=0;$i<count($m[0]);$i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if(isset($Features[$t]) && strpos($omittags,$t)===false && (!$onlytags || strpos($tags,$t)!==false )) {
				$usetags = str_replace($t,'',$usetags);
			}
		}

		// Font features to enable - set by font-feature-settings
		if (isset($this->mpdf->OTLtags['FFPlus'])) $ffp = $this->mpdf->OTLtags['FFPlus'];	// Font Features - may include integer: salt4
		preg_match_all('/([a-zA-Z0-9]{4})([\d+]*)/',$ffp,$m);
		for($i=0;$i<count($m[0]);$i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if(isset($Features[$t]) && strpos($omittags,$t)===false && (!$onlytags || strpos($tags,$t)!==false )) {
				$usetags .= ' '.$m[0][$i];		//  - may include integer: salt4
			}
		}

		// Font features to disable - set by font-feature-settings
		if (isset($this->mpdf->OTLtags['FFMinus'])) $ffm = $this->mpdf->OTLtags['FFMinus'];
		preg_match_all('/([a-zA-Z0-9]{4})/',$ffm,$m);
		for($i=0;$i<count($m[0]);$i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if(isset($Features[$t]) && strpos($omittags,$t)===false && (!$onlytags || strpos($tags,$t)!==false )) {
				$usetags = str_replace($t,'',$usetags);
			}
		}
		return $usetags; 
}

function _applyGSUBrules($usetags, $scriptTag, $langsys) {
	// Features from all Tags are applied together, in Lookup List order.
	// For Indic - should be applied one syllable at a time	
	// - Implemented in functions checkContextMatch and checkContextMatchMultiple by failing to match if outside scope of current 'syllable'
	// if $this->restrictToSyllable is true

	$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];
	$LookupList = array();
	foreach($GSUBFeatures AS $tag=>$arr) {
		if (strpos($usetags, $tag)!==false) {
			foreach($arr AS $lu) { $LookupList[$lu] = $tag; }
		}
	}
	ksort($LookupList);

	foreach($LookupList AS $lu=>$tag) {
		$Type = $this->GSUBLookups[$lu]['Type'];
		$Flag = $this->GSUBLookups[$lu]['Flag'];
		$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
		$tagInt = 1;
		if (preg_match('/'.$tag.'([0-9]{1,2})/', $usetags, $m)) {
			$tagInt = $m[1];
		}
		$ptr = 0;
		// Test each glyph sequentially
		while($ptr < (count($this->OTLdata))) {	// whilst there is another glyph ..0064
			$currGlyph = $this->OTLdata[$ptr]['hex'];
			$currGID = $this->OTLdata[$ptr]['uni'];
			$shift = 1;
			foreach($this->GSUBLookups[$lu]['Subtables'] AS $c=>$subtable_offset) {
				// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
				if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
					// Get rules from font GSUB subtable
					$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $tag, 0, $tagInt);

					if ($shift) { break; }
				}
			}
			if ($shift == 0) { $shift = 1; }
			$ptr += $shift;

		}
	}
}

function _applyGSUBrulesSingly($usetags, $scriptTag, $langsys) {
	// Features are applied one at a time, working through each codepoint

	$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];

	$tags = explode(' ',$usetags);
	foreach($tags AS $usetag) {
		$LookupList = array();
		foreach($GSUBFeatures AS $tag=>$arr) {
			if (strpos($usetags, $tag)!==false) {
				foreach($arr AS $lu) { $LookupList[$lu] = $tag; }
			}
		}
		ksort($LookupList);

		$ptr = 0;
		// Test each glyph sequentially
		while($ptr < (count($this->OTLdata))) {	// whilst there is another glyph ..0064
			$currGlyph = $this->OTLdata[$ptr]['hex'];
			$currGID = $this->OTLdata[$ptr]['uni'];
			$shift = 1;

			foreach($LookupList AS $lu=>$tag) {
				$Type = $this->GSUBLookups[$lu]['Type'];
				$Flag = $this->GSUBLookups[$lu]['Flag'];
				$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
				$tagInt = 1;
				if (preg_match('/'.$tag.'([0-9]{1,2})/', $usetags, $m)) {
					$tagInt = $m[1];
				}

				foreach($this->GSUBLookups[$lu]['Subtables'] AS $c=>$subtable_offset) {
					// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
					if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
						// Get rules from font GSUB subtable
						$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $tag, 0, $tagInt);

						if ($shift) { break 2; }
					}
				}
			}
			if ($shift == 0) { $shift = 1; }
			$ptr += $shift;

		}
	}
}

function _applyGSUBrulesMyanmar($usetags, $scriptTag, $langsys) {
	// $usetags = locl ccmp rphf pref blwf pstf';
	// applied to all characters

	$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];

	// ALL should be applied one syllable at a time	
	// Implemented in functions checkContextMatch and checkContextMatchMultiple by failing to match if outside scope of current 'syllable'
	$tags = explode(' ',$usetags);
	foreach($tags AS $usetag) {

		$LookupList = array();
		foreach($GSUBFeatures AS $tag=>$arr) {
			if ($tag==$usetag) {
				foreach($arr AS $lu) { $LookupList[$lu] = $tag; }
			}
		}
		ksort($LookupList);

		foreach($LookupList AS $lu=>$tag) {

			$Type = $this->GSUBLookups[$lu]['Type'];
			$Flag = $this->GSUBLookups[$lu]['Flag'];
			$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
			$tagInt = 1;
			if (preg_match('/'.$tag.'([0-9]{1,2})/', $usetags, $m)) {
				$tagInt = $m[1];
			}

			$ptr = 0;
			// Test each glyph sequentially
			while($ptr < (count($this->OTLdata))) {	// whilst there is another glyph ..0064
				$currGlyph = $this->OTLdata[$ptr]['hex'];
				$currGID = $this->OTLdata[$ptr]['uni'];
				$shift = 1;
				foreach($this->GSUBLookups[$lu]['Subtables'] AS $c=>$subtable_offset) {
					// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
					if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
						// Get rules from font GSUB subtable
						$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $usetag, 0, $tagInt);

						if ($shift) { break; }
					}
				}
				if ($shift == 0) { $shift = 1; }
				$ptr += $shift;

			}
		}
	}
}

function _applyGSUBrulesIndic($usetags, $scriptTag, $langsys, $is_old_spec) {
	// $usetags = 'locl ccmp nukt akhn rphf rkrf pref blwf half pstf vatu cjct'; then later - init
	// rphf, pref, blwf, half, abvf, pstf, and init are only applied where ['mask'] indicates:  INDIC::FLAG(INDIC::RPHF);
	// The rest are applied to all characters

	$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];

	// ALL should be applied one syllable at a time	
	// Implemented in functions checkContextMatch and checkContextMatchMultiple by failing to match if outside scope of current 'syllable'
	$tags = explode(' ',$usetags);
	foreach($tags AS $usetag) {

		$LookupList = array();
		foreach($GSUBFeatures AS $tag=>$arr) {
			if ($tag==$usetag) {
				foreach($arr AS $lu) { $LookupList[$lu] = $tag; }
			}
		}
		ksort($LookupList);

		foreach($LookupList AS $lu=>$tag) {

			$Type = $this->GSUBLookups[$lu]['Type'];
			$Flag = $this->GSUBLookups[$lu]['Flag'];
			$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
			$tagInt = 1;
			if (preg_match('/'.$tag.'([0-9]{1,2})/', $usetags, $m)) {
				$tagInt = $m[1];
			}

			$ptr = 0;
			// Test each glyph sequentially
			while($ptr < (count($this->OTLdata))) {	// whilst there is another glyph ..0064
				$currGlyph = $this->OTLdata[$ptr]['hex'];
				$currGID = $this->OTLdata[$ptr]['uni'];
				$shift = 1;
				foreach($this->GSUBLookups[$lu]['Subtables'] AS $c=>$subtable_offset) {
					// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
					if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
						if (strpos('rphf pref blwf half pstf cfar init' , $usetag)!==false) { // only apply when mask indicates
							$mask = 0;
							switch ($usetag) {
								case 'rphf': $mask = (1<<(INDIC::RPHF)); break;
								case 'pref': $mask = (1<<(INDIC::PREF)); break;
								case 'blwf': $mask = (1<<(INDIC::BLWF)); break;
								case 'half': $mask = (1<<(INDIC::HALF)); break;
								case 'pstf': $mask = (1<<(INDIC::PSTF)); break;
								case 'cfar': $mask = (1<<(INDIC::CFAR)); break;
								case 'init': $mask = (1<<(INDIC::INIT)); break;
							}
							if (!($this->OTLdata[$ptr]['mask'] & $mask)) { continue; }
						}
						// Get rules from font GSUB subtable
						$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $usetag, $is_old_spec, $tagInt);

						if ($shift) { break; }
					}

					// Special case for Indic  ZZZ99S
					// Check to substitute Halant-Consonant in PREF, BLWF or PSTF
					// i.e. new spec but GSUB tables have Consonant-Halant in Lookups e.g. FreeSerif, which
					// incorrectly just moved old spec tables to new spec. Uniscribe seems to cope with this
					// See also ttffontsuni.php
					// First check if current glyph is a Halant/Virama
					else if (_OTL_OLD_SPEC_COMPAT_1 && $Type==4 && !$is_old_spec && strpos('0094D 009CD 00A4D 00ACD 00B4D 00BCD 00C4D 00CCD 00D4D',$currGlyph)!== false) {
						 // only apply when 'pref blwf pstf' tags, and when mask indicates
						if (strpos('pref blwf pstf' , $usetag)!==false) {
							$mask = 0;
							switch ($usetag) {
								case 'pref': $mask = (1<<(INDIC::PREF)); break;
								case 'blwf': $mask = (1<<(INDIC::BLWF)); break;
								case 'pstf': $mask = (1<<(INDIC::PSTF)); break;
							}
							if (!($this->OTLdata[$ptr]['mask'] & $mask)) { continue; }

							$nextGlyph = $this->OTLdata[$ptr+1]['hex'];
							$nextGID = $this->OTLdata[$ptr+1]['uni'];
							if (isset($this->GSLuCoverage[$lu][$c][$nextGID])) {

								// Get rules from font GSUB subtable
								$shift = $this->_applyGSUBsubtableSpecial($lu, $c, $ptr, $currGlyph, $currGID, $nextGlyph, $nextGID, ($subtable_offset - $this->GSUB_offset), $Type, $this->GSLuCoverage[$lu][$c]);

								if ($shift) { break; }
							}
						}
					}


				}
				if ($shift == 0) { $shift = 1; }
				$ptr += $shift;

			}
		}
	}
}


function _applyGSUBsubtableSpecial($lookupID, $subtable, $ptr, $currGlyph, $currGID, $nextGlyph, $nextGID, $subtable_offset, $Type, $LuCoverage) {

	// Special case for Indic
	// Check to substitute Halant-Consonant in PREF, BLWF or PSTF
	// i.e. new spec but GSUB tables have Consonant-Halant in Lookups e.g. FreeSerif, which
	// incorrectly just moved old spec tables to new spec. Uniscribe seems to cope with this
	// See also ttffontsuni.php

	$this->seek($subtable_offset);
	$SubstFormat= $this->read_ushort();

	// Subtable contains Consonant - Halant
	// Text string contains Halant ($CurrGlyph) - Consonant ($nextGlyph)
	// Halant has already been matched, and already checked that $nextGID is in Coverage table

	////////////////////////////////////////////////////////////////////////////////
	// Only does: LookupType 4: Ligature Substitution Subtable : n to 1
	////////////////////////////////////////////////////////////////////////////////
	$Coverage = $subtable_offset + $this->read_ushort();
	$NextGlyphPos = $LuCoverage[$nextGID];
	$LigSetCount = $this->read_short();

	$this->skip($NextGlyphPos * 2);
	$LigSet = $subtable_offset + $this->read_short();

	$this->seek($LigSet);
	$LigCount = $this->read_short();
	// LigatureSet i.e. all starting with the same Glyph $nextGlyph [Consonant]
	$LigatureOffset = array();
	for ($g=0;$g<$LigCount;$g++) { 
		$LigatureOffset[$g] = $LigSet + $this->read_ushort();
	}
	for ($g=0;$g<$LigCount;$g++) { 
		// Ligature tables
		$this->seek($LigatureOffset[$g]);
		$LigGlyph = $this->read_ushort();
		$substitute = $this->glyphToChar($LigGlyph);
		$CompCount = $this->read_ushort();

		if ($CompCount != 2) { return 0; }	// Only expecting to work with 2:1 (and no ignore characters in between)


		$gid = $this->read_ushort();
		$checkGlyph = $this->glyphToChar($gid); // Other component/input Glyphs starting at position 2 (arrayindex 1)

		if ($currGID == $checkGlyph) { $match = true; }
		else { $match = false; break; }

		$GlyphPos = array();
		$GlyphPos[] = $ptr;
		$GlyphPos[] = $ptr+1;


		if ($match) {
			$shift = $this->GSUBsubstitute($ptr, $substitute, 4, $GlyphPos );	// GlyphPos contains positions to set null
			if ($shift) return 1;
		}

	}
	return 0;
}

function _applyGSUBsubtable($lookupID, $subtable, $ptr, $currGlyph, $currGID, $subtable_offset, $Type, $Flag, $MarkFilteringSet, $LuCoverage, $level=0, $currentTag, $is_old_spec, $tagInt) {
	$ignore = $this->_getGCOMignoreString($Flag, $MarkFilteringSet);

	// Lets start
	$this->seek($subtable_offset);
	$SubstFormat= $this->read_ushort();

	////////////////////////////////////////////////////////////////////////////////
	// LookupType 1: Single Substitution Subtable : 1 to 1
	////////////////////////////////////////////////////////////////////////////////
	if ($Type == 1) {
		// Flag = Ignore
		if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) { return 0; }
		$CoverageOffset = $subtable_offset + $this->read_ushort();
		$GlyphPos = $LuCoverage[$currGID];
		//===========
		// Format 1: 
		//===========
		if ($SubstFormat==1) {	// Calculated output glyph indices
			$DeltaGlyphID = $this->read_short();
			$this->seek($CoverageOffset);
			$glyphs = $this->_getCoverageGID();
			$GlyphID = $glyphs[$GlyphPos] + $DeltaGlyphID;
		}
		//===========
		// Format 2: 
		//===========
		else if ($SubstFormat==2) {	// Specified output glyph indices
			$GlyphCount = $this->read_ushort();
			$this->skip($GlyphPos * 2 );
			$GlyphID = $this->read_ushort();
		}

		$substitute = $this->glyphToChar($GlyphID);
		$shift = $this->GSUBsubstitute($ptr, $substitute, $Type );
		if ($this->debugOTL && $shift) { $this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level); }
		if ($shift) return 1;
		return 0;
	}

	////////////////////////////////////////////////////////////////////////////////
	// LookupType 2: Multiple Substitution Subtable : 1 to n
	////////////////////////////////////////////////////////////////////////////////
	else if ($Type == 2) {
		// Flag = Ignore
		if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) { return 0; }
		$Coverage = $subtable_offset + $this->read_ushort();
		$GlyphPos = $LuCoverage[$currGID];
		$this->skip(2);
		$this->skip($GlyphPos * 2);
		$Sequences = $subtable_offset + $this->read_short();

		$this->seek($Sequences);
		$GlyphCount = $this->read_short();
		$SubstituteGlyphs = array();
		for ($g=0;$g<$GlyphCount;$g++) { 
			$sgid = $this->read_ushort();
			$SubstituteGlyphs[] = $this->glyphToChar($sgid);
		}

		$shift = $this->GSUBsubstitute($ptr, $SubstituteGlyphs, $Type  );
		if ($this->debugOTL && $shift) { $this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level); }
		if ($shift) return $shift;
		return 0;
	}
	////////////////////////////////////////////////////////////////////////////////
	// LookupType 3: Alternate Forms : 1 to 1(n)
	////////////////////////////////////////////////////////////////////////////////
	else if ($Type == 3) {
 		// Flag = Ignore
		if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) { return 0; }
		$Coverage = $subtable_offset + $this->read_ushort();
		$AlternateSetCount = $this->read_short();
		///////////////////////////////////////////////////////////////////////////////!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		// Need to set alternate IF set by CSS3 font-feature for a tag
		// i.e. if this is 'salt' alternate may be set to 2
		// default value will be $alt=1 ( === index of 0 in list of alternates)
		$alt = 1;	// $alt=1 points to Alternative[0]
		if ($tagInt>1) { $alt = $tagInt; }
		///////////////////////////////////////////////////////////////////////////////!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		if ($alt == 0) { return 0; }	// If specified alternate not present, cancel [ or could default $alt = 1 ?]

		$GlyphPos = $LuCoverage[$currGID];
		$this->skip($GlyphPos * 2);

		$AlternateSets = $subtable_offset + $this->read_short();
		$this->seek($AlternateSets );

		$AlternateGlyphCount = $this->read_short();
		if ($alt > $AlternateGlyphCount) { return 0; }	// If specified alternate not present, cancel [ or could default $alt = 1 ?]

		$this->skip(($alt-1) * 2);
		$GlyphID = $this->read_ushort();

		$substitute = $this->glyphToChar($GlyphID);
		$shift = $this->GSUBsubstitute($ptr, $substitute, $Type );
		if ($this->debugOTL && $shift) { $this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level); }
		if ($shift) return 1;
		return 0;
	}
	////////////////////////////////////////////////////////////////////////////////
	// LookupType 4: Ligature Substitution Subtable : n to 1
	////////////////////////////////////////////////////////////////////////////////
	else if ($Type == 4) {
		// Flag = Ignore
		if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) { return 0; }
		$Coverage = $subtable_offset + $this->read_ushort();
		$FirstGlyphPos = $LuCoverage[$currGID];

		$LigSetCount = $this->read_short();

		$this->skip($FirstGlyphPos * 2);
		$LigSet = $subtable_offset + $this->read_short();

		$this->seek($LigSet);
		$LigCount = $this->read_short();
		// LigatureSet i.e. all starting with the same first Glyph $currGlyph
		$LigatureOffset = array();
		for ($g=0;$g<$LigCount;$g++) { 
			$LigatureOffset[$g] = $LigSet + $this->read_ushort();
		}
		for ($g=0;$g<$LigCount;$g++) { 
			// Ligature tables
			$this->seek($LigatureOffset[$g]);
			$LigGlyph = $this->read_ushort();	// Output Ligature GlyphID
			$substitute = $this->glyphToChar($LigGlyph);
			$CompCount = $this->read_ushort();

			$spos = $ptr;
			$match = true; 
			$GlyphPos = array();
			$GlyphPos[] = $spos;
			for ($l=1;$l<$CompCount;$l++) { 
				$gid = $this->read_ushort();
				$checkGlyph = $this->glyphToChar($gid); // Other component/input Glyphs starting at position 2 (arrayindex 1)

				$spos++;
				//while $this->OTLdata[$spos]['uni'] is an "ignore" =>  spos++
				while (isset($this->OTLdata[$spos]) && strpos($ignore, $this->OTLdata[$spos]['hex'])!==false) { $spos++; }

				if (isset($this->OTLdata[$spos]) && $this->OTLdata[$spos]['uni'] == $checkGlyph) {
					$GlyphPos[] = $spos;
				}
				else { $match = false; break; }

			}


			if ($match) {
				$shift = $this->GSUBsubstitute($ptr, $substitute, $Type, $GlyphPos );	// GlyphPos contains positions to set null
				if ($this->debugOTL && $shift) { $this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level); }
				if ($shift) return ($spos-$ptr+1-($CompCount-1));
			}

		}
		return 0;
	}

	////////////////////////////////////////////////////////////////////////////////
	// LookupType 5: Contextual Substitution Subtable
	////////////////////////////////////////////////////////////////////////////////
	else if ($Type == 5) {
		//===========
		// Format 1: Simple Context Glyph Substitution
		//===========
		if ($SubstFormat==1) {
			$CoverageTableOffset = $subtable_offset + $this->read_ushort();
			$SubRuleSetCount = $this->read_ushort();
			$SubRuleSetOffset = array();
			for ($b=0;$b<$SubRuleSetCount;$b++) {
				$offset = $this->read_ushort();
				if ($offset==0x0000) {
					$SubRuleSetOffset[] = $offset;
				}
				else {
					$SubRuleSetOffset[] = $subtable_offset + $offset;
				}
			}

			// SubRuleSet tables: All contexts beginning with the same glyph
			// Select the SubRuleSet required using the position of the glyph in the coverage table
			$GlyphPos = $LuCoverage[$currGID];
			if ($SubRuleSetOffset[$GlyphPos]>0) {
					$this->seek($SubRuleSetOffset[$GlyphPos]);
					$SubRuleCnt = $this->read_ushort();
					$SubRule = array();
					for($b=0;$b<$SubRuleCnt;$b++) {
						$SubRule[$b] = $SubRuleSetOffset[$GlyphPos]+$this->read_ushort();
					}
					for($b=0;$b<$SubRuleCnt;$b++) {		// EACH RULE
						$this->seek($SubRule[$b]);
						$InputGlyphCount = $this->read_ushort();
						$SubstCount = $this->read_ushort();

						$Backtrack = array();
						$Lookahead = array();
						$Input = array();
						$Input[0] = $this->OTLdata[$ptr]['uni'];
						for ($r=1;$r<$InputGlyphCount;$r++) {
							$gid = $this->read_ushort();
							$Input[$r] = $this->glyphToChar($gid);
						}
						$matched = $this->checkContextMatch($Input, $Backtrack, $Lookahead, $ignore, $ptr);
						if ($matched) {
							if ($this->debugOTL) { $this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level); }
							for ($p=0;$p<$SubstCount;$p++) {	// EACH LOOKUP
								$SequenceIndex[$p] = $this->read_ushort();
								$LookupListIndex[$p] = $this->read_ushort();
							}

							for ($p=0;$p<$SubstCount;$p++) {
								// Apply  $LookupListIndex  at   $SequenceIndex
								if ($SequenceIndex[$p] >= $InputGlyphCount) { continue; }
								$lu = $LookupListIndex[$p];
								$luType = $this->GSUBLookups[$lu]['Type'];
								$luFlag = $this->GSUBLookups[$lu]['Flag'];
								$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

								$luptr = $matched[$SequenceIndex[$p]];
								$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
								$lucurrGID = $this->OTLdata[$luptr]['uni'];

								foreach($this->GSUBLookups[$lu]['Subtables'] AS $luc=>$lusubtable_offset) {
									$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset) , $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
									if ($shift) { break; }
								}
							}

							if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) { return $shift ; }	/* OTL_FIX_3 */
							else return $InputGlyphCount ;	// should be + matched ignores in Input Sequence

						}
					}

			}
			return 0;
		}

		//===========
		// Format 2: 
		//===========
		// Format 2: Class-based Context Glyph Substitution
		else if ($SubstFormat==2) {

			$CoverageTableOffset = $subtable_offset + $this->read_ushort();
			$InputClassDefOffset = $subtable_offset + $this->read_ushort();
			$SubClassSetCnt = $this->read_ushort();
			$SubClassSetOffset = array();
			for ($b=0;$b<$SubClassSetCnt;$b++) {
				$offset = $this->read_ushort();
				if ($offset==0x0000) {
					$SubClassSetOffset[] = $offset;
				}
				else {
					$SubClassSetOffset[] = $subtable_offset + $offset;
				}
			}

			$InputClasses = $this->_getClasses($InputClassDefOffset);

			for ($s=0;$s<$SubClassSetCnt;$s++) {	// $SubClassSet is ordered by input class-may be NULL
				// Select $SubClassSet if currGlyph is in First Input Class
				if ($SubClassSetOffset[$s]>0 && isset($InputClasses[$s][$currGID])) {
					$this->seek($SubClassSetOffset[$s]);
					$SubClassRuleCnt = $this->read_ushort();
					$SubClassRule = array();
					for($b=0;$b<$SubClassRuleCnt;$b++) {
						$SubClassRule[$b] = $SubClassSetOffset[$s]+$this->read_ushort();
					}

					for($b=0;$b<$SubClassRuleCnt;$b++) {		// EACH RULE
						$this->seek($SubClassRule[$b]);
						$InputGlyphCount = $this->read_ushort();
						$SubstCount = $this->read_ushort();
						$Input = array();
						for ($r=1;$r<$InputGlyphCount;$r++) {
							$Input[$r] = $this->read_ushort();
						}

						$inputClass = $s;	

						$inputGlyphs = array();
						$inputGlyphs[0] = $InputClasses[$inputClass];

						if ($InputGlyphCount>1) {
							//  NB starts at 1 
							for ($gcl=1;$gcl<$InputGlyphCount;$gcl++) {
								$classindex = $Input[$gcl];
								if (isset($InputClasses[$classindex])) { $inputGlyphs[$gcl] = $InputClasses[$classindex]; }
								else { $inputGlyphs[$gcl] = ''; }
							}
						}

						// Class 0 contains all the glyphs NOT in the other classes
						$class0excl = array();
						for ($gc=1;$gc<=count($InputClasses);$gc++) {
							if (is_array($InputClasses[$gc])) $class0excl = $class0excl + $InputClasses[$gc];
						}

						$backtrackGlyphs = array();
						$lookaheadGlyphs = array();

						$matched = $this->checkContextMatchMultipleUni($inputGlyphs, $backtrackGlyphs, $lookaheadGlyphs, $ignore, $ptr, $class0excl);
						if ($matched) {
							if ($this->debugOTL) { $this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level); }
							for ($p=0;$p<$SubstCount;$p++) {	// EACH LOOKUP
								$SequenceIndex[$p] = $this->read_ushort();
								$LookupListIndex[$p] = $this->read_ushort();
							}

							for ($p=0;$p<$SubstCount;$p++) {
								// Apply  $LookupListIndex  at   $SequenceIndex
								if ($SequenceIndex[$p] >= $InputGlyphCount) { continue; }
								$lu = $LookupListIndex[$p];
								$luType = $this->GSUBLookups[$lu]['Type'];
								$luFlag = $this->GSUBLookups[$lu]['Flag'];
								$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

								$luptr = $matched[$SequenceIndex[$p]];
								$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
								$lucurrGID = $this->OTLdata[$luptr]['uni'];

								foreach($this->GSUBLookups[$lu]['Subtables'] AS $luc=>$lusubtable_offset) {
									$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset) , $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
									if ($shift) { break; }
								}
							}

							if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) { return $shift ; }	/* OTL_FIX_3 */
							else return $InputGlyphCount ;	// should be + matched ignores in Input Sequence

						}

					}

				}
			}

			return 0;
		}

		//===========
		// Format 3: 
		//===========
		// Format 3: Coverage-based Context Glyph Substitution
		else if ($SubstFormat==3) {
			die("GSUB Lookup Type ".$Type." Format ".$SubstFormat." not TESTED YET."); 
			return 0;
		}

	}

	////////////////////////////////////////////////////////////////////////////////
	// LookupType 6: Chaining Contextual Substitution Subtable
	////////////////////////////////////////////////////////////////////////////////
	else if ($Type == 6) {

		//===========
		// Format 1: 
		//===========
		// Format 1: Simple Chaining Context Glyph Substitution
		if ($SubstFormat==1) {	
			$Coverage = $subtable_offset + $this->read_ushort();
			$GlyphPos = $LuCoverage[$currGID];
			$ChainSubRuleSetCount = $this->read_ushort();
			// All of the ChainSubRule tables defining contexts that begin with the same first glyph are grouped together and defined in a ChainSubRuleSet table
			$this->skip($GlyphPos * 2);
			$ChainSubRuleSet= $subtable_offset + $this->read_ushort();
			$this->seek($ChainSubRuleSet);
			$ChainSubRuleCount = $this->read_ushort();

			for($s=0;$s<$ChainSubRuleCount;$s++) {
				$ChainSubRule[$s] = $ChainSubRuleSet + $this->read_ushort();
			}

			for($s=0;$s<$ChainSubRuleCount;$s++) {
				$this->seek($ChainSubRule[$s]);

				$BacktrackGlyphCount = $this->read_ushort();
				$Backtrack = array();
				for ($b=0;$b<$BacktrackGlyphCount;$b++) {
					$gid = $this->read_ushort();
					$Backtrack[] = $this->glyphToChar($gid);
				}
				$Input = array();
				$Input[0] = $this->OTLdata[$ptr]['uni'];
				$InputGlyphCount = $this->read_ushort();
				for ($b=1;$b<$InputGlyphCount;$b++) {
					$gid = $this->read_ushort();
					$Input[$b] = $this->glyphToChar($gid);
				}
				$LookaheadGlyphCount = $this->read_ushort();
				$Lookahead = array();
				for ($b=0;$b<$LookaheadGlyphCount;$b++) {
					$gid = $this->read_ushort();
					$Lookahead[] = $this->glyphToChar($gid);
				}

				$matched = $this->checkContextMatch($Input, $Backtrack, $Lookahead, $ignore, $ptr);
				if ($matched) {
					if ($this->debugOTL) { $this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level); }
					$SubstCount = $this->read_ushort();
					for ($p=0;$p<$SubstCount;$p++) {
						// SubstLookupRecord
						$SubstLookupRecord[$p]['SequenceIndex'] = $this->read_ushort();
						$SubstLookupRecord[$p]['LookupListIndex'] = $this->read_ushort();
					}
					for ($p=0;$p<$SubstCount;$p++) {
						// Apply  $SubstLookupRecord[$p]['LookupListIndex']  at   $SubstLookupRecord[$p]['SequenceIndex']
						if ($SubstLookupRecord[$p]['SequenceIndex'] >= $InputGlyphCount) { continue; }
						$lu = $SubstLookupRecord[$p]['LookupListIndex'];
						$luType = $this->GSUBLookups[$lu]['Type'];
						$luFlag = $this->GSUBLookups[$lu]['Flag'];
						$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

						$luptr = $matched[$SubstLookupRecord[$p]['SequenceIndex']];
						$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
						$lucurrGID = $this->OTLdata[$luptr]['uni'];

						foreach($this->GSUBLookups[$lu]['Subtables'] AS $luc=>$lusubtable_offset) {
							$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
							if ($shift) { break; }
						}
					}
					if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) { return $shift ; }	/* OTL_FIX_3 */
					else return $InputGlyphCount ;	// should be + matched ignores in Input Sequence
				}




			}
			return 0;
		}

		//===========
		// Format 2: 
		//===========
		// Format 2: Class-based Chaining Context Glyph Substitution  p257
		else if ($SubstFormat==2) {	

			// NB Format 2 specifies fixed class assignments (identical for each position in the backtrack, input, or lookahead sequence) and exclusive classes (a glyph cannot be in more than one class at a time)

			$CoverageTableOffset = $subtable_offset + $this->read_ushort();
			$BacktrackClassDefOffset = $subtable_offset + $this->read_ushort();
			$InputClassDefOffset = $subtable_offset + $this->read_ushort();
			$LookaheadClassDefOffset = $subtable_offset + $this->read_ushort();
			$ChainSubClassSetCnt = $this->read_ushort();
			$ChainSubClassSetOffset = array();
			for ($b=0;$b<$ChainSubClassSetCnt;$b++) {
				$offset = $this->read_ushort();
				if ($offset==0x0000) {
					$ChainSubClassSetOffset[] = $offset;
				}
				else {
					$ChainSubClassSetOffset[] = $subtable_offset + $offset;
				}
			}

			$BacktrackClasses = $this->_getClasses($BacktrackClassDefOffset);
			$InputClasses = $this->_getClasses($InputClassDefOffset);
			$LookaheadClasses = $this->_getClasses($LookaheadClassDefOffset);

			for ($s=0;$s<$ChainSubClassSetCnt;$s++) {	// $ChainSubClassSet is ordered by input class-may be NULL
				// Select $ChainSubClassSet if currGlyph is in First Input Class
				if ($ChainSubClassSetOffset[$s]>0 && isset($InputClasses[$s][$currGID])) {
					$this->seek($ChainSubClassSetOffset[$s]);
					$ChainSubClassRuleCnt = $this->read_ushort();
					$ChainSubClassRule = array();
					for($b=0;$b<$ChainSubClassRuleCnt;$b++) {
						$ChainSubClassRule[$b] = $ChainSubClassSetOffset[$s]+$this->read_ushort();
					}

					for($b=0;$b<$ChainSubClassRuleCnt;$b++) {		// EACH RULE
						$this->seek($ChainSubClassRule[$b]);
						$BacktrackGlyphCount = $this->read_ushort();
						for ($r=0;$r<$BacktrackGlyphCount;$r++) {
							$Backtrack[$r] = $this->read_ushort();
						}
						$InputGlyphCount = $this->read_ushort();
						for ($r=1;$r<$InputGlyphCount;$r++) {
							$Input[$r] = $this->read_ushort();
						}
						$LookaheadGlyphCount = $this->read_ushort();
						for ($r=0;$r<$LookaheadGlyphCount;$r++) {
							$Lookahead[$r] = $this->read_ushort();
						}


						// These contain classes of glyphs as arrays
						// $InputClasses[(class)] e.g. 0x02E6,0x02E7,0x02E8
						// $LookaheadClasses[(class)]
						// $BacktrackClasses[(class)]

						// These contain arrays of classIndexes
						// [Backtrack] [Lookahead] and [Input] (Input is from the second position only)


						$inputClass = $s;	//???

						$inputGlyphs = array();
						$inputGlyphs[0] = $InputClasses[$inputClass];

						if ($InputGlyphCount>1) {
							//  NB starts at 1 
							for ($gcl=1;$gcl<$InputGlyphCount;$gcl++) {
								$classindex = $Input[$gcl];
								if (isset($InputClasses[$classindex])) { $inputGlyphs[$gcl] = $InputClasses[$classindex]; }
								else { $inputGlyphs[$gcl] = ''; }
							}
						}

						// Class 0 contains all the glyphs NOT in the other classes
						$class0excl = array();
						for ($gc=1;$gc<=count($InputClasses);$gc++) {
							if (isset($InputClasses[$gc])) $class0excl = $class0excl + $InputClasses[$gc];
						}

						if ($BacktrackGlyphCount) {
							for ($gcl=0;$gcl<$BacktrackGlyphCount;$gcl++) {
								$classindex = $Backtrack[$gcl];
								if (isset($BacktrackClasses[$classindex])) { $backtrackGlyphs[$gcl] = $BacktrackClasses[$classindex]; }
								else { $backtrackGlyphs[$gcl] = ''; }
							}
						}
						else { $backtrackGlyphs = array(); }

						// Class 0 contains all the glyphs NOT in the other classes
						$bclass0excl = array();
						for ($gc=1;$gc<=count($BacktrackClasses);$gc++) {
							if (isset($BacktrackClasses[$gc])) $bclass0excl = $bclass0excl + $BacktrackClasses[$gc];
						}


						if ($LookaheadGlyphCount) {
							for ($gcl=0;$gcl<$LookaheadGlyphCount;$gcl++) {
								$classindex = $Lookahead[$gcl];
								if (isset($LookaheadClasses[$classindex])) { $lookaheadGlyphs[$gcl] = $LookaheadClasses[$classindex]; }
								else { $lookaheadGlyphs[$gcl] = ''; }
							}
						}
						else { $lookaheadGlyphs = array(); }

 						// Class 0 contains all the glyphs NOT in the other classes
						$lclass0excl = array();
						for ($gc=1;$gc<=count($LookaheadClasses);$gc++) {
							if (isset($LookaheadClasses[$gc])) $lclass0excl = $lclass0excl + $LookaheadClasses[$gc];
						}


						$matched = $this->checkContextMatchMultipleUni($inputGlyphs, $backtrackGlyphs, $lookaheadGlyphs, $ignore, $ptr, $class0excl, $bclass0excl, $lclass0excl );
						if ($matched) {
							if ($this->debugOTL) { $this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level); }
							$SubstCount = $this->read_ushort();
							for ($p=0;$p<$SubstCount;$p++) {	// EACH LOOKUP
								$SequenceIndex[$p] = $this->read_ushort();
								$LookupListIndex[$p] = $this->read_ushort();
							}

							for ($p=0;$p<$SubstCount;$p++) {
								// Apply  $LookupListIndex  at   $SequenceIndex
								if ($SequenceIndex[$p] >= $InputGlyphCount) { continue; }
								$lu = $LookupListIndex[$p];
								$luType = $this->GSUBLookups[$lu]['Type'];
								$luFlag = $this->GSUBLookups[$lu]['Flag'];
								$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

								$luptr = $matched[$SequenceIndex[$p]];
								$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
								$lucurrGID = $this->OTLdata[$luptr]['uni'];

								foreach($this->GSUBLookups[$lu]['Subtables'] AS $luc=>$lusubtable_offset) {
									$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset) , $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
									if ($shift) { break; }
								}
							}

							if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) { return $shift ; }	/* OTL_FIX_3 */
							else return $InputGlyphCount ;	// should be + matched ignores in Input Sequence

						}

					}

				}
			}

			return 0;
		}
 
		//===========
		// Format 3: 
		//===========
		// Format 3: Coverage-based Chaining Context Glyph Substitution  p259
		else if ($SubstFormat==3) {

			$BacktrackGlyphCount = $this->read_ushort();
			for ($b=0;$b<$BacktrackGlyphCount;$b++) {
				$CoverageBacktrackOffset[] = $subtable_offset + $this->read_ushort();	// in glyph sequence order
			}
			$InputGlyphCount = $this->read_ushort();
			for ($b=0;$b<$InputGlyphCount;$b++) {
				$CoverageInputOffset[] = $subtable_offset + $this->read_ushort();	// in glyph sequence order
			}
			$LookaheadGlyphCount = $this->read_ushort();
			for ($b=0;$b<$LookaheadGlyphCount;$b++) {
				$CoverageLookaheadOffset[] = $subtable_offset + $this->read_ushort();	// in glyph sequence order
			}
			$SubstCount = $this->read_ushort();
			$save_pos = $this->_pos;	// Save the point just after PosCount

			$CoverageBacktrackGlyphs = array();
			for ($b=0;$b<$BacktrackGlyphCount;$b++) {
				$this->seek($CoverageBacktrackOffset[$b]);
				$glyphs = $this->_getCoverage();
				$CoverageBacktrackGlyphs[$b] = implode("|",$glyphs);
			}
			$CoverageInputGlyphs = array();
			for ($b=0;$b<$InputGlyphCount;$b++) {
				$this->seek($CoverageInputOffset[$b]);
				$glyphs = $this->_getCoverage();
				$CoverageInputGlyphs[$b] = implode("|",$glyphs);
			}
			$CoverageLookaheadGlyphs = array();
			for ($b=0;$b<$LookaheadGlyphCount;$b++) {
				$this->seek($CoverageLookaheadOffset[$b]);
				$glyphs = $this->_getCoverage();
				$CoverageLookaheadGlyphs[$b] = implode("|",$glyphs);
			}

			$matched = $this->checkContextMatchMultiple($CoverageInputGlyphs, $CoverageBacktrackGlyphs, $CoverageLookaheadGlyphs , $ignore, $ptr);
			if ($matched) {
				if ($this->debugOTL) { $this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level); }

				$this->seek($save_pos);	// Return to just after PosCount
				for ($p=0;$p<$SubstCount;$p++) {
					// SubstLookupRecord
					$SubstLookupRecord[$p]['SequenceIndex'] = $this->read_ushort();
					$SubstLookupRecord[$p]['LookupListIndex'] = $this->read_ushort();
				}
				for ($p=0;$p<$SubstCount;$p++) {
					// Apply  $SubstLookupRecord[$p]['LookupListIndex']  at   $SubstLookupRecord[$p]['SequenceIndex']
					if ($SubstLookupRecord[$p]['SequenceIndex'] >= $InputGlyphCount) { continue; }
					$lu = $SubstLookupRecord[$p]['LookupListIndex'];
					$luType = $this->GSUBLookups[$lu]['Type'];
					$luFlag = $this->GSUBLookups[$lu]['Flag'];
					$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

					$luptr = $matched[$SubstLookupRecord[$p]['SequenceIndex']];
					$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
					$lucurrGID = $this->OTLdata[$luptr]['uni'];

					foreach($this->GSUBLookups[$lu]['Subtables'] AS $luc=>$lusubtable_offset) {
						$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
						if ($shift) { break; }
					}
				}
				if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) { return (isset($shift) ? $shift : 0) ; }	/* OTL_FIX_3 */
				else return $InputGlyphCount ;	// should be + matched ignores in Input Sequence
			}

			return 0;

		}
	}

	else { die("GSUB Lookup Type ".$Type." not supported."); }

}

function _updateLigatureMarks($pos, $n) {
	if ($n > 0) {
		// Update position of Ligatures and associated Marks
		// Foreach lig/assocMarks
		// Any position lpos or mpos > $pos + count($substitute)
		//	$this->assocMarks = array(); 	// assocMarks[$pos mpos] => array(compID, ligPos)
		//	$this->assocLigs = array();	// Ligatures[$pos lpos] => nc
		for ($p=count($this->OTLdata)-1;$p>=($pos+$n);$p--) {
			if (isset($this->assocLigs[$p])) {
				$tmp = $this->assocLigs[$p];
				unset($this->assocLigs[$p]); 
				$this->assocLigs[($p + $n)] = $tmp;
			}
		}
		for ($p=count($this->OTLdata)-1;$p>=0;$p--) {
			if (isset($this->assocMarks[$p])) {
				if ($this->assocMarks[$p]['ligPos'] >=($pos+$n)) { $this->assocMarks[$p]['ligPos'] += $n; }
				if ($p>=($pos+$n)) {
					$tmp = $this->assocMarks[$p];
					unset($this->assocMarks[$p]); 
					$this->assocMarks[($p + $n)] = $tmp;
				}
			}
		}
	}

	else if ($n<1) { // glyphs removed
		$nrem = -$n;
		// Update position of pre-existing Ligatures and associated Marks
		for ($p=($pos+1);$p<count($this->OTLdata);$p++) {
			if (isset($this->assocLigs[$p])) {
				$tmp = $this->assocLigs[$p];
				unset($this->assocLigs[$p]); 
				$this->assocLigs[($p - $nrem)] = $tmp;
			}
		}
		for ($p=0;$p<count($this->OTLdata);$p++) {
			if (isset($this->assocMarks[$p])) {
				if ($this->assocMarks[$p]['ligPos'] >=($pos)) { $this->assocMarks[$p]['ligPos'] -= $nrem; }
				if ($p>$pos) {
					$tmp = $this->assocMarks[$p];
					unset($this->assocMarks[$p]); 
					$this->assocMarks[($p - $nrem)] = $tmp;
				}
			}
		}
	}
}

function GSUBsubstitute($pos, $substitute, $Type, $GlyphPos=NULL ) {

	// LookupType 1: Simple Substitution Subtable : 1 to 1
	// LookupType 3: Alternate Forms : 1 to 1(n)
	if ($Type == 1 || $Type == 3) {
		$this->OTLdata[$pos]['uni'] = $substitute;
		$this->OTLdata[$pos]['hex'] = $this->unicode_hex($substitute);
		return 1;
	}
	// LookupType 2: Multiple Substitution Subtable : 1 to n
	else if ($Type == 2) {
		for($i=0;$i<count($substitute);$i++) {
			$uni = $substitute[$i];
			$newOTLdata[$i] = array();
			$newOTLdata[$i]['uni'] = $uni;
			$newOTLdata[$i]['hex'] = $this->unicode_hex($uni);


			// Get types of new inserted chars - or replicate type of char being replaced
		//	$bt = UCDN::get_bidi_class($uni);
		//	if (!$bt) { 
				$bt = $this->OTLdata[$pos]['bidi_type']; 
		//	}

			if (strpos($this->GlyphClassMarks, $newOTLdata[$i]['hex'] )!==false) { $gp = 'M'; }
			else if ($uni == 32) { $gp = 'S'; }
			else { $gp = 'C'; }

			// Need to update matra_type ??? of new glyphs inserted ???????????????????????????????????????

			$newOTLdata[$i]['bidi_type'] = $bt;
			$newOTLdata[$i]['group'] = $gp;

			// Need to update details of new glyphs inserted 
			$newOTLdata[$i]['general_category'] = $this->OTLdata[$pos]['general_category'];

			if ($this->shaper=='I' || $this->shaper=='K' || $this->shaper=='S') {
				$newOTLdata[$i]['indic_category'] = $this->OTLdata[$pos]['indic_category'];
				$newOTLdata[$i]['indic_position'] = $this->OTLdata[$pos]['indic_position'];
			}
			else if ($this->shaper=='M') {
				$newOTLdata[$i]['myanmar_category'] = $this->OTLdata[$pos]['myanmar_category'];
				$newOTLdata[$i]['myanmar_position'] = $this->OTLdata[$pos]['myanmar_position'];
			}
			if (isset($this->OTLdata[$pos]['mask'])) { $newOTLdata[$i]['mask'] = $this->OTLdata[$pos]['mask']; }
			if (isset($this->OTLdata[$pos]['syllable'])) { $newOTLdata[$i]['syllable'] = $this->OTLdata[$pos]['syllable']; }

		}
		if ($this->shaper=='K' || $this->shaper=='T' || $this->shaper=='L') {
			if ($this->OTLdata[$pos]['wordend']) { $newOTLdata[count($substitute)-1]['wordend'] = true; }
		}

		array_splice($this->OTLdata, $pos, 1, $newOTLdata);	// Replace 1 with n
		// Update position of Ligatures and associated Marks
		// count($substitute)-1  is the number of glyphs added
		$nadd = count($substitute)-1;
		$this->_updateLigatureMarks($pos, $nadd);
		return count($substitute);
	}
	// LookupType 4: Ligature Substitution Subtable : n to 1
	else if ($Type == 4) {
		// Create Ligatures and associated Marks
		$firstGlyph = $this->OTLdata[$pos]['hex'];

		// If all components of the ligature are marks (and in the same syllable), we call this a mark ligature.
		$contains_marks = false;
		$contains_nonmarks = false;
		if (isset($this->OTLdata[$pos]['syllable'])) { $current_syllable = $this->OTLdata[$pos]['syllable']; }
		else { $current_syllable = 0; }
		for($i=0;$i<count($GlyphPos);$i++) {
			// If subsequent components are not Marks as well - don't ligate
			$unistr = $this->OTLdata[$GlyphPos[$i]]['hex'];
			if ($this->restrictToSyllable && isset($this->OTLdata[$GlyphPos[$i]]['syllable']) && $this->OTLdata[$GlyphPos[$i]]['syllable'] != $current_syllable) {
				return 0;
			}
			if (strpos($this->GlyphClassMarks, $unistr )!==false) { $contains_marks = true; }
			else  { $contains_nonmarks = true; }
		}
		if ($contains_marks && !$contains_nonmarks) {
			// Mark Ligature (all components are Marks)
			$firstMarkAssoc = '';
			if (isset($this->assocMarks[$pos])) { 
				$firstMarkAssoc = $this->assocMarks[$pos]; 
			}
			// If all components of the ligature are marks, we call this a mark ligature.
			for($i=1;$i<count($GlyphPos);$i++) {

				// If subsequent components are not Marks as well - don't ligate
		//		$unistr = $this->OTLdata[$GlyphPos[$i]]['hex'];
		//		if (strpos($this->GlyphClassMarks, $unistr )===false