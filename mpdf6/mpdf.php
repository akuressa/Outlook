<?php

// ******************************************************************************
// Software: mPDF, Unicode-HTML Free PDF generator                              *
// Version:  6.0        based on                                                *
//           FPDF by Olivier PLATHEY                                            *
//           HTML2FPDF by Renato Coelho                                         *
// Date:     2014-11-24                                                         *
// Author:   Ian Back <ianb@bpm1.com>                                           *
// License:  GPL                                                                *
//                                                                              *
// Changes:  See changelog.txt                                                  *
// ******************************************************************************


define('mPDF_VERSION','6.0');

//Scale factor
define('_MPDFK', (72/25.4));


// Specify which font metrics to use:
// 'winTypo' uses sTypoAscender etc from the OS/2 table and is the one usually recommended - BUT
// 'win' use WinAscent etc from OS/2 and inpractice seems to be used more commonly in Windows environment
// 'mac' uses Ascender etc from hhea table, and is used on Mac/OSX environment
if (!defined('_FONT_DESCRIPTOR')) define("_FONT_DESCRIPTOR", 'win');	// Values: '' [BLANK] or 'win', 'mac', 'winTypo'


/*-- HTML-CSS --*/

define('_BORDER_ALL',15);
define('_BORDER_TOP',8);
define('_BORDER_RIGHT',4);
define('_BORDER_BOTTOM',2);
define('_BORDER_LEFT',1);
/*-- END HTML-CSS --*/

// mPDF 6.0
// Used for $textvars - user settings via CSS
define('FD_UNDERLINE',1);	// font-decoration
define('FD_LINETHROUGH',2);
define('FD_OVERLINE',4);
define('FA_SUPERSCRIPT',8);	// font-(vertical)-align
define('FA_SUBSCRIPT',16);
define('FT_UPPERCASE',32);	// font-transform
define('FT_LOWERCASE',64);
define('FT_CAPITALIZE',128);
define('FC_KERNING',256);	// font-(other)-controls
define('FC_SMALLCAPS',512);


if (!defined('_MPDF_PATH')) define('_MPDF_PATH', dirname(preg_replace('/\\\\/','/',__FILE__)) . '/');
if (!defined('_MPDF_URI')) define('_MPDF_URI',_MPDF_PATH);

require_once(_MPDF_PATH.'includes/functions.php');
require_once(_MPDF_PATH.'config_lang2fonts.php');

require_once(_MPDF_PATH.'classes/ucdn.php');	// mPDF 6.0

/*-- OTL --*/
require_once(_MPDF_PATH.'classes/indic.php');	// mPDF 6.0
require_once(_MPDF_PATH.'classes/myanmar.php');	// mPDF 6.0
require_once(_MPDF_PATH.'classes/sea.php');	// mPDF 6.0
/*-- END OTL --*/


if (!defined('_JPGRAPH_PATH')) define("_JPGRAPH_PATH", _MPDF_PATH.'jpgraph/'); 

if (!defined('_MPDF_TEMP_PATH')) define("_MPDF_TEMP_PATH", _MPDF_PATH.'tmp/');

if (!defined('_MPDF_TTFONTPATH')) { define('_MPDF_TTFONTPATH',_MPDF_PATH.'ttfonts/'); }
if (!defined('_MPDF_TTFONTDATAPATH')) { define('_MPDF_TTFONTDATAPATH',_MPDF_PATH.'ttfontdata/'); }

$errorlevel=error_reporting();
$errorlevel=error_reporting($errorlevel & ~E_NOTICE);

//error_reporting(E_ALL);

if(function_exists("date_default_timezone_set")) {
	if (ini_get("date.timezone")=="") { date_default_timezone_set("Europe/London"); }
}
if (!function_exists("mb_strlen")) { die("Error - mPDF requires mb_string functions. Ensure that PHP is compiled with php_mbstring.dll enabled."); }

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

class mPDF
{

///////////////////////////////
// EXTERNAL (PUBLIC) VARIABLES
// Define these in config.php
///////////////////////////////
var $useFixedNormalLineHeight;	// mPDF 6
var $useFixedTextBaseline;	// mPDF 6
var $adjustFontDescLineheight;	// mPDF 6
var $interpolateImages; // mPDF 6
var $defaultPagebreakType;	// mPDF 6 pagebreaktype

var $indexUseSubentries; // mPDF 6

var $autoScriptToLang; // mPDF 6
var $baseScript; // mPDF 6
var $autoVietnamese; // mPDF 6
var $autoArabic; // mPDF 6

var $CJKforceend;
var $h2bookmarks;
var $h2toc;
var $decimal_align;
var $margBuffer;
var $splitTableBorderWidth;

var $bookmarkStyles;
var $useActiveForms;

var $repackageTTF;
var $allowCJKorphans;
var $allowCJKoverflow;

var $useKerning;
var $restrictColorSpace;
var $bleedMargin;
var $crossMarkMargin;
var $cropMarkMargin;
var $cropMarkLength;
var $nonPrintMargin;

var $PDFX;
var $PDFXauto;

var $PDFA;
var $PDFAauto;
var $ICCProfile;

var $printers_info;
var $iterationCounter;
var $smCapsScale;
var $smCapsStretch;

var $backupSubsFont;
var $backupSIPFont;
var $debugfonts;
var $useAdobeCJK;
var $percentSubset;
var $maxTTFFilesize;
var $BMPonly;

var $tableMinSizePriority;

var $dpi;
var $watermarkImgAlphaBlend;
var $watermarkImgBehind;
var $justifyB4br;
var $packTableData;
var $pgsIns;
var $simpleTables;
var $enableImports;

var $debug;
var $showStats;
var $setAutoTopMargin;
var $setAutoBottomMargin;
var $autoMarginPadding;
var $collapseBlockMargins;
var $falseBoldWeight;
var $normalLineheight;
var $progressBar;
var $incrementFPR1;
var $incrementFPR2;
var $incrementFPR3;
var $incrementFPR4;

var $SHYlang;
var $SHYleftmin;
var $SHYrightmin;
var $SHYcharmin;
var $SHYcharmax;
var $SHYlanguages;
// PageNumber Conditional Text
var $pagenumPrefix;
var $pagenumSuffix;
var $nbpgPrefix;
var $nbpgSuffix;
var $showImageErrors;
var $allow_output_buffering;
var $autoPadding;
var $useGraphs;
var $tabSpaces;
var $autoLangToFont;
var $watermarkTextAlpha;
var $watermarkImageAlpha;
var $watermark_size;
var $watermark_pos;
var $annotSize;
var $annotMargin;
var $annotOpacity;
var $title2annots;
var $keepColumns;
var $keep_table_proportions;
var $ignore_table_widths;
var $ignore_table_percents;
var $list_number_suffix;
var $list_auto_mode;	// mPDF 6
var $list_indent_first_level;	// mPDF 6
var $list_indent_default;	// mPDF 6
var $list_marker_offset;	// mPDF 6
var $useSubstitutions;
var $CSSselectMedia;

var $forcePortraitHeaders;
var $forcePortraitMargins;
var $displayDefaultOrientation;
var $ignore_invalid_utf8;
var $allowedCSStags;
var $onlyCoreFonts;
var $allow_charset_conversion;

var $jSWord;
var $jSmaxChar;
var $jSmaxCharLast;
var $jSmaxWordLast;

var $max_colH_correction;


var $table_error_report;
var $table_error_report_param;
var $biDirectional;
var $text_input_as_HTML; 
var $anchor2Bookmark;
var $shrink_tables_to_fit;

var $allow_html_optional_endtags;

var $img_dpi;

var $defaultheaderfontsize;
var $defaultheaderfontstyle;
var $defaultheaderline;
var $defaultfooterfontsize;
var $defaultfooterfontstyle;
var $defaultfooterline;
var $header_line_spacing;
var $footer_line_spacing;

var $pregCJKchars;
var $pregRTLchars;
var $pregCURSchars; // mPDF 6

var $mirrorMargins;
var $watermarkText;
var $watermarkImage;
var $showWatermarkText;
var $showWatermarkImage;

var $fontsizes;

var $defaultPageNumStyle; // mPDF 6

//////////////////////
// CLASS OBJECTS
//////////////////////
var $otl;	// mPDF 5.7.1
var $cssmgr;
var $grad;
var $bmp;
var $wmf;
var $tocontents;
var $mpdfform;
var $directw;

//////////////////////
// INTERNAL VARIABLES
//////////////////////
var $script2lang;
var $viet;
var $pashto;
var $urdu;
var $persian;
var $sindhi;

var $extrapagebreak;	// mPDF 6 pagebreaktype
var $uniqstr;	// mPDF 5.7.2
var $hasOC;

var $textvar;	// mPDF 5.7.1
var $fontLanguageOverride;	// mPDF 5.7.1
var $OTLtags;	// mPDF 5.7.1
var $OTLdata; 	// mPDF 5.7.1
var $writingToC;
var $layers;
var $current_layer;
var $open_layer_pane;
var $decimal_offset;
var $inMeter;

var $CJKleading;
var $CJKfollowing;
var $CJKoverflow;

var $textshadow;

var $colsums;
var $spanborder;
var $spanborddet;

var $visibility;

var $useRC128encryption;
var $uniqid;

var $kerning;
var $fixedlSpacing;
var $minwSpacing;
var $lSpacingCSS;
var $wSpacingCSS;

var $spotColorIDs;
var $SVGcolors;
var $spotColors;
var $defTextColor;
var $defDrawColor;
var $defFillColor;

var $tableBackgrounds;
var $inlineDisplayOff;
var $kt_y00;
var $kt_p00;
var $upperCase;
var $checkSIP;
var $checkSMP;
var $checkCJK;

var $watermarkImgAlpha;
var $PDFAXwarnings;
var $MetadataRoot; 
var $OutputIntentRoot;
var $InfoRoot; 
var $current_filename;
var $parsers;
var $current_parser;
var $_obj_stack;
var $_don_obj_stack;
var $_current_obj_id;
var $tpls;
var $tpl;
var $tplprefix;
var $_res;

var $pdf_version;
var $noImageFile;
var $lastblockbottommargin;
var $baselineC;
// mPDF 5.7.3  inline text-decoration parameters
var $baselineSup;
var $baselineSub;
var $baselineS;
var $subPos;
var $subArrMB;
var $ReqFontStyle;
var $tableClipPath ;

var $fullImageHeight;
var $inFixedPosBlock;		// Internal flag for position:fixed block
var $fixedPosBlock;		// Buffer string for position:fixed block
var $fixedPosBlockDepth;
var $fixedPosBlockBBox;
var $fixedPosBlockSave;
var $maxPosL;
var $maxPosR;

var $loaded;

var $extraFontSubsets;
var $docTemplateStart;		// Internal flag for page (page no. -1) that docTemplate starts on
var $time0;

// Classes
var $indic;
var $barcode;

var $SHYpatterns;
var $loadedSHYpatterns;
var $loadedSHYdictionary;
var $SHYdictionary;
var $SHYdictionaryWords;

var $spanbgcolorarray;
var $default_font;
var $headerbuffer;
var $lastblocklevelchange;
var $nestedtablejustfinished;
var $linebreakjustfinished;
var $cell_border_dominance_L;
var $cell_border_dominance_R;
var $cell_border_dominance_T;
var $cell_border_dominance_B;
var $table_keep_together;
var $plainCell_properties;
var $shrin_k1;
var $outerfilled;

var $blockContext;
var $floatDivs;


var $patterns;
var $pageBackgrounds;

var $bodyBackgroundGradient;
var $bodyBackgroundImage;
var $bodyBackgroundColor;

var $writingHTMLheader;	// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
var $writingHTMLfooter;
var $angle;

var $gradients;

var $kwt_Reference;
var $kwt_BMoutlines;
var $kwt_toc;

var $tbrot_BMoutlines;
var $tbrot_toc;

var $col_BMoutlines;
var $col_toc;

var $currentGraphId;
var $graphs;

var $floatbuffer;
var $floatmargins;

var $bullet;
var $bulletarray;


var $currentLang;
var $default_lang;
var $default_available_fonts;
var $pageTemplate;
var $docTemplate;
var $docTemplateContinue;

var $arabGlyphs;
var $arabHex;
var $persianGlyphs;
var $persianHex;
var $arabVowels;
var $arabPrevLink;
var $arabNextLink;


var $formobjects; // array of Form Objects for WMF
var $InlineProperties;
var $InlineAnnots;
var $InlineBDF;	// mPDF 6 Bidirectional formatting
var $InlineBDFctr;	// mPDF 6
var $ktAnnots;
var $tbrot_Annots;
var $kwt_Annots;
var $columnAnnots;
var $columnForms;

var $PageAnnots;

var $pageDim;	// Keep track of page wxh for orientation changes - set in _beginpage, used in _putannots

var $breakpoints;

var $tableLevel;
var $tbctr;
var $innermostTableLevel;
var $saveTableCounter;
var $cellBorderBuffer;

var $saveHTMLFooter_height;
var $saveHTMLFooterE_height;

var $firstPageBoxHeader;
var $firstPageBoxHeaderEven;
var $firstPageBoxFooter;
var $firstPageBoxFooterEven;

var $page_box;
var $show_marks;	// crop or cross marks

var $basepathIsLocal;

var $use_kwt;
var $kwt;
var $kwt_height;
var $kwt_y0;
var $kwt_x0;
var $kwt_buffer;
var $kwt_Links;
var $kwt_moved;
var $kwt_saved;

var $PageNumSubstitutions;

var $table_borders_separate;
var $base_table_properties;
var $borderstyles;

var $blockjustfinished;

var $orig_bMargin;
var $orig_tMargin;
var $orig_lMargin;
var $orig_rMargin;
var $orig_hMargin;
var $orig_fMargin;

var $pageHTMLheaders;
var $pageHTMLfooters;

var $saveHTMLHeader;
var $saveHTMLFooter;

var $HTMLheaderPageLinks;
var $HTMLheaderPageAnnots;
var $HTMLheaderPageForms;

// See config_fonts.php for these next 5 values
var $available_unifonts;
var $sans_fonts;
var $serif_fonts;
var $mono_fonts;
var $defaultSubsFont;

// List of ALL available CJK fonts (incl. styles) (Adobe add-ons)  hw removed
var $available_CJK_fonts;

var $HTMLHeader;
var $HTMLFooter;
var $HTMLHeaderE;
var $HTMLFooterE;
var $bufferoutput; 


// CJK fonts
var $Big5_widths;
var $GB_widths;
var $SJIS_widths;
var $UHC_widths;

// SetProtection
var $encrypted;	//whether document is protected
var $Uvalue;	//U entry in pdf document
var $Ovalue;	//O entry in pdf document
var $Pvalue;	//P entry in pdf document
var $enc_obj_id;	//encryption object id
var $last_rc4_key;	//last RC4 key encrypted (cached for optimisation)
var $last_rc4_key_c;	//last RC4 computed key
var $encryption_key;
var $padding;	//used for encryption


// Bookmark
var $BMoutlines;
var $OutlineRoot;
// INDEX
var $ColActive;
var $Reference;
var $CurrCol;
var $NbCol;
var $y0;			//Top ordinate of columns
var $ColL;
var $ColWidth;
var $ColGap;
// COLUMNS 
var $ColR;
var $ChangeColumn;
var $columnbuffer;
var $ColDetails;
var $columnLinks;
var $colvAlign;
// Substitutions
var $substitute;		// Array of substitution strings e.g. <ttz>112</ttz>
var $entsearch;		// Array of HTML entities (>ASCII 127) to substitute
var $entsubstitute;	// Array of substitution decimal unicode for the Hi entities


// Default values if no style sheet offered	(cf. http://www.w3.org/TR/CSS21/sample.html)
var $defaultCSS;

var $lastoptionaltag;	// Save current block item which HTML specifies optionsl endtag
var $pageoutput;
var $charset_in;
var $blk;
var $blklvl;
var $ColumnAdjust;
var $ws;	// Word spacing
var $HREF;
var $pgwidth;
var $fontlist; 
var $oldx;
var $oldy;
var $B;
var $I;

var $tdbegin;
var $table;
var $cell;
var $col;
var $row;

var $divbegin;
var $divwidth;
var $divheight;
var $spanbgcolor;

// mPDF 6   Used for table cell (block-type) properties
var $cellTextAlign;
var $cellLineHeight;
var $cellLineStackingStrategy;
var $cellLineStackingShift;

// mPDF 6  Lists
var $listcounter;
var $listlvl;
var $listtype;
var $listitem;

var $pjustfinished;
var $ignorefollowingspaces;
var $SMALL;
var $BIG;
var $dash_on;
var $dotted_on;

var $textbuffer;
var $currentfontstyle;
var $currentfontfamily;
var $currentfontsize;
var $colorarray;
var $bgcolorarray;
var $internallink;
var $enabledtags;

var $lineheight;
var $basepath;
var $textparam;

var $specialcontent;
var $selectoption;
var $objectbuffer;

// Table Rotation
var $table_rotate;
var $tbrot_maxw;
var $tbrot_maxh;
var $tablebuffer;
var $tbrot_align;
var $tbrot_Links;

var $keep_block_together;	// Keep a Block from page-break-inside: avoid

var $tbrot_y0;
var $tbrot_x0;
var $tbrot_w;
var $tbrot_h;

var $mb_enc;
var $directionality;

var $extgstates; // Used for alpha channel - Transparency (Watermark)
var $mgl;
var $mgt;
var $mgr;
var $mgb;

var $tts;
var $ttz;
var $tta;

// Best to alter the below variables using default stylesheet above
var $page_break_after_avoid;
var $margin_bottom_collapse;
var $default_font_size;	// in pts
var $original_default_font_size;	// used to save default sizes when using table default
var $original_default_font;
var $watermark_font;
var $defaultAlign;

// TABLE
var $defaultTableAlign;
var $tablethead;
var $thead_font_weight;
var $thead_font_style;
var $thead_font_smCaps;
var $thead_valign_default;
var $thead_textalign_default;
var $tabletfoot;
var $tfoot_font_weight;
var $tfoot_font_style;
var $tfoot_font_smCaps;
var $tfoot_valign_default;
var $tfoot_textalign_default;

var $trow_text_rotate;

var $cellPaddingL;
var $cellPaddingR;
var $cellPaddingT;
var $cellPaddingB;
var $table_border_attr_set;
var $table_border_css_set;

var $shrin_k;			// factor with which to shrink tables - used internally - do not change
var $shrink_this_table_to_fit;	// 0 or false to disable; value (if set) gives maximum factor to reduce fontsize
var $MarginCorrection;	// corrects for OddEven Margins
var $margin_footer;
var $margin_header;

var $tabletheadjustfinished;
var $usingCoreFont;
var $charspacing;

//Private properties FROM FPDF
var $DisplayPreferences; 
var $flowingBlockAttr;
var $page;               //current page number
var $n;                  //current object number
var $offsets;            //array of object offsets
var $buffer;             //buffer holding in-memory PDF
var $pages;              //array containing pages
var $state;              //current document state
var $compress;           //compression flag
var $DefOrientation;     //default orientation
var $CurOrientation;     //current orientation
var $OrientationChanges; //array indicating orientation changes
var $k;                  //scale factor (number of points in user unit)
var $fwPt;
var $fhPt;         //dimensions of page format in points
var $fw;
var $fh;             //dimensions of page format in user unit
var $wPt;
var $hPt;           //current dimensions of page in points
var $w;
var $h;               //current dimensions of page in user unit
var $lMargin;            //left margin
var $tMargin;            //top margin
var $rMargin;            //right margin
var $bMargin;            //page break margin
var $cMarginL;            //cell margin Left
var $cMarginR;            //cell margin Right
var $cMarginT;            //cell margin Left
var $cMarginB;            //cell margin Right
var $DeflMargin;            //Default left margin
var $DefrMargin;            //Default right margin
var $x;
var $y;               //current position in user unit for cell positioning
var $lasth;              //height of last cell printed
var $LineWidth;          //line width in user unit
var $CoreFonts;          //array of standard font names
var $fonts;              //array of used fonts
var $FontFiles;          //array of font files
var $images;             //array of used images
var $PageLinks;          //array of links in pages
var $links;              //array of internal links
var $FontFamily;         //current font family
var $FontStyle;          //current font style
var $CurrentFont;        //current font info
var $FontSizePt;         //current font size in points
var $FontSize;           //current font size in user unit
var $DrawColor;          //commands for drawing color
var $FillColor;          //commands for filling color
var $TextColor;          //commands for text color
var $ColorFlag;          //indicates whether fill and text colors are different
var $autoPageBreak;      //automatic page breaking
var $PageBreakTrigger;   //threshold used to trigger page breaks
var $InFooter;           //flag set when processing footer
var $InHTMLFooter;

var $processingFooter;   //flag set when processing footer - added for columns
var $processingHeader;   //flag set when processing header - added for columns
var $ZoomMode;           //zoom display mode
var $LayoutMode;         //layout display mode
var $title;              //title
var $subject;            //subject
var $author;             //author
var $keywords;           //keywords
var $creator;            //creator

var $aliasNbPg;       //alias for total number of pages
var $aliasNbPgGp;       //alias for total number of pages in page group
//var $aliasNbPgHex;	// mPDF 6 deleted
//var $aliasNbPgGpHex;	// mPDF 6 deleted

var $ispre;

var $outerblocktags;
var $innerblocktags;


// **********************************
// **********************************
// **********************************
// **********************************
// **********************************
// **********************************
// **********************************
// **********************************
// **********************************

function mPDF($mode='',$format='A4',$default_font_size=0,$default_font='',$mgl=15,$mgr=15,$mgt=16,$mgb=16,$mgh=9,$mgf=9, $orientation='P') {

/*-- BACKGROUNDS --*/
		if (!class_exists('grad', false)) { include(_MPDF_PATH.'classes/grad.php'); }
		if (empty($this->grad)) { $this->grad = new grad($this); }
/*-- END BACKGROUNDS --*/
/*-- FORMS --*/
		if (!class_exists('mpdfform', false)) { include(_MPDF_PATH.'classes/mpdfform.php'); }
		if (empty($this->mpdfform)) { $this->mpdfform = new mpdfform($this); }
/*-- END FORMS --*/

	$this->time0 = microtime(true);
	//Some checks
	$this->_dochecks();

	$this->writingToC = false;
	$this->layers = array();
	$this->current_layer = 0;
	$this->open_layer_pane = false;

	$this->visibility='visible';

	//Initialization of properties
	$this->spotColors=array();
	$this->spotColorIDs = array();
	$this->tableBackgrounds = array();
	$this->uniqstr = '20110230';	// mPDF 5.7.2
	$this->kt_y00 = '';
	$this->kt_p00 = '';
	$this->iterationCounter = false;
	$this->BMPonly = array();
	$this->page=0;
	$this->n=2;
	$this->buffer='';
	$this->objectbuffer = array();
	$this->pages=array();
	$this->OrientationChanges=array();
	$this->state=0;
	$this->fonts=array();
	$this->FontFiles=array();
	$this->images=array();
	$this->links=array();
	$this->InFooter=false;
	$this->processingFooter=false;
	$this->processingHeader=false;
	$this->lasth=0;
	$this->FontFamily='';
	$this->FontStyle='';
	$this->FontSizePt=9;
	$this->U=false;
	// Small Caps
	$this->upperCase = array();
	$this->smCapsScale = 1;
	$this->smCapsStretch = 100;
	$this->margBuffer = 0;
	$this->inMeter = false;
	$this->decimal_offset = 0;

	$this->defTextColor = $this->TextColor = $this->SetTColor($this->ConvertColor(0),true);
	$this->defDrawColor = $this->DrawColor = $this->SetDColor($this->ConvertColor(0),true);
	$this->defFillColor = $this->FillColor = $this->SetFColor($this->ConvertColor(255),true);

	//SVG color names array
	//http://www.w3schools.com/css/css_colornames.asp
	$this->SVGcolors = array('antiquewhite'=>'#FAEBD7','aqua'=>'#00FFFF','aquamarine'=>'#7FFFD4','beige'=>'#F5F5DC','black'=>'#000000',
'blue'=>'#0000FF','brown'=>'#A52A2A','cadetblue'=>'#5F9EA0','chocolate'=>'#D2691E','cornflowerblue'=>'#6495ED','crimson'=>'#DC143C',
'darkblue'=>'#00008B','darkgoldenrod'=>'#B8860B','darkgreen'=>'#006400','darkmagenta'=>'#8B008B','darkorange'=>'#FF8C00',
'darkred'=>'#8B0000','darkseagreen'=>'#8FBC8F','darkslategray'=>'#2F4F4F','darkviolet'=>'#9400D3','deepskyblue'=>'#00BFFF',
'dodgerblue'=>'#1E90FF','firebrick'=>'#B22222','forestgreen'=>'#228B22','fuchsia'=>'#FF00FF','gainsboro'=>'#DCDCDC','gold'=>'#FFD700',
'gray'=>'#808080','green'=>'#008000','greenyellow'=>'#ADFF2F','hotpink'=>'#FF69B4','indigo'=>'#4B0082','khaki'=>'#F0E68C',
'lavenderblush'=>'#FFF0F5','lemonchiffon'=>'#FFFACD','lightcoral'=>'#F08080','lightgoldenrodyellow'=>'#FAFAD2','lightgreen'=>'#90EE90',
'lightsalmon'=>'#FFA07A','lightskyblue'=>'#87CEFA','lightslategray'=>'#778899','lightyellow'=>'#FFFFE0','lime'=>'#00FF00','limegreen'=>'#32CD32',
'magenta'=>'#FF00FF','maroon'=>'#800000','mediumaquamarine'=>'#66CDAA','mediumorchid'=>'#BA55D3','mediumseagreen'=>'#3CB371',
'mediumspringgreen'=>'#00FA9A','mediumvioletred'=>'#C71585','midnightblue'=>'#191970','mintcream'=>'#F5FFFA','moccasin'=>'#FFE4B5','navy'=>'#000080',
'olive'=>'#808000','orange'=>'#FFA500','orchid'=>'#DA70D6','palegreen'=>'#98FB98',
'palevioletred'=>'#D87093','peachpuff'=>'#FFDAB9','pink'=>'#FFC0CB','powderblue'=>'#B0E0E6','purple'=>'#800080',
'red'=>'#FF0000','royalblue'=>'#4169E1','salmon'=>'#FA8072','seagreen'=>'#2E8B57','sienna'=>'#A0522D','silver'=>'#C0C0C0','skyblue'=>'#87CEEB',
'slategray'=>'#708090','springgreen'=>'#00FF7F','steelblue'=>'#4682B4','tan'=>'#D2B48C','teal'=>'#008080','thistle'=>'#D8BFD8','turquoise'=>'#40E0D0',
'violetred'=>'#D02090','white'=>'#FFFFFF','yellow'=>'#FFFF00', 
'aliceblue'=>'#f0f8ff', 'azure'=>'#f0ffff', 'bisque'=>'#ffe4c4', 'blanchedalmond'=>'#ffebcd', 'blueviolet'=>'#8a2be2', 'burlywood'=>'#deb887', 
'chartreuse'=>'#7fff00', 'coral'=>'#ff7f50', 'cornsilk'=>'#fff8dc', 'cyan'=>'#00ffff', 'darkcyan'=>'#008b8b', 'darkgray'=>'#a9a9a9', 
'darkgrey'=>'#a9a9a9', 'darkkhaki'=>'#bdb76b', 'darkolivegreen'=>'#556b2f', 'darkorchid'=>'#9932cc', 'darksalmon'=>'#e9967a', 
'darkslateblue'=>'#483d8b', 'darkslategrey'=>'#2f4f4f', 'darkturquoise'=>'#00ced1', 'deeppink'=>'#ff1493', 'dimgray'=>'#696969', 
'dimgrey'=>'#696969', 'floralwhite'=>'#fffaf0', 'ghostwhite'=>'#f8f8ff', 'goldenrod'=>'#daa520', 'grey'=>'#808080', 'honeydew'=>'#f0fff0', 
'indianred'=>'#cd5c5c', 'ivory'=>'#fffff0', 'lavender'=>'#e6e6fa', 'lawngreen'=>'#7cfc00', 'lightblue'=>'#add8e6', 'lightcyan'=>'#e0ffff', 
'lightgray'=>'#d3d3d3', 'lightgrey'=>'#d3d3d3', 'lightpink'=>'#ffb6c1', 'lightseagreen'=>'#20b2aa', 'lightslategrey'=>'#778899', 
'lightsteelblue'=>'#b0c4de', 'linen'=>'#faf0e6', 'mediumblue'=>'#0000cd', 'mediumpurple'=>'#9370db', 'mediumslateblue'=>'#7b68ee', 
'mediumturquoise'=>'#48d1cc', 'mistyrose'=>'#ffe4e1', 'navajowhite'=>'#ffdead', 'oldlace'=>'#fdf5e6', 'olivedrab'=>'#6b8e23', 'orangered'=>'#ff4500', 
'palegoldenrod'=>'#eee8aa', 'paleturquoise'=>'#afeeee', 'papayawhip'=>'#ffefd5', 'peru'=>'#cd853f', 'plum'=>'#dda0dd', 'rosybrown'=>'#bc8f8f', 
'saddlebrown'=>'#8b4513', 'sandybrown'=>'#f4a460', 'seashell'=>'#fff5ee', 'slateblue'=>'#6a5acd', 'slategrey'=>'#708090', 'snow'=>'#fffafa', 
'tomato'=>'#ff6347', 'violet'=>'#ee82ee', 'wheat'=>'#f5deb3', 'whitesmoke'=>'#f5f5f5', 'yellowgreen'=>'#9acd32');

	// Uppercase alternatives (for Small Caps)
	if (empty($this->upperCase)) { @include(_MPDF_PATH.'includes/upperCase.php'); } 
	$this->extrapagebreak = true;	// mPDF 6 pagebreaktype

	$this->ColorFlag=false;
	$this->extgstates = array();

	$this->mb_enc='windows-1252';
	$this->directionality='ltr';
	$this->defaultAlign = 'L';
	$this->defaultTableAlign = 'L';

	$this->fixedPosBlockSave = array();
	$this->extraFontSubsets = 0;

	$this->SHYpatterns = array();
	$this->loadedSHYdictionary = false;
	$this->SHYdictionary = array();
	$this->SHYdictionaryWords = array();
	$this->blockContext = 1;
	$this->floatDivs = array();
	$this->DisplayPreferences=''; 

	$this->patterns = array();		// Tiling patterns used for backgrounds
	$this->pageBackgrounds = array();
	$this->writingHTMLheader = false;	// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
	$this->writingHTMLfooter = false;	// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
	$this->gradients = array();

	$this->kwt_Reference = array();
	$this->kwt_BMoutlines = array();
	$this->kwt_toc = array();

	$this->tbrot_BMoutlines = array();
	$this->tbrot_toc = array();

	$this->col_BMoutlines = array();
	$this->col_toc = array();
	$this->graphs = array();

	$this->pgsIns = array();
	$this->PDFAXwarnings = array();
	$this->inlineDisplayOff = false;
	$this->lSpacingCSS = '';
	$this->wSpacingCSS = '';
	$this->fixedlSpacing = false;
	$this->minwSpacing = 0;


	$this->baselineC = 0.35;	// Baseline for text
	// mPDF 5.7.3  inline text-decoration parameters
	$this->baselineSup = 0.5;	// Sets default change in baseline for <sup> text as factor of preceeding fontsize
						// 0.35 has been recommended; 0.5 matches applications like MS Word
	$this->baselineSub = -0.2;	// Sets default change in baseline for <sub> text as factor of preceeding fontsize
	$this->baselineS = 0.3;		// Sets default height for <strike> text as factor of fontsize
	$this->baselineO = 1.1;		// Sets default height for overline text as factor of fontsize

	$this->noImageFile = str_replace("\\","/",dirname(__FILE__)) . '/includes/no_image.jpg';
	$this->subPos = 0;
	$this->normalLineheight = 1.3;	// This should be overridden in config.php - but it is so important a default value is put here
	// These are intended as configuration variables, and should be set in config.php - which will override these values; 
	// set here as failsafe as will cause an error if not defined
	$this->incrementFPR1 = 10;
	$this->incrementFPR2 = 10;
	$this->incrementFPR3 = 10;
	$this->incrementFPR4 = 10;

	$this->fullImageHeight = false;
	$this->floatbuffer = array();
	$this->floatmargins = array();
	$this->formobjects=array(); // array of Form Objects for WMF
	$this->InlineProperties=array();
	$this->InlineAnnots=array();
	$this->InlineBDF=array();	// mPDF 6
	$this->InlineBDFctr= 0;	// mPDF 6
	$this->tbrot_Annots=array();
	$this->kwt_Annots=array();
	$this->columnAnnots=array();
	$this->pageDim=array();
	$this->breakpoints = array();	// used in columnbuffer
	$this->tableLevel=0;
	$this->tbctr=array();	// counter for nested tables at each level
	$this->page_box = array();
	$this->show_marks = '';	// crop or cross marks
	$this->kwt = false;
	$this->kwt_height = 0;
	$this->kwt_y0 = 0;
	$this->kwt_x0 = 0;
	$this->kwt_buffer = array();
	$this->kwt_Links = array();
	$this->kwt_moved = false;
	$this->kwt_saved = false;
	$this->PageNumSubstitutions = array();
	$this->base_table_properties=array();
	$this->borderstyles = array('inset','groove','outset','ridge','dotted','dashed','solid','double');
	$this->tbrot_align = 'C';

	$this->pageHTMLheaders=array();
	$this->pageHTMLfooters=array();
	$this->HTMLheaderPageLinks = array();
	$this->HTMLheaderPageAnnots = array();

	$this->HTMLheaderPageForms = array();
	$this->columnForms = array();
	$this->tbrotForms = array();
	$this->useRC128encryption = false;
	$this->uniqid = '';

	$this->pageoutput = array();

	$this->bufferoutput = false; 
	$this->encrypted=false;    		//whether document is protected
	$this->BMoutlines=array();
	$this->ColActive=0;        		//Flag indicating that columns are on (the index is being processed)
	$this->Reference=array();  		//Array containing the references
	$this->CurrCol=0;              	//Current column number
	$this->ColL = array(0);			// Array of Left pos of columns - absolute - needs Margin correction for Odd-Even
	$this->ColR = array(0);			// Array of Right pos of columns - absolute pos - needs Margin correction for Odd-Even
	$this->ChangeColumn = 0;
	$this->columnbuffer = array();
	$this->ColDetails = array();		// Keeps track of some column details
	$this->columnLinks = array();		// Cross references PageLinks
	$this->substitute = array();		// Array of substitution strings e.g. <ttz>112</ttz>
	$this->entsearch = array();		// Array of HTML entities (>ASCII 127) to substitute
	$this->entsubstitute = array();	// Array of substitution decimal unicode for the Hi entities
	$this->lastoptionaltag = '';
	$this->charset_in = '';
	$this->blk = array();
	$this->blklvl = 0;
	$this->tts = false;
	$this->ttz = false;
	$this->tta = false;
	$this->ispre=false;

	$this->checkSIP = false;
	$this->checkSMP = false;
	$this->checkCJK = false;

	$this->page_break_after_avoid = false;
	$this->margin_bottom_collapse = false;
	$this->tablethead = 0;
	$this->tabletfoot = 0;
	$this->table_border_attr_set = 0;
	$this->table_border_css_set = 0;
	$this->shrin_k = 1.0;
	$this->shrink_this_table_to_fit = 0;
	$this->MarginCorrection = 0;

	$this->tabletheadjustfinished = false;
	$this->usingCoreFont = false;
	$this->charspacing=0;

	$this->autoPageBreak = true;

	require(_MPDF_PATH.'config.php');	// config data

	$this->_setPageSize($format, $orientation);
	$this->DefOrientation=$orientation;

	$this->margin_header=$mgh;
	$this->margin_footer=$mgf;

	$bmargin=$mgb;

	$this->DeflMargin = $mgl;
	$this->DefrMargin = $mgr;

	$this->orig_tMargin = $mgt;
	$this->orig_bMargin = $bmargin;
	$this->orig_lMargin = $this->DeflMargin;
	$this->orig_rMargin = $this->DefrMargin;
	$this->orig_hMargin = $this->margin_header;
	$this->orig_fMargin = $this->margin_footer;

	if ($this->setAutoTopMargin=='pad') { $mgt += $this->margin_header; }
	if ($this->setAutoBottomMargin=='pad') { $mgb += $this->margin_footer; }
	$this->SetMargins($this->DeflMargin,$this->DefrMargin,$mgt);	// sets l r t margin
	//Automatic page break
	$this->SetAutoPageBreak($this->autoPageBreak,$bmargin);	// sets $this->bMargin & PageBreakTrigger

	$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;

	//Interior cell margin (1 mm) ? not used
	$this->cMarginL = 1;
	$this->cMarginR = 1;
	//Line width (0.2 mm)
	$this->LineWidth=.567/_MPDFK;

	//To make the function Footer() work - replaces {nb} with page number
	$this->AliasNbPages();
	$this->AliasNbPageGroups();

	//$this->aliasNbPgHex = '{nbHEXmarker}';	// mPDF 6 deleted
	//$this->aliasNbPgGpHex = '{nbpgHEXmarker}';	// mPDF 6 deleted

	//Enable all tags as default
	$this->DisableTags();
	//Full width display mode
	$this->SetDisplayMode(100);	// fullwidth?		'fullpage'
	//Compression
	$this->SetCompression(true);
	//Set default display preferences
	$this->SetDisplayPreferences(''); 

	// Font data
	require(_MPDF_PATH.'config_fonts.php');
	// Available fonts
	$this->available_unifonts = array();
	foreach ($this->fontdata AS $f => $fs) {
		if (isset($fs['R']) && $fs['R']) { $this->available_unifonts[] = $f; }
		if (isset($fs['B']) && $fs['B']) { $this->available_unifonts[] = $f.'B'; }
		if (isset($fs['I']) && $fs['I']) { $this->available_unifonts[] = $f.'I'; }
		if (isset($fs['BI']) && $fs['BI']) { $this->available_unifonts[] = $f.'BI'; }
	}

	$this->default_available_fonts = $this->available_unifonts;

	$optcore = false;
	$onlyCoreFonts = false;
	if (preg_match('/([\-+])aCJK/i',$mode, $m)) {
		$mode = preg_replace('/([\-+])aCJK/i','',$mode);	// mPDF 6
		if ($m[1]=='+') { $this->useAdobeCJK = true; }
		else { $this->useAdobeCJK = false; }
	}

	if (strlen($mode)==1) {
		if ($mode=='s') { $this->percentSubset = 100; $mode = ''; }
		else if ($mode=='c') { $onlyCoreFonts = true; $mode = ''; }
	}
	else if (substr($mode,-2)=='-s') {
		$this->percentSubset = 100; 
		$mode = substr($mode,0,strlen($mode)-2);
	}
	else if (substr($mode,-2)=='-c') {
		$onlyCoreFonts = true;
		$mode = substr($mode,0,strlen($mode)-2);
	}
	else if (substr($mode,-2)=='-x') {
		$optcore = true;
		$mode = substr($mode,0,strlen($mode)-2);
	}

	// Autodetect if mode is a language_country string (en-GB or en_GB or en)
	if ($mode && $mode != 'UTF-8') {	// mPDF 6
		list ($coreSuitable,$mpdf_pdf_unifont) = GetLangOpts($mode, $this->useAdobeCJK, $this->fontdata);
		if ($coreSuitable && $optcore) { $onlyCoreFonts = true; }
		if ($mpdf_pdf_unifont) { 	// mPDF 6
			$default_font = $mpdf_pdf_unifont; 
		}
		$this->currentLang = $mode;
		$this->default_lang = $mode;
	}

	$this->onlyCoreFonts =  $onlyCoreFonts;

	if ($this->onlyCoreFonts) {
		$this->setMBencoding('windows-1252');	// sets $this->mb_enc
	}
	else {
		$this->setMBencoding('UTF-8');	// sets $this->mb_enc
	}
	@mb_regex_encoding('UTF-8');	// required only for mb_ereg... and mb_split functions


	// Adobe CJK fonts
	$this->available_CJK_fonts = array('gb','big5','sjis','uhc','gbB','big5B','sjisB','uhcB','gbI','big5I','sjisI','uhcI',
		'gbBI','big5BI','sjisBI','uhcBI');


	//Standard fonts
	$this->CoreFonts=array('ccourier'=>'Courier','ccourierB'=>'Courier-Bold','ccourierI'=>'Courier-Oblique','ccourierBI'=>'Courier-BoldOblique',
		'chelvetica'=>'Helvetica','chelveticaB'=>'Helvetica-Bold','chelveticaI'=>'Helvetica-Oblique','chelveticaBI'=>'Helvetica-BoldOblique',
		'ctimes'=>'Times-Roman','ctimesB'=>'Times-Bold','ctimesI'=>'Times-Italic','ctimesBI'=>'Times-BoldItalic',
		'csymbol'=>'Symbol','czapfdingbats'=>'ZapfDingbats');
	$this->fontlist=array("ctimes","ccourier","chelvetica","csymbol","czapfdingbats");

	// Substitutions
	$this->setHiEntitySubstitutions();

	if ($this->onlyCoreFonts) {
		$this->useSubstitutions = true;
		$this->SetSubstitutions();
	}
	else { $this->useSubstitutions = false; }

/*-- HTML-CSS --*/

	if (!class_exists('cssmgr', false)) { include(_MPDF_PATH .'classes/cssmgr.php'); }
	$this->cssmgr = new cssmgr($this);
	// mPDF 6
	if (file_exists(_MPDF_PATH.'mpdf.css')) {
		$css = file_get_contents(_MPDF_PATH.'mpdf.css');
		$this->cssmgr->ReadCSS('<style> '.$css.' </style>');
	}
/*-- END HTML-CSS --*/

	if ($default_font=='') { 
	  if ($this->onlyCoreFonts) { 
		if (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']),$this->mono_fonts)) { $default_font = 'ccourier'; }
		else if (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']),$this->sans_fonts)) { $default_font = 'chelvetica'; }
		else { $default_font = 'ctimes'; }
	  }
	  else { $default_font = $this->defaultCSS['BODY']['FONT-FAMILY']; }
	}
	if (!$default_font_size) { 
		$mmsize = $this->ConvertSize($this->defaultCSS['BODY']['FONT-SIZE']);
		$default_font_size = $mmsize*(_MPDFK);
	}

	if ($default_font) { $this->SetDefaultFont($default_font); }
	if ($default_font_size) { $this->SetDefaultFontSize($default_font_size); }

	$this->SetLineHeight();	// lineheight is in mm

	$this->SetFColor($this->ConvertColor(255));
	$this->HREF='';
	$this->oldy=-1;
	$this->B=0;
	$this->I=0;

	// mPDF 6  Lists
	$this->listlvl=0;
	$this->listtype=array();
	$this->listitem=array();
	$this->listcounter=array();

	$this->tdbegin=false; 
	$this->table=array(); 
	$this->cell=array();  
	$this->col=-1; 
	$this->row=-1; 
	$this->cellBorderBuffer = array();

	$this->divbegin=false;
	// mPDF 6
	$this->cellTextAlign='';
	$this->cellLineHeight = '';
	$this->cellLineStackingStrategy = '';
	$this->cellLineStackingShift = '';

	$this->divwidth=0; 
	$this->divheight=0; 
	$this->spanbgcolor=false;
	$this->spanborder=false;
	$this->spanborddet=array();

	$this->blockjustfinished=false;
	$this->ignorefollowingspaces = true; //in order to eliminate exceeding left-side spaces
	$this->dash_on=false;
	$this->dotted_on=false;
	$this->textshadow='';

	$this->currentfontfamily='';
	$this->currentfontsize='';
	$this->currentfontstyle='';
	$this->colorarray='';	// mPDF 6
	$this->spanbgcolorarray='';	// mPDF 6
	$this->textbuffer=array();
	$this->internallink=array();
	$this->basepath = "";

	$this->SetBasePath('');

	$this->textparam = array();

	$this->specialcontent = '';
	$this->selectoption = array();

/*-- IMPORTS --*/

	$this->tpls = array();
	$this->tpl = 0;
	$this->tplprefix = "/TPL";
	$this->res = array();
	if ($this->enableImports) {
		$this->SetImportUse();
	}
/*-- END IMPORTS --*/

	if ($this->progressBar) { $this->StartProgressBarOutput($this->progressBar) ;	}	// *PROGRESS-BAR*
}



function _setPageSize($format, &$orientation) {
	//Page format
	if(is_string($format))
	{
		if ($format=='') { $format = 'A4'; }
		$pfo = 'P';
		if(preg_match('/([0-9a-zA-Z]*)-L/i',$format,$m)) {	// e.g. A4-L = A4 landscape
			$format=$m[1]; 
			$pfo='L'; 
		}
		$format = $this->_getPageFormat($format);
		if (!$format) { $this->Error('Unknown page format: '.$format); }
		else { $orientation = $pfo; }

		$this->fwPt=$format[0];
		$this->fhPt=$format[1];
	}
	else
	{
		if (!$format[0] || !$format[1]) { $this->Error('Invalid page format: '.$format[0].' '.$format[1]); }
		$this->fwPt=$format[0]*_MPDFK;
		$this->fhPt=$format[1]*_MPDFK;
	}
	$this->fw=$this->fwPt/_MPDFK;
	$this->fh=$this->fhPt/_MPDFK;
	//Page orientation
	$orientation=strtolower($orientation);
	if($orientation=='p' or $orientation=='portrait')
	{
		$orientation='P';
		$this->wPt=$this->fwPt;
		$this->hPt=$this->fhPt;
	}
	elseif($orientation=='l' or $orientation=='landscape')
	{
		$orientation='L';
		$this->wPt=$this->fhPt;
		$this->hPt=$this->fwPt;
	}
	else $this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation=$orientation;

	$this->w=$this->wPt/_MPDFK;
	$this->h=$this->hPt/_MPDFK;
}

function _getPageFormat($format) {
		switch (strtoupper($format)) {
			case '4A0': {$format = array(4767.87,6740.79); break;}
			case '2A0': {$format = array(3370.39,4767.87); break;}
			case 'A0': {$format = array(2383.94,3370.39); break;}
			case 'A1': {$format = array(1683.78,2383.94); break;}
			case 'A2': {$format = array(1190.55,1683.78); break;}
			case 'A3': {$format = array(841.89,1190.55); break;}
			case 'A4': {$format = array(595.28,841.89); break;}
			case 'A5': {$format = array(419.53,595.28); break;}
			case 'A6': {$format = array(297.64,419.53); break;}
			case 'A7': {$format = array(209.76,297.64); break;}
			case 'A8': {$format = array(147.40,209.76); break;}
			case 'A9': {$format = array(104.88,147.40); break;}
			case 'A10': {$format = array(73.70,104.88); break;}
			case 'B0': {$format = array(2834.65,4008.19); break;}
			case 'B1': {$format = array(2004.09,2834.65); break;}
			case 'B2': {$format = array(1417.32,2004.09); break;}
			case 'B3': {$format = array(1000.63,1417.32); break;}
			case 'B4': {$format = array(708.66,1000.63); break;}
			case 'B5': {$format = array(498.90,708.66); break;}
			case 'B6': {$format = array(354.33,498.90); break;}
			case 'B7': {$format = array(249.45,354.33); break;}
			case 'B8': {$format = array(175.75,249.45); break;}
			case 'B9': {$format = array(124.72,175.75); break;}
			case 'B10': {$format = array(87.87,124.72); break;}
			case 'C0': {$format = array(2599.37,3676.54); break;}
			case 'C1': {$format = array(1836.85,2599.37); break;}
			case 'C2': {$format = array(1298.27,1836.85); break;}
			case 'C3': {$format = array(918.43,1298.27); break;}
			case 'C4': {$format = array(649.13,918.43); break;}
			case 'C5': {$format = array(459.21,649.13); break;}
			case 'C6': {$format = array(323.15,459.21); break;}
			case 'C7': {$format = array(229.61,323.15); break;}
			case 'C8': {$format = array(161.57,229.61); break;}
			case 'C9': {$format = array(113.39,161.57); break;}
			case 'C10': {$format = array(79.37,113.39); break;}
			case 'RA0': {$format = array(2437.80,3458.27); break;}
			case 'RA1': {$format = array(1729.13,2437.80); break;}
			case 'RA2': {$format = array(1218.90,1729.13); break;}
			case 'RA3': {$format = array(864.57,1218.90); break;}
			case 'RA4': {$format = array(609.45,864.57); break;}
			case 'SRA0': {$format = array(2551.18,3628.35); break;}
			case 'SRA1': {$format = array(1814.17,2551.18); break;}
			case 'SRA2': {$format = array(1275.59,1814.17); break;}
			case 'SRA3': {$format = array(907.09,1275.59); break;}
			case 'SRA4': {$format = array(637.80,907.09); break;}
			case 'LETTER': {$format = array(612.00,792.00); break;}
			case 'LEGAL': {$format = array(612.00,1008.00); break;}
			case 'LEDGER': {$format = array(279.00,432.00); break;}
			case 'TABLOID': {$format = array(279.00,432.00); break;}
			case 'EXECUTIVE': {$format = array(521.86,756.00); break;}
			case 'FOLIO': {$format = array(612.00,936.00); break;}
			case 'B': {$format=array(362.83,561.26 );	 break;}		//	'B' format paperback size 128x198mm
			case 'A': {$format=array(314.65,504.57 );	 break;}		//	'A' format paperback size 111x178mm
			case 'DEMY': {$format=array(382.68,612.28 );  break;}		//	'Demy' format paperback size 135x216mm
			case 'ROYAL': {$format=array(433.70,663.30 );  break;}	//	'Royal' format paperback size 153x234mm
			default: {$format = array(595.28,841.89); break;}
		}
	return $format;
}


/*-- PROGRESS-BAR --*/
function StartProgressBarOutput($mode=1) {
	// must be relative path, or URI (not a file system path)
	if (!defined('_MPDF_URI')) { 
		$this->progressBar = false;
		if ($this->debug) { $this->Error("You need to define _MPDF_URI to use the progress bar!"); }
		else return false; 
	}
	$this->progressBar = $mode;
	if ($this->progbar_altHTML) {
		echo $this->progbar_altHTML;
	}
	else {
	   echo '<html>
	<head>
	<title>mPDF File Progress</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="'._MPDF_URI.'progbar.css" />
		</head>
	<body>
	<div class="main">
		<div class="heading">'.$this->progbar_heading.'</div>
		<div class="demo">
	   ';
	   if ($this->progressBar==2) { echo '		<table width="100%"><tr><td style="width: 50%;"> 
			<span class="barheading">Writing HTML code</span> <br/>

			<div class="progressBar">
			<div id="element1"  class="innerBar">&nbsp;</div>
			</div>
			<span class="code" id="box1"></span>
			</td><td style="width: 50%;">
			<span class="barheading">Autosizing elements</span> <br/>
			<div class="progressBar">
			<div id="element4"  class="innerBar">&nbsp;</div>
			</div>
			<span class="code" id="box4"></span>
			<br/><br/>
			<span class="barheading">Writing Tables</span> <br/>
			<div class="progressBar">
			<div id="element7"  class="innerBar">&nbsp;</div>
			</div>
			<span class="code" id="box7"></span>
			</td></tr>
			<tr><td><br /><br /></td><td></td></tr>
			<tr><td style="width: 50%;"> 
	'; }
	echo '			<span class="barheading">Writing PDF file</span> <br/>
			<div class="progressBar">
			<div id="element2"  class="innerBar">&nbsp;</div>
			</div>
			<span class="code" id="box2"></span>
	   ';
	   if ($this->progressBar==2) { echo '
			</td><td style="width: 50%;">
			<span class="barheading">Memory usage</span> <br/>
			<div class="progressBar">
			<div id="element5"  class="innerBar">&nbsp;</div>
			</div>
			<span id="box5">0</span> '.ini_get("memory_limit").'<br />
			<br/><br/>
			<span class="barheading">Memory usage (peak)</span> <br/>
			<div class="progressBar">
			<div id="element6"  class="innerBar">&nbsp;</div>
			</div>
			<span id="box6">0</span> '.ini_get("memory_limit").'<br />
			</td></tr>
			</table>
	   '; }
	   echo '			<br/><br/>
		<span id="box3"></span>

		</div>
	   ';
	}
	ob_flush();
      flush();
}

function UpdateProgressBar($el,$val,$txt='') {
	// $val should be a string - 5 = actual value, +15 = increment

	if ($this->progressBar<2) {
		if ($el>3) { return; }
		else if ($el ==1) { $el = 2; }
	}
	echo '<script type="text/javascript">';
	if ($val) { echo ' document.getElementById(\'element'.$el.'\').style.width=\''.$val.'%\'; '; }
	if ($txt) { echo ' document.getElementById(\'box'.$el.'\').innerHTML=\''.$txt.'\'; '; }
	if ($this->progressBar==2) { 
		$m = round(memory_get_usage(true)/1048576);
		$m2 = round(memory_get_peak_usage(true)/1048576);
		$mem = $m * 100 / (ini_get("memory_limit")+0);
		$mem2 = $m2 * 100 / (ini_get("memory_limit")+0);
		echo ' document.getElementById(\'element5\').style.width=\''.$mem.'%\'; '; 
		echo ' document.getElementById(\'element6\').style.width=\''.$mem2.'%\'; '; 
		echo ' document.getElementById(\'box5\').innerHTML=\''.$m.'MB / \'; '; 
		echo ' document.getElementById(\'box6\').innerHTML=\''.$m2.'MB / \'; '; 
	}
	echo '</script>'."\n";
	ob_flush();
	flush();
}
/*-- END PROGRESS-BAR --*/



function RestrictUnicodeFonts($res) {
	// $res = array of (Unicode) fonts to restrict to: e.g. norasi|norasiB - language specific
	if (count($res)) {	// Leave full list of available fonts if passed blank array
		$this->available_unifonts = $res;
	}
	else { $this->available_unifonts = $this->default_available_fonts; }
	if (count($this->available_unifonts) == 0) { $this->available_unifonts[] = $this->default_available_fonts[0]; }
	$this->available_unifonts = array_values($this->available_unifonts);
}


function setMBencoding($enc) {
	if ($this->mb_enc != $enc) { 
		$this->mb_enc = $enc; 
		mb_internal_encoding($this->mb_enc); 
	}
}


function SetMargins($left,$right,$top) {
	//Set left, top and right margins
	$this->lMargin=$left;
	$this->rMargin=$right;
	$this->tMargin=$top;
}

function ResetMargins() {
	//ReSet left, top margins
	if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation=='P' && $this->CurOrientation=='L') {
	    if (($this->mirrorMargins) && (($this->page)%2==0)) {	// EVEN
		$this->tMargin=$this->orig_rMargin;
		$this->bMargin=$this->orig_lMargin;
	    }
	    else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS
		$this->tMargin=$this->orig_lMargin;
		$this->bMargin=$this->orig_rMargin;
	    }
	   $this->lMargin=$this->DeflMargin;
	   $this->rMargin=$this->DefrMargin;
	   $this->MarginCorrection = 0;
	   $this->PageBreakTrigger=$this->h-$this->bMargin;
	}
	else  if (($this->mirrorMargins) && (($this->page)%2==0)) {	// EVEN
		$this->lMargin=$this->DefrMargin;
		$this->rMargin=$this->DeflMargin;
		$this->MarginCorrection = $this->DefrMargin-$this->DeflMargin;

	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS
		$this->lMargin=$this->DeflMargin;
		$this->rMargin=$this->DefrMargin;
		if ($this->mirrorMargins) { $this->MarginCorrection = $this->DeflMargin-$this->DefrMargin; }
	}
	$this->x=$this->lMargin;

}

function SetLeftMargin($margin) {
	//Set left margin
	$this->lMargin=$margin;
	if($this->page>0 and $this->x<$margin) $this->x=$margin;
}

function SetTopMargin($margin) {
	//Set top margin
	$this->tMargin=$margin;
}

function SetRightMargin($margin) {
	//Set right margin
	$this->rMargin=$margin;
}

function SetAutoPageBreak($auto,$margin=0) {
	//Set auto page break mode and triggering margin
	$this->autoPageBreak=$auto;
	$this->bMargin=$margin;
	$this->PageBreakTrigger=$this->h-$margin;
}

function SetDisplayMode($zoom,$layout='continuous') {
	//Set display mode in viewer
	if($zoom=='fullpage' or $zoom=='fullwidth' or $zoom=='real' or $zoom=='default' or !is_string($zoom))
		$this->ZoomMode=$zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' or $layout=='continuous' or $layout=='two' or $layout=='twoleft' or $layout=='tworight' or $layout=='default')
		$this->LayoutMode=$layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress) {
	//Set page compression
	if(function_exists('gzcompress'))	$this->compress=$compress;
	else $this->compress=false;
}

function SetTitle($title) {
	//Title of document // Arrives as UTF-8
	$this->title = $title;
}

function SetSubject($subject) {
	//Subject of document
	$this->subject= $subject;
}

function SetAuthor($author) {
	//Author of document
	$this->author= $author;
}

function SetKeywords($keywords) {
	//Keywords of document
	$this->keywords= $keywords;
}

function SetCreator($creator) {
	//Creator of document
	$this->creator= $creator;
}


function SetAnchor2Bookmark($x) {
	$this->anchor2Bookmark = $x;
}

function AliasNbPages($alias='{nb}') {
	//Define an alias for total number of pages
	$this->aliasNbPg=$alias;
}

function AliasNbPageGroups($alias='{nbpg}') {
	//Define an alias for total number of pages in a group
	$this->aliasNbPgGp=$alias;
}

function SetAlpha($alpha, $bm='Normal', $return=false, $mode='B') {
// alpha: real value from 0 (transparent) to 1 (opaque)
// bm:    blend mode, one of the following:
//          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
//          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
// set alpha for stroking (CA) and non-stroking (ca) operations
// mode determines F (fill) S (stroke) B (both)
	if (($this->PDFA || $this->PDFX) && $alpha!=1) { 
		if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) { $this->PDFAXwarnings[] = "Image opacity must be 100% (Opacity changed to 100%)"; }
		$alpha = 1; 
	}
	$a = array('BM'=>'/'.$bm);
	if ($mode=='F' || $mode=='B') $a['ca'] = $alpha;	// mPDF 5.7.2
	if ($mode=='S' || $mode=='B') $a['CA'] = $alpha;	// mPDF 5.7.2
	$gs = $this->AddExtGState($a);
	if ($return) { return sprintf('/GS%d gs', $gs); }
	else { $this->_out(sprintf('/GS%d gs', $gs)); }
}

function AddExtGState($parms) {
	$n = count($this->extgstates);
	// check if graphics state already exists
	for ($i=1; $i<=$n; $i++) {
	  if (count($this->extgstates[$i]['parms']) == count($parms)) {
	    $same = true;
	    foreach($this->extgstates[$i]['parms'] AS $k=>$v) {
		if (!isset($parms[$k]) || $parms[$k] != $v) { $same = false; break; }
	    }
	    if ($same) { return $i; }
	  }
	}
	$n++;
	$this->extgstates[$n]['parms'] = $parms;
	return $n;
}

function SetVisibility($v) {
	if (($this->PDFA || $this->PDFX) && $this->visibility!='visible') { $this->PDFAXwarnings[] = "Cannot set visibility to anything other than full when using PDFA or PDFX"; return ''; }
	else if (!$this->PDFA && !$this->PDFX)
		$this->pdf_version='1.5';
	if($this->visibility!='visible') {
		$this->_out('EMC');
		$this->hasOC=intval($this->hasOC );
	}
	if($v=='printonly') {
		$this->_out('/OC /OC1 BDC');
		$this->hasOC=($this->hasOC | 1);
	}
	elseif($v=='screenonly') {
		$this->_out('/OC /OC2 BDC');
		$this->hasOC=($this->hasOC | 2);
	}
	elseif($v=='hidden') {
		$this->_out('/OC /OC3 BDC');
		$this->hasOC=($this->hasOC | 4);
	}
	elseif($v!='visible')
		$this->Error('Incorrect visibility: '.$v);
	$this->visibility=$v;
}

function Error($msg) {
	//Fatal error
	header('Content-Type: text/html; charset=utf-8');
	die('<B>mPDF error: </B>'.$msg);
}

function Open() {
	//Begin document
	if($this->state==0) {
		// Was is function _begindoc()
		// Start document
		$this->state=1;
		$this->_out('%PDF-'.$this->pdf_version);
		$this->_out('%'.chr(226).chr(227).chr(207).chr(211));	// 4 chars > 128 to show binary file
	}
}

// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// DEPRACATED but included for backwards compatability
// Depracated - can use AddPage for all
function AddPages($a='',$b='', $c='', $d='', $e='',$f='',$g='',$h='',$i='',$j='',$k='',$l='',$m='',$n='',$o='',$p=0,$q=0,$r=0,$s=0,$t='',$u='') {
	$this->Error('function AddPages is depracated as of mPDF 6. Please use AddPage or HTML code methods instead.'); 
}

function startPageNums() { $this->Error('function startPageNums is depracated as of mPDF 6.'); }

function setUnvalidatedText($a='', $b=-1) { $this->Error('function setUnvalidatedText is depracated as of mPDF 6. Please use SetWatermarkText instead.');  }

function SetAutoFont($a) { $this->Error('function SetAutoFont is depracated as of mPDF 6. Please use autoScriptToLang instead. See config.php'); }

function Reference($a) { $this->Error('function Reference is depracated as of mPDF 6. Please use IndexEntry instead.');  }

function ReferenceSee($a,$b) { $this->Error('function ReferenceSee is depracated as of mPDF 6. Please use IndexEntrySee instead.');  }

function CreateReference($a=1, $b='', $c='', $d=3, $e=1, $f='', $g=5, $h='',$i='', $j=false) { $this->Error('function CreateReference is depracated as of mPDF 6. Please use InsertIndex instead.');  }

function CreateIndex($a=1, $b='', $c='', $d=3, $e=1, $f='', $g=5, $h='',$i='', $j=false) { $this->Error('function CreateIndex is depracated as of mPDF 6. Please use InsertIndex instead.');  }

// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

function Close() {
	// Check old Aliases - now depracated mPDF 6
	if (isset($this->UnvalidatedText)) { $this->Error('$mpdf->UnvalidatedText is depracated as of mPDF 6. Please use $mpdf->watermarkText  instead.'); }
	if (isset($this->TopicIsUnvalidated)) { $this->Error('$mpdf->TopicIsUnvalidated is depracated as of mPDF 6. Please use $mpdf->showWatermarkText instead.'); }
	if (isset($this->AliasNbPg)) { $this->Error('$mpdf->AliasNbPg is depracated as of mPDF 6. Please use $mpdf->aliasNbPg instead.'); }
	if (isset($this->AliasNbPgGp)) { $this->Error('$mpdf->AliasNbPgGp is depracated as of mPDF 6. Please use $mpdf->aliasNbPgGp instead.'); }
	if (isset($this->BiDirectional)) { $this->Error('$mpdf->BiDirectional is depracated as of mPDF 6. Please use $mpdf->biDirectional instead.'); }
	if (isset($this->Anchor2Bookmark)) { $this->Error('$mpdf->Anchor2Bookmark is depracated as of mPDF 6. Please use $mpdf->anchor2Bookmark instead.'); }
	if (isset($this->KeepColumns)) { $this->Error('$mpdf->KeepColumns is depracated as of mPDF 6. Please use $mpdf->keepColumns instead.'); }
	if (isset($this->useOddEven)) { $this->Error('$mpdf->useOddEven is depracated as of mPDF 6. Please use $mpdf->mirrorMargins instead.'); }
	if (isset($this->useSubstitutionsMB)) { $this->Error('$mpdf->useSubstitutionsMB is depracated as of mPDF 6. Please use $mpdf->useSubstitutions instead.'); }
	if (isset($this->useLang)) { $this->Error('$mpdf->useLang is depracated as of mPDF 6. Please use $mpdf->autoLangToFont instead.'); }
	if (isset($this->useAutoFont)) { $this->Error('$mpdf->useAutoFont is depracated. Please use $mpdf->autoScriptToLang instead.'); }

	if ($this->progressBar) { $this->UpdateProgressBar(2,'2','Closing last page'); }	// *PROGRESS-BAR*
	//Terminate document
	if($this->state==3)	return;
	if($this->page==0) $this->AddPage($this->CurOrientation);
	if (count($this->cellBorderBuffer)) { $this->printcellbuffer(); }	// *TABLES*
	if ($this->tablebuffer) { $this->printtablebuffer(); }	// *TABLES*
/*-- COLUMNS --*/

	if ($this->ColActive) {
		$this->SetColumns(0);
		$this->ColActive = 0; 
		if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
	}
/*-- END COLUMNS --*/

	// BODY Backgrounds
	$s = '';

	$s .= $this->PrintBodyBackgrounds();

	$s .= $this->PrintPageBackgrounds();
	$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS'.$this->uniqstr.')/', "\n".$s."\n".'\\1', $this->pages[$this->page]);
	$this->pageBackgrounds = array();

	if($this->visibility!='visible')
		$this->SetVisibility('visible');
	$this->EndLayer();

	if (!$this->tocontents || !$this->tocontents->TOCmark) { //Page footer
		$this->InFooter=true;
		$this->Footer();
		$this->InFooter=false;
	}
	if ($this->tocontents && ($this->tocontents->TOCmark || count($this->tocontents->m_TOC))) { $this->tocontents->insertTOC(); }	// *TOC*

	//Close page
	$this->_endpage();

	//Close document
	$this->_enddoc();
}

/*-- BACKGROUNDS --*/
function _resizeBackgroundImage($imw, $imh, $cw, $ch, $resize=0, $repx, $repy, $pba=array(), $size=array()) {
	// pba is background positioning area (from CSS background-origin) may not always be set [x,y,w,h]
	// size is from CSS3 background-size - takes precendence over old resize 
	//	$w - absolute length or % or auto or cover | contain
	//	$h - absolute length or % or auto or cover | contain
	if (isset($pba['w'])) $cw = $pba['w'];
	if (isset($pba['h'])) $ch = $pba['h'];

	$cw = $cw*_MPDFK;
	$ch = $ch*_MPDFK;
	if (empty($size) && !$resize) { return array($imw, $imh, $repx, $repy); }

	if (isset($size['w']) && $size['w']) {
		if ($size['w']=='contain') {
		// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest size such that both its width and its height can fit inside the background positioning area. 
		// Same as resize==3
			$h = $imh * $cw/$imw;
			$w = $cw;
			if ($h > $ch) {
				$w = $w * $ch/$h;
				$h = $ch;
			}
		}
		else if ($size['w']=='cover') {
		// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest size such that both its width and its height can completely cover the background positioning area. 
			$h = $imh * $cw/$imw;
			$w = $cw;
			if ($h < $ch) {
				$w = $w * $h/$ch;
				$h = $ch;
			}
		}
		else {
			if (stristr($size['w'] ,'%')) {
				$size['w'] += 0; 
				$size['w'] /= 100; 
				$size['w'] = ($cw * $size['w']);
			}
			if (stristr($size['h'] ,'%')) {
				$size['h'] += 0; 
				$size['h'] /= 100; 
				$size['h'] = ($ch * $size['h']);
			}
			if ($size['w']=='auto' && $size['h']=='auto') {
				$w = $imw;
				$h = $imh;
			}
			else if ($size['w']=='auto' && $size['h']!='auto') {
				$w = $imw * $size['h']/$imh;
				$h = $size['h'];
			}
			else if ($size['w']!='auto' && $size['h']=='auto') {
				$h = $imh * $size['w']/$imw;
				$w = $size['w'];
			}
			else {
				$w = $size['w'];
				$h = $size['h'];
			}
		}
		return array($w, $h, $repx, $repy); 
	}
	else if ($resize==1 && $imw > $cw) {
		$h = $imh * $cw/$imw;
		return array($cw, $h, $repx, $repy); 
	}
	else if ($resize==2 && $imh > $ch) {
		$w = $imw * $ch/$imh;
		return array($w, $ch, $repx, $repy); 
	}
	else if ($resize==3) {
		$w = $imw;
		$h = $imh;
		if ($w > $cw) {
			$h = $h * $cw/$w;
			$w = $cw;
		}
		if ($h > $ch) {
			$w = $w * $ch/$h;
			$h = $ch;
		}
		return array($w, $h, $repx, $repy); 
	}
	else if ($resize==4) {
		$h = $imh * $cw/$imw;
		return array($cw, $h, $repx, $repy); 
	}
	else if ($resize==5) {
		$w = $imw * $ch/$imh;
		return array($w, $ch, $repx, $repy); 
	}
	else if ($resize==6) {
		return array($cw, $ch, $repx, $repy); 
	}
	return array($imw, $imh, $repx, $repy);
}


function SetBackground(&$properties, &$maxwidth) {
	if (isset($properties['BACKGROUND-ORIGIN']) && ($properties['BACKGROUND-ORIGIN']=='border-box' || $properties['BACKGROUND-ORIGIN']== 'content-box')) { $origin = $properties['BACKGROUND-ORIGIN']; }
	else { $origin = 'padding-box'; }
	if (isset($properties['BACKGROUND-SIZE'])) { 
		if (stristr($properties['BACKGROUND-SIZE'] ,'contain') ) { $bsw = $bsh = 'contain'; }
		else if (stristr($properties['BACKGROUND-SIZE'] ,'cover') ) { $bsw = $bsh = 'cover'; }
		else {
			$bsw = $bsh = 'auto';
			$sz = preg_split('/\s+/',trim($properties['BACKGROUND-SIZE']));
			if (count($sz)==2) { $bsw = $sz[0]; $bsh = $sz[1]; }
			else { $bsw = $sz[0]; }
			if (!stristr($bsw ,'%') && !stristr($bsw ,'auto') ) { $bsw = $this->ConvertSize($bsw ,$maxwidth,$this->FontSize); }
			if (!stristr($bsh ,'%') && !stristr($bsh ,'auto') ) { $bsh = $this->ConvertSize($bsh ,$maxwidth,$this->FontSize); }
		}
		$size = array('w'=>$bsw, 'h'=>$bsh);
	}
	else { $size = false; }	// mPDF 6
	   if (preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/',$properties['BACKGROUND-IMAGE'])) {
		return array('gradient'=>$properties['BACKGROUND-IMAGE'], 'origin'=>$origin, 'size'=>$size );
	   }
	   else {
		$file = $properties['BACKGROUND-IMAGE'];
		$sizesarray = $this->Image($file,0,0,0,0,'','',false, false, false, false, true);
		if (isset($sizesarray['IMAGE_ID'])) {
			$image_id = $sizesarray['IMAGE_ID'];
			$orig_w = $sizesarray['WIDTH']*_MPDFK;		// in user units i.e. mm
 			$orig_h = $sizesarray['HEIGHT']*_MPDFK;		// (using $this->img_dpi)
			if (isset($properties['BACKGROUND-IMAGE-RESOLUTION'])) { 
				if (preg_match('/from-image/i', $properties['BACKGROUND-IMAGE-RESOLUTION']) && isset($sizesarray['set-dpi']) && $sizesarray['set-dpi']>0) {
					$orig_w *= $this->img_dpi / $sizesarray['set-dpi'];
					$orig_h *= $this->img_dpi / $sizesarray['set-dpi'];
				}
				else if (preg_match('/(\d+)dpi/i', $properties['BACKGROUND-IMAGE-RESOLUTION'], $m)) {
					$dpi = $m[1]; 
					if ($dpi > 0) {
						$orig_w *= $this->img_dpi / $dpi;
						$orig_h *= $this->img_dpi / $dpi;
					}
				}
			}
			$x_repeat = true;
			$y_repeat = true;
			if (isset($properties['BACKGROUND-REPEAT'])) {
				if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-x') { $y_repeat = false; }
				if ($properties['BACKGROUND-REPEAT']=='no-repeat' || $properties['BACKGROUND-REPEAT']=='repeat-y') { $x_repeat = false; }
			}
			$x_pos = 0;
			$y_pos = 0;
			if (isset($properties['BACKGROUND-POSITION'])) { 
				$ppos = preg_split('/\s+/',$properties['BACKGROUND-POSITION']);
				$x_pos = $ppos[0];
				$y_pos = $ppos[1];
				if (!stristr($x_pos ,'%') ) { $x_pos = $this->ConvertSize($x_pos ,$maxwidth,$this->FontSize); }
				if (!stristr($y_pos ,'%') ) { $y_pos = $this->ConvertSize($y_pos ,$maxwidth,$this->FontSize); }
			}
			if (isset($properties['BACKGROUND-IMAGE-RESIZE'])) { $resize = $properties['BACKGROUND-IMAGE-RESIZE']; }
			else { $resize = 0; }
			if (isset($properties['BACKGROUND-IMAGE-OPACITY'])) { $opacity = $properties['BACKGROUND-IMAGE-OPACITY']; }
			else { $opacity = 1; }
			return array('image_id'=>$image_id, 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$x_pos, 'y_pos'=>$y_pos, 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'resize'=>$resize, 'opacity'=>$opacity, 'itype'=>$sizesarray['itype'], 'origin'=>$origin, 'size'=>$size );
		}
	   }
	   return false;
}
/*-- END BACKGROUNDS --*/

function PrintBodyBackgrounds() {
	$s = '';
	$clx = 0;
	$cly = 0;
	$clw = $this->w;
	$clh = $this->h;
	// If using bleed and trim margins in paged media
	if ($this->pageDim[$this->page]['outer_width_LR'] || $this->pageDim[$this->page]['outer_width_TB']) {
		$clx = $this->pageDim[$this->page]['outer_width_LR'] - $this->pageDim[$this->page]['bleedMargin'];
		$cly = $this->pageDim[$this->page]['outer_width_TB'] - $this->pageDim[$this->page]['bleedMargin'];
		$clw = $this->w - 2*$clx;
		$clh = $this->h - 2*$cly;
	}

	if ($this->bodyBackgroundColor) {
		$s .= 'q ' .$this->SetFColor($this->bodyBackgroundColor, true)."\n";
		if ($this->bodyBackgroundColor{0}==5) {	// RGBa
			$s .= $this->SetAlpha(ord($this->bodyBackgroundColor{4})/100, 'Normal', true, 'F')."\n";
		}
		else if ($this->bodyBackgroundColor{0}==6) {	// CMYKa
			$s .= $this->SetAlpha(ord($this->bodyBackgroundColor{5})/100, 'Normal', true, 'F')."\n";
		}
		$s .= sprintf('%.3F %.3F %.3F %.3F re f Q', ($clx*_MPDFK), ($cly*_MPDFK),$clw*_MPDFK,$clh*_MPDFK)."\n";
	}

/*-- BACKGROUNDS --*/
	if ($this->bodyBackgroundGradient) { 
		$g = $this->grad->parseBackgroundGradient($this->bodyBackgroundGradient);
		if ($g) {
			$s .= $this->grad->Gradient($clx, $cly, $clw, $clh, (isset($g['gradtype']) ? $g['gradtype'] : null), $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true);
		}
	}
	if ($this->bodyBackgroundImage) {
	   if (isset($this->bodyBackgroundImage['gradient']) && $this->bodyBackgroundImage['gradient'] && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $this->bodyBackgroundImage['gradient'])) {
		$g = $this->grad->parseMozGradient( $this->bodyBackgroundImage['gradient']);
		if ($g) {
			$s .= $this->grad->Gradient($clx, $cly, $clw, $clh, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true);
		}
	   }
	   else if ($this->bodyBackgroundImage['image_id']) {	// Background pattern
			$n = count($this->patterns)+1;
			// If using resize, uses TrimBox (not including the bleed)
			list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($this->bodyBackgroundImage['orig_w'], $this->bodyBackgroundImage['orig_h'], $clw, $clh, $this->bodyBackgroundImage['resize'], $this->bodyBackgroundImage['x_repeat'], $this->bodyBackgroundImage['y_repeat']);

			$this->patterns[$n] = array('x'=>$clx, 'y'=>$cly, 'w'=>$clw, 'h'=>$clh, 'pgh'=>$this->h, 'image_id'=>$this->bodyBackgroundImage['image_id'], 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$this->bodyBackgroundImage['x_pos'], 'y_pos'=>$this->bodyBackgroundImage['y_pos'], 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'itype'=>$this->bodyBackgroundImage['itype']);
			if (($this->bodyBackgroundImage['opacity']>0 || $this->bodyBackgroundImage['opacity']==='0') && $this->bodyBackgroundImage['opacity']<1) { $opac = $this->SetAlpha($this->bodyBackgroundImage['opacity'],'Normal',true); }
			else { $opac = ''; }
			$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, ($clx*_MPDFK), ($cly*_MPDFK),$clw*_MPDFK, $clh*_MPDFK) ."\n";
	   }
	}
/*-- END BACKGROUNDS --*/
	return $s;
}

function _setClippingPath($clx, $cly, $clw, $clh) {
	$s = ' q 0 w ';	// Line width=0
	$s .= sprintf('%.3F %.3F m ', ($clx)*_MPDFK, ($this->h-($cly))*_MPDFK);	// start point TL before the arc
	$s .= sprintf('%.3F %.3F l ', ($clx)*_MPDFK, ($this->h-($cly+$clh))*_MPDFK);	// line to BL
	$s .= sprintf('%.3F %.3F l ', ($clx+$clw)*_MPDFK, ($this->h-($cly+$clh))*_MPDFK);	// line to BR
	$s .= sprintf('%.3F %.3F l ', ($clx+$clw)*_MPDFK, ($this->h-($cly))*_MPDFK);	// line to TR
	$s .= sprintf('%.3F %.3F l ', ($clx)*_MPDFK, ($this->h-($cly))*_MPDFK);	// line to TL
	$s .= ' W n ';	// Ends path no-op & Sets the clipping path
	return $s;
}


function PrintPageBackgrounds($adjustmenty=0) {
	$s = '';

	ksort($this->pageBackgrounds);
	foreach($this->pageBackgrounds AS $bl=>$pbs) {
		foreach ($pbs AS $pb) {
		  if ((!isset($pb['image_id']) && !isset($pb['gradient'])) || isset($pb['shadowonly'])) {	// Background colour or boxshadow
			if($pb['z-index']>0) {
				$this->current_layer = $pb['z-index'];
				$s .= "\n".'/OCBZ-index /ZI'.$pb['z-index'].' BDC'."\n";
			}

			if($pb['visibility']!='visible') {
				if($pb['visibility']=='printonly') 
					$s .= '/OC /OC1 BDC'."\n";
				else if($pb['visibility']=='screenonly')
					$s .= '/OC /OC2 BDC'."\n";
				else if($pb['visibility']=='hidden')
					$s .= '/OC /OC3 BDC'."\n";
			}
			// Box shadow
			if (isset($pb['shadow']) && $pb['shadow']) { $s .= $pb['shadow']."\n"; }
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }
			$s .= 'q '.$this->SetFColor($pb['col'], true)."\n";
			if ($pb['col']{0}==5) {	// RGBa
				$s .= $this->SetAlpha(ord($pb['col']{4})/100, 'Normal', true, 'F')."\n"; 
			}
			else if ($pb['col']{0}==6) {	// CMYKa
				$s .= $this->SetAlpha(ord($pb['col']{5})/100, 'Normal', true, 'F')."\n";
			}
			$s .= sprintf('%.3F %.3F %.3F %.3F re f Q',$pb['x']*_MPDFK,($this->h-$pb['y'])*_MPDFK,$pb['w']*_MPDFK,-$pb['h']*_MPDFK)."\n";
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
			if($pb['visibility']!='visible')
				$s .= 'EMC'."\n";

			if($pb['z-index']>0) {
				$s .= "\n".'EMCBZ-index'."\n";
				$this->current_layer = 0;
			}
		  }
		}
/*-- BACKGROUNDS --*/
		foreach ($pbs AS $pb) {
	 	  if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {
		 	if($pb['z-index']>0) {
				$this->current_layer = $pb['z-index'];
				$s .= "\n".'/OCGZ-index /ZI'.$pb['z-index'].' BDC'."\n";
		 	}
		 	if($pb['visibility']!='visible') {
				if($pb['visibility']=='printonly') 
					$s .= '/OC /OC1 BDC'."\n";
				else if($pb['visibility']=='screenonly')
					$s .= '/OC /OC2 BDC'."\n";
				else if($pb['visibility']=='hidden')
					$s .= '/OC /OC3 BDC'."\n";
		 	}
		  }
	 	  if (isset($pb['gradient']) && $pb['gradient']) {
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }
			$s .= $this->grad->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['stops'], $pb['colorspace'], $pb['coords'], $pb['extend'], true);
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
		  }
		  else if (isset($pb['image_id']) && $pb['image_id']) {	// Background Image
			$pb['y'] -= $adjustmenty; 
			$pb['h'] += $adjustmenty; 
			$n = count($this->patterns)+1;
			list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($pb['orig_w'], $pb['orig_h'], $pb['w'], $pb['h'], $pb['resize'], $pb['x_repeat'], $pb['y_repeat'], $pb['bpa'], $pb['size']);
			$this->patterns[$n] = array('x'=>$pb['x'], 'y'=>$pb['y'], 'w'=>$pb['w'], 'h'=>$pb['h'], 'pgh'=>$this->h, 'image_id'=>$pb['image_id'], 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$pb['x_pos'], 'y_pos'=>$pb['y_pos'], 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'itype'=>$pb['itype'], 'bpa'=>$pb['bpa']);
			$x = $pb['x']*_MPDFK;
			$y = ($this->h - $pb['y'])*_MPDFK;
			$w = $pb['w']*_MPDFK;
			$h = -$pb['h']*_MPDFK;
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }
			if ($this->writingHTMLfooter || $this->writingHTMLheader) {	// Write each (tiles) image rather than use as a pattern
				$iw = $pb['orig_w']/_MPDFK;
				$ih = $pb['orig_h']/_MPDFK;

				$w = $pb['w'];
				$h = $pb['h'];
				$x0 = $pb['x'];
				$y0 = $pb['y'];

				if (isset($pb['bpa']) && $pb['bpa']) {
					$w = $pb['bpa']['w'];
					$h = $pb['bpa']['h'];
					$x0 = $pb['bpa']['x'];
					$y0 = $pb['bpa']['y'];
				}

				if (isset($pb['size']['w']) && $pb['size']['w']) {
					$size = $pb['size'];

					if ($size['w']=='contain') {
					// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest size such that both its width and its height can fit inside the background positioning area. 
					// Same as resize==3
						$ih = $ih * $pb['bpa']['w']/$iw;
						$iw = $pb['bpa']['w'];
						if ($ih > $pb['bpa']['h']) {
							$iw = $iw * $pb['bpa']['h']/$ih;
							$ih = $pb['bpa']['h'];
						}
					}
					else if ($size['w']=='cover') {
					// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest size such that both its width and its height can completely cover the background positioning area. 
						$ih = $ih * $pb['bpa']['w']/$iw;
						$iw = $pb['bpa']['w'];
						if ($ih < $pb['bpa']['h']) {
							$iw = $iw * $ih/$pb['bpa']['h'];
							$ih = $pb['bpa']['h'];
						}
					}
					else {
						if (stristr($size['w'] ,'%')) {
							$size['w'] += 0; 
							$size['w'] /= 100; 
							$size['w'] = ($pb['bpa']['w'] * $size['w']);
						}
						if (stristr($size['h'] ,'%')) {
							$size['h'] += 0; 
							$size['h'] /= 100; 
							$size['h'] = ($pb['bpa']['h'] * $size['h']);
						}
						if ($size['w']=='auto' && $size['h']=='auto') {
							$iw = $iw;
							$ih = $ih;
						}
						else if ($size['w']=='auto' && $size['h']!='auto') {
							$iw = $iw * $size['h']/$ih;
							$ih = $size['h'];
						}
						else if ($size['w']!='auto' && $size['h']=='auto') {
							$ih = $ih * $size['w']/$iw;
							$iw = $size['w'];
						}
						else {
							$iw = $size['w'];
							$ih = $size['h'];
						}
					}
				}

				// Number to repeat
				if ($pb['x_repeat']) { $nx = ceil($pb['w']/$iw)+1; }
				else { $nx = 1; }
				if ($pb['y_repeat']) { $ny = ceil($pb['h']/$ih)+1; }
				else { $ny = 1; }

				$x_pos = $pb['x_pos'];
				if (stristr($x_pos ,'%') ) { 
					$x_pos += 0; 
					$x_pos /= 100; 
					$x_pos = ($pb['bpa']['w'] * $x_pos) - ($iw * $x_pos);
				}
				$y_pos = $pb['y_pos'];
				if (stristr($y_pos ,'%') ) { 
					$y_pos += 0; 
					$y_pos /= 100; 
					$y_pos = ($pb['bpa']['h'] * $y_pos) - ($ih * $y_pos);
				}
				if ($nx>1) {
					while($x_pos>($pb['x']-$pb['bpa']['x'])) { $x_pos -= $iw; }
				}
				if ($ny>1) {
					while($y_pos>($pb['y']-$pb['bpa']['y'])) { $y_pos -= $ih; }
				}
				for($xi=0;$xi<$nx;$xi++) {
				  for($yi=0;$yi<$ny;$yi++) {
					$x = $x0 + $x_pos + ($iw*$xi);
					$y = $y0 + $y_pos + ($ih*$yi);
					if ($pb['opacity']>0 && $pb['opacity']<1) { $opac = $this->SetAlpha($pb['opacity'],'Normal',true); }
					else { $opac = ''; }
					$s .= sprintf("q %s %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $opac,$iw*_MPDFK,$ih*_MPDFK,$x*_MPDFK,($this->h-($y+$ih))*_MPDFK,$pb['image_id']) ."\n";
				  }
				}
			}
			else {
				if (($pb['opacity']>0 || $pb['opacity']==='0') && $pb['opacity']<1) { $opac = $this->SetAlpha($pb['opacity'],'Normal',true); }
				else { $opac = ''; }
				$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $x, $y, $w, $h) ."\n";
			}
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
		  }
	 	  if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {
		 	if($pb['visibility']!='visible')
				$s .= 'EMC'."\n";

			if($pb['z-index']>0) {
				$s .= "\n".'EMCGZ-index'."\n";
				$this->current_layer = 0;
			}
		  }

		}
/*-- END BACKGROUNDS --*/
	}
	return $s;
}

function PrintTableBackgrounds($adjustmenty=0) {
	$s = '';
/*-- BACKGROUNDS --*/
	ksort($this->tableBackgrounds);
	foreach($this->tableBackgrounds AS $bl=>$pbs) {
		foreach ($pbs AS $pb) {
	 	 if ((!isset($pb['gradient']) || !$pb['gradient']) && (!isset($pb['image_id']) || !$pb['image_id'])) {
			$s .= 'q '.$this->SetFColor($pb['col'], true)."\n";
			if ($pb['col']{0}==5) {	// RGBa
				$s .= $this->SetAlpha(ord($pb['col']{4})/100, 'Normal', true, 'F')."\n"; 
			}
			else if ($pb['col']{0}==6) {	// CMYKa
				$s .= $this->SetAlpha(ord($pb['col']{5})/100, 'Normal', true, 'F')."\n";
			}
			$s .= sprintf('%.3F %.3F %.3F %.3F re %s Q',$pb['x']*_MPDFK,($this->h-$pb['y'])*_MPDFK,$pb['w']*_MPDFK,-$pb['h']*_MPDFK,'f')."\n";
		  }
	 	 if (isset($pb['gradient']) && $pb['gradient']) {
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }
			$s .= $this->grad->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['stops'], $pb['colorspace'], $pb['coords'], $pb['extend'], true);
			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
		  }
		  if (isset($pb['image_id']) && $pb['image_id']) {	// Background pattern
			$pb['y'] -= $adjustmenty; 
			$pb['h'] += $adjustmenty; 
			$n = count($this->patterns)+1;
			list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($pb['orig_w'], $pb['orig_h'], $pb['w'], $pb['h'], $pb['resize'], $pb['x_repeat'], $pb['y_repeat']);
			$this->patterns[$n] = array('x'=>$pb['x'], 'y'=>$pb['y'], 'w'=>$pb['w'], 'h'=>$pb['h'], 'pgh'=>$this->h, 'image_id'=>$pb['image_id'], 'orig_w'=>$orig_w, 'orig_h'=>$orig_h, 'x_pos'=>$pb['x_pos'], 'y_pos'=>$pb['y_pos'], 'x_repeat'=>$x_repeat, 'y_repeat'=>$y_repeat, 'itype'=>$pb['itype']);
			$x = $pb['x']*_MPDFK;
			$y = ($this->h - $pb['y'])*_MPDFK;
			$w = $pb['w']*_MPDFK;
			$h = -$pb['h']*_MPDFK;

			// mPDF 5.7.3
			if (($this->writingHTMLfooter || $this->writingHTMLheader) && (!isset($pb['clippath']) || $pb['clippath']=='') ) {
				// Set clipping path
				$pb['clippath'] = sprintf(' q 0 w %.3F %.3F m %.3F %.3F l %.3F %.3F l %.3F %.3F l %.3F %.3F l W n ', $x, $y, $x, $y+$h, $x+$w, $y+$h, $x+$w, $y, $x, $y);
			}	

			if (isset($pb['clippath']) && $pb['clippath']) { $s .= $pb['clippath']."\n"; }

			// mPDF 5.7.3
			if ($this->writingHTMLfooter || $this->writingHTMLheader) {	// Write each (tiles) image rather than use as a pattern
				$iw = $pb['orig_w']/_MPDFK;
				$ih = $pb['orig_h']/_MPDFK;

				$w = $pb['w'];
				$h = $pb['h'];
				$x0 = $pb['x'];
				$y0 = $pb['y'];

				if (isset($pb['bpa']) && $pb['bpa']) {
					$w = $pb['bpa']['w'];
					$h = $pb['bpa']['h'];
					$x0 = $pb['bpa']['x'];
					$y0 = $pb['bpa']['y'];
				}
				// At present 'bpa' (background page area) is not set for tablebackgrounds - only pagebackgrounds
				// For now, just set it as:
				else {
					$pb['bpa'] = array('x'=>$x0, 'y'=>$y0, 'w'=>$w, 'h'=>$h);
				}

				if (isset($pb['size']['w']) && $pb['size']['w']) {
					$size = $pb['size'];

					if ($size['w']=='contain') {
					// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest size such that both its width and its height can fit inside the background positioning area. 
					// Same as resize==3
						$ih = $ih * $pb['bpa']['w']/$iw;
						$iw = $pb['bpa']['w'];
						if ($ih > $pb['bpa']['h']) {
							$iw = $iw * $pb['bpa']['h']/$ih;
							$ih = $pb['bpa']['h'];
						}
					}
					else if ($size['w']=='cover') {
					// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest size such that both its width and its height can completely cover the background positioning area. 
						$ih = $ih * $pb['bpa']['w']/$iw;
						$iw = $pb['bpa']['w'];
						if ($ih < $pb['bpa']['h']) {
							$iw = $iw * $ih/$pb['bpa']['h'];
							$ih = $pb['bpa']['h'];
						}
					}
					else {
						if (stristr($size['w'] ,'%')) {
							$size['w'] += 0; 
							$size['w'] /= 100; 
							$size['w'] = ($pb['bpa']['w'] * $size['w']);
						}
						if (stristr($size['h'] ,'%')) {
							$size['h'] += 0; 
							$size['h'] /= 100; 
							$size['h'] = ($pb['bpa']['h'] * $size['h']);
						}
						if ($size['w']=='auto' && $size['h']=='auto') {
							$iw = $iw;
							$ih = $ih;
						}
						else if ($size['w']=='auto' && $size['h']!='auto') {
							$iw = $iw * $size['h']/$ih;
							$ih = $size['h'];
						}
						else if ($size['w']!='auto' && $size['h']=='auto') {
							$ih = $ih * $size['w']/$iw;
							$iw = $size['w'];
						}
						else {
							$iw = $size['w'];
							$ih = $size['h'];
						}
					}
				}

				// Number to repeat
				if (isset($pb['x_repeat']) && $pb['x_repeat']) { $nx = ceil($pb['w']/$iw)+1; }
				else { $nx = 1; }
				if (isset($pb['y_repeat']) && $pb['y_repeat']) { $ny = ceil($pb['h']/$ih)+1; }
				else { $ny = 1; }

				$x_pos = $pb['x_pos'];
				if (stristr($x_pos ,'%') ) { 
					$x_pos += 0; 
					$x_pos /= 100; 
					$x_pos = ($pb['bpa']['w'] * $x_pos) - ($iw * $x_pos);
				}
				$y_pos = $pb['y_pos'];
				if (stristr($y_pos ,'%') ) { 
					$y_pos += 0; 
					$y_pos /= 100; 
					$y_pos = ($pb['bpa']['h'] * $y_pos) - ($ih * $y_pos);
				}
				if ($nx>1) {
					while($x_pos>($pb['x']-$pb['bpa']['x'])) { $x_pos -= $iw; }
				}
				if ($ny>1) {
					while($y_pos>($pb['y']-$pb['bpa']['y'])) { $y_pos -= $ih; }
				}
				for($xi=0;$xi<$nx;$xi++) {
				  for($yi=0;$yi<$ny;$yi++) {
					$x = $x0 + $x_pos + ($iw*$xi);
					$y = $y0 + $y_pos + ($ih*$yi);
					if ($pb['opacity']>0 && $pb['opacity']<1) { $opac = $this->SetAlpha($pb['opacity'],'Normal',true); }
					else { $opac = ''; }
					$s .= sprintf("q %s %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $opac,$iw*_MPDFK,$ih*_MPDFK,$x*_MPDFK,($this->h-($y+$ih))*_MPDFK,$pb['image_id']) ."\n";
				  }
				}
			}
			else {
				if (($pb['opacity']>0 || $pb['opacity']==='0') && $pb['opacity']<1) { $opac = $this->SetAlpha($pb['opacity'],'Normal',true); }
				else { $opac = ''; }
				$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $x, $y, $w, $h) ."\n";
			}

			if (isset($pb['clippath']) && $pb['clippath']) { $s .= 'Q'."\n"; }
		  }
		}
	}
/*-- END BACKGROUNDS --*/
	return $s;
}

function BeginLayer($id) {
	if($this->current_layer>0) $this->EndLayer();
	if ($id < 1) { return false; }
	if (!isset($this->layers[$id])) { 
		$this->layers[$id] = array('name'=>'Layer '.($id) );
		if (($this->PDFA || $this->PDFX)) { $this->PDFAXwarnings[] = "Cannot use layers when using PDFA or PDFX"; return ''; }
		else if (!$this->PDFA && !$this->PDFX) { $this->pdf_version='1.5'; }
	}
	$this->current_layer = $id;
	$this->_out('/OCZ-index /ZI'.$id.' BDC');

	$this->pageoutput[$this->page] = array();
}

function EndLayer() {
	if($this->current_layer>0) {
		$this->_out('EMCZ-index');
		$this->current_layer = 0;
	}
}


function AddPageByArray($a) {
	if (!is_array($a)) { $a = array(); }
	$orientation = (isset($a['orientation']) ? $a['orientation'] : '');
	$condition = (isset($a['condition']) ? $a['condition'] : (isset($a['type']) ? $a['type'] : ''));
	$resetpagenum = (isset($a['resetpagenum']) ? $a['resetpagenum'] : '');
	$pagenumstyle = (isset($a['pagenumstyle']) ? $a['pagenumstyle'] : '');
	$suppress = (isset($a['suppress']) ? $a['suppress'] : '');
	$mgl = (isset($a['mgl']) ? $a['mgl'] : (isset($a['margin-left']) ? $a['margin-left'] : ''));
	$mgr = (isset($a['mgr']) ? $a['mgr'] : (isset($a['margin-right']) ? $a['margin-right'] : ''));
	$mgt = (isset($a['mgt']) ? $a['mgt'] : (isset($a['margin-top']) ? $a['margin-top'] : ''));
	$mgb = (isset($a['mgb']) ? $a['mgb'] : (isset($a['margin-bottom']) ? $a['margin-bottom'] : ''));
	$mgh = (isset($a['mgh']) ? $a['mgh'] : (isset($a['margin-header']) ? $a['margin-header'] : ''));
	$mgf = (isset($a['mgf']) ? $a['mgf'] : (isset($a['margin-footer']) ? $a['margin-footer'] : ''));
	$ohname = (isset($a['ohname']) ? $a['ohname'] : (isset($a['odd-header-name']) ? $a['odd-header-name'] : ''));
	$ehname = (isset($a['ehname']) ? $a['ehname'] : (isset($a['even-header-name']) ? $a['even-header-name'] : ''));
	$ofname = (isset($a['ofname']) ? $a['ofname'] : (isset($a['odd-footer-name']) ? $a['odd-footer-name'] : ''));
	$efname = (isset($a['efname']) ? $a['efname'] : (isset($a['even-footer-name']) ? $a['even-footer-name'] : ''));
	$ohvalue = (isset($a['ohvalue']) ? $a['ohvalue'] : (isset($a['odd-header-value']) ? $a['odd-header-value'] : 0));
	$ehvalue = (isset($a['ehvalue']) ? $a['ehvalue'] : (isset($a['even-header-value']) ? $a['even-header-value'] : 0));
	$ofvalue = (isset($a['ofvalue']) ? $a['ofvalue'] : (isset($a['odd-footer-value']) ? $a['odd-footer-value'] : 0));
	$efvalue = (isset($a['efvalue']) ? $a['efvalue'] : (isset($a['even-footer-value']) ? $a['even-footer-value'] : 0));
	$pagesel = (isset($a['pagesel']) ? $a['pagesel'] : (isset($a['pageselector']) ? $a['pageselector'] : ''));
	$newformat = (isset($a['newformat']) ? $a['newformat'] : (isset($a['sheet-size']) ? $a['sheet-size'] : ''));

	$this->AddPage($orientation,$condition,$resetpagenum, $pagenumstyle, $suppress,$mgl,$mgr,$mgt,$mgb,$mgh,$mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue,$pagesel,$newformat);

}

// mPDF 6 pagebreaktype
function _preForcedPagebreak($pagebreaktype) {
	if ($pagebreaktype == 'cloneall') {
		// Close any open block tags
		$arr=array();
		$ai=0;
		for ($b=$this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag'],$arr,$ai); }
		if ($this->blklvl==0 && !empty($this->textbuffer))  {	//Output previously buffered content
			$this->printbuffer($this->textbuffer, 1);
      			$this->textbuffer=array(); 
		}
	}
	else if ($pagebreaktype == 'clonebycss') {
		// Close open block tags whilst box-decoration-break==clone
		$arr=array();
		$ai=0;
		for ($b=$this->blklvl;$b>0;$b--) { 
			if (isset($this->blk[$b]['box_decoration_break']) && $this->blk[$b]['box_decoration_break']=='clone') { $this->CloseTag($this->blk[$b]['tag'],$arr,$ai); }
			else { 
				if ($b==$this->blklvl && !empty($this->textbuffer))  {	//Output previously buffered content
					$this->printbuffer($this->textbuffer, 1);
					$this->textbuffer=array(); 
				}
				break; 
			}
		}
	}
	else if (!empty($this->textbuffer))  {	//Output previously buffered content
   		$this->printbuffer($this->textbuffer, 1);
      	$this->textbuffer=array(); 
	}
}

// mPDF 6 pagebreaktype
function _postForcedPagebreak($pagebreaktype, $startpage, $save_blk, $save_blklvl) {
	if ($pagebr