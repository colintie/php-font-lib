<?php
/**
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library in the file LICENSE.LGPL; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307 USA
 *
 * Alternatively, you may distribute this software under the terms of the
 * PHP License, version 3.0 or later.  A copy of this license should have
 * been distributed with this file in the file LICENSE.PHP .  If this is not
 * the case, you can obtain a copy at http://www.php.net/license/3_0.txt.
 *
 * @link http://php-font-lib.googlecode.com/
 * @author Fabien M�nager
 */

/* $Id$ */

$dir = dirname(__FILE__);
require_once "$dir/font_binary_stream.cls.php";
require_once "$dir/font_truetype_table_directory_entry.cls.php";
require_once "$dir/font_truetype_header.cls.php";
require_once "$dir/font_table.cls.php";
require_once "$dir/adobe_font_metrics.cls.php";

class Font_TrueType extends Font_Binary_Stream {
  /**
   * @var Font_TrueType_Header
   */
  public $header = array();
  
  private $tableOffset = 0; // Used for TTC
  
  private static $raw = false;
  
  protected $directory = array();
  protected $data = array();
  
  static $nameIdCodes = array(
    0  => "Copyright",
    1  => "FontName",
    2  => "FontSubfamily",
    3  => "UniqueID",
    4  => "FullName",
    5  => "Version",
    6  => "PostScriptName",
    7  => "Trademark",
    8  => "Manufacturer",
    9  => "Designer",
    10 => "Description",
    11 => "FontVendorURL",
    12 => "FontDesignerURL",
    13 => "LicenseDescription",
    14 => "LicenseURL",
 // 15
    16 => "PreferredFamily",
    17 => "PreferredSubfamily",
    18 => "CompatibleFullName",
    19 => "SampleText",
  );
  
  static $platforms = array(
    0 => "Unicode",
    1 => "Macintosh",
 // 2 =>  Reserved
    3 => "Microsoft",
  );
  
  static $plaformSpecific = array(
    // Unicode
    0 => array(
      0 => "Default semantics",
      1 => "Version 1.1 semantics",
      2 => "ISO 10646 1993 semantics (deprecated)",
      3 => "Unicode 2.0 or later semantics",
    ),
    
    // Macintosh
    1 => array(
      0 => "Roman",
      1 => "Japanese",
      2 => "Traditional Chinese",
      3 => "Korean",
      4 => "Arabic",  
      5 => "Hebrew",  
      6 => "Greek", 
      7 => "Russian", 
      8 => "RSymbol", 
      9 => "Devanagari",  
      10 => "Gurmukhi",  
      11 => "Gujarati",  
      12 => "Oriya", 
      13 => "Bengali", 
      14 => "Tamil", 
      15 => "Telugu",
      16 => "Kannada",
      17 => "Malayalam",
      18 => "Sinhalese",
      19 => "Burmese",
      20 => "Khmer",
      21 => "Thai",
      22 => "Laotian",
      23 => "Georgian",
      24 => "Armenian",
      25 => "Simplified Chinese",
      26 => "Tibetan",
      27 => "Mongolian",
      28 => "Geez",
      29 => "Slavic",
      30 => "Vietnamese",
      31 => "Sindhi",
    ),
    
    // Microsoft
    3 => array(
      0 => "Symbol",
      1 => "Unicode BMP (UCS-2)",
      2 => "ShiftJIS",
      3 => "PRC",
      4 => "Big5",
      5 => "Wansung",
      6 => "Johab",
  //  7 => Reserved
  //  8 => Reserved
  //  9 => Reserved
      10 => "Unicode UCS-4",
    ),
  );
  
  static $macCharNames = array(
    ".notdef", ".null", "CR",
    "space", "exclam", "quotedbl", "numbersign",
    "dollar", "percent", "ampersand", "quotesingle",
    "parenleft", "parenright", "asterisk", "plus",
    "comma", "hyphen", "period", "slash",
    "zero", "one", "two", "three",
    "four", "five", "six", "seven",
    "eight", "nine", "colon", "semicolon",
    "less", "equal", "greater", "question",
    "at", "A", "B", "C", "D", "E", "F", "G",
    "H", "I", "J", "K", "L", "M", "N", "O",
    "P", "Q", "R", "S", "T", "U", "V", "W",
    "X", "Y", "Z", "bracketleft",
    "backslash", "bracketright", "asciicircum", "underscore",
    "grave", "a", "b", "c", "d", "e", "f", "g",
    "h", "i", "j", "k", "l", "m", "n", "o",
    "p", "q", "r", "s", "t", "u", "v", "w",
    "x", "y", "z", "braceleft",
    "bar", "braceright", "asciitilde", "Adieresis",
    "Aring", "Ccedilla", "Eacute", "Ntilde",
    "Odieresis", "Udieresis", "aacute", "agrave",
    "acircumflex", "adieresis", "atilde", "aring",
    "ccedilla", "eacute", "egrave", "ecircumflex",
    "edieresis", "iacute", "igrave", "icircumflex",
    "idieresis", "ntilde", "oacute", "ograve",
    "ocircumflex", "odieresis", "otilde", "uacute",
    "ugrave", "ucircumflex", "udieresis", "dagger",
    "degree", "cent", "sterling", "section",
    "bullet", "paragraph", "germandbls", "registered",
    "copyright", "trademark", "acute", "dieresis",
    "notequal", "AE", "Oslash", "infinity",
    "plusminus", "lessequal", "greaterequal", "yen",
    "mu", "partialdiff", "summation", "product",
    "pi", "integral", "ordfeminine", "ordmasculine",
    "Omega", "ae", "oslash", "questiondown",
    "exclamdown", "logicalnot", "radical", "florin",
    "approxequal", "increment", "guillemotleft", "guillemotright",
    "ellipsis", "nbspace", "Agrave", "Atilde",
    "Otilde", "OE", "oe", "endash",
    "emdash", "quotedblleft", "quotedblright", "quoteleft",
    "quoteright", "divide", "lozenge", "ydieresis",
    "Ydieresis", "fraction", "currency", "guilsinglleft",
    "guilsinglright", "fi", "fl", "daggerdbl",
    "periodcentered", "quotesinglbase", "quotedblbase", "perthousand",
    "Acircumflex", "Ecircumflex", "Aacute", "Edieresis",
    "Egrave", "Iacute", "Icircumflex", "Idieresis",
    "Igrave", "Oacute", "Ocircumflex", "applelogo",
    "Ograve", "Uacute", "Ucircumflex", "Ugrave",
    "dotlessi", "circumflex", "tilde", "macron",
    "breve", "dotaccent", "ring", "cedilla",
    "hungarumlaut", "ogonek", "caron", "Lslash",
    "lslash", "Scaron", "scaron", "Zcaron",
    "zcaron", "brokenbar", "Eth", "eth",
    "Yacute", "yacute", "Thorn", "thorn",
    "minus", "multiply", "onesuperior", "twosuperior",
    "threesuperior", "onehalf", "onequarter", "threequarters",
    "franc", "Gbreve", "gbreve", "Idot",
    "Scedilla", "scedilla", "Cacute", "cacute",
    "Ccaron", "ccaron", "dmacron"
  );
  
  function getTable(){
    $this->parseTableEntries();
    return $this->directory;
  }
  
  function setTableOffset($offset) {
    $this->tableOffset = $offset;
  }
  
  function parse() {
    $this->parseTableEntries();
    
    foreach($this->directory as $tag => $table) {
      $this->readTable($tag);
    }
  }
  
  function encode($tags = array()){
    if (!self::$raw) {
      $tags += array("head", "hhea", "cmap", "hmtx", "loca", "maxp", "name", "post", "glyf");
    }
    else {
      $tags = array_keys($this->directory);
    }
    
    $num_tables = count($tags);
    $n = 16;// @todo
    
    Font::d("Tables : ".implode(", ", $tags));
    
    $entries = array();
    foreach($tags as $tag) {
      if (!isset($this->directory[$tag])) {
        Font::d("  >> '$tag' table doesn't exist");
        continue;
      }
      
      $entries[$tag] = $this->directory[$tag];
    }
    
    $this->header->encode();
    
    $directory_offset = $this->pos();
    $offset = $directory_offset + $num_tables * $n;
    $this->seek($offset);
    
    $i = 0;
    foreach($entries as $tag => $entry) {
      $entry->encode($directory_offset + $i * $n);
      $i++;
    }
  }
  
  function parseHeader(){
		if (!empty($this->header)) {
      return;
		}
		
    $this->seek($this->tableOffset);
    
    $this->header = new Font_TrueType_Header($this);
    $this->header->parse();
  }
  
  function parseTableEntries(){
    $this->parseHeader();
    
    if (!empty($this->directory)) {
      return;
    }
    
    $class = get_class($this)."_Table_Directory_Entry";
    
    for($i = 0; $i < $this->header->data["numTables"]; $i++) {
      $entry = new $class($this);
      $this->directory[$entry->tag] = $entry;
    }
  }
  
  function normalizeFUnit($value, $base = 1000){
    return round($value * ($base / $this->getData("head", "unitsPerEm")));
  }
  
  protected function readTable($tag) {
    $this->parseTableEntries();
    
    if (!self::$raw) {
      $name_canon = preg_replace("/[^a-z0-9]/", "", strtolower($tag));
      $class_file = dirname(__FILE__)."/font_table_$name_canon.cls.php";
      
      if (!isset($this->directory[$tag]) || !file_exists($class_file)) {
        return;
      }
      
      require_once $class_file;
      $class = "Font_Table_$name_canon";
    }
    else {
      $class = "Font_Table";
    }
    
    $table = new $class($this->directory[$tag]);
    $table->parse();
    
    $this->data[$tag] = $table;
  }
  
  public function getData($name, $key = null) {
    $this->parseTableEntries();
    
    if (empty($this->data[$name])) {
      $this->readTable($name);
    }
    
    if (!isset($this->data[$name])) {
      return null;
    }
    
    if (!$key) {
      return $this->data[$name]->data;
    }
    else {
      return $this->data[$name]->data[$key];
    }
  }
  
  function saveAdobeFontMetrics($file, $encoding = null) {
    $afm = new Adobe_Font_Metrics($this);
    $afm->write($file, $encoding);
  }
}
