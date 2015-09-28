<?php

$csv = new SimpleTable();
$dom = DOMDocument::load('supplementalData.xml');

$csv->load_byDomAttribs(
	$dom
	,"/supplementalData/languageData/language[@territories]"
	,['type','alt','territories']
)->save('scrapped/languageData_bylang.csv');


$csv->load_byDomAttribs(
	$dom
	,"/supplementalData/territoryContainment/group[not(@status)]"
	,['type','contains']
)->save('scrapped/territoryContainment.csv');

$popPercMin=1;
$csv->load_byDomFunc(
	$dom
	,function ($xp,$node,$head) use ($popPercMin) {
		$oficiais = [];
		$etc      = [];
		$country = $node->getAttribute('type');
		$pop = $node->getAttribute('population');
		$lst = $xp->query("languagePopulation",$node);  // subquery
		if ($lst->length) foreach($lst as $g) {
			$lang = $g->getAttribute('type');
			$popPerc = $g->getAttribute('populationPercent');
			if ($g->getAttribute('officialStatus')=='official')
				$oficiais[] = $lang;
			elseif ( (int) $popPerc >= $popPercMin )
				$etc[] = $lang;
		} // if for $g
		return [ $country, $pop, join(' ',$oficiais), join(' ',$etc) ];
	}// func
	,"/supplementalData/territoryInfo/territory"
	,['country','population','langs_official', 'langs_other']
)->save('scrapped/territoryInfo.csv');

// testar -> joinWith($csv2, [1=>2,2=>1], [3,4]) -> save();
//country,langs_official,langs_otherGt1perc
//FALTA (inclui tag -BR, etc. da lang oficial caso exista IETF)
// ... tag parentLocales for lang complement


//////////////////////////////
////////  LIB UTIL  //////////

class SimpleTable {  // W3C table
	var $in_format = 'CSV:,:"'; // I/O format expressed as URN 
	var $out_format = 'CSV';    // I/O standard
	var $buffsize = 4000;
	var $file_in = 'php:stdin'; //STDIN
	var $file_out = 'php:stdout'; //STDOUT

	var $tab_body = [];
	var $tab_head=NULL;

	var $stdFormats = [ // standard tabular simple formats
		'CSV'=>['text',',','"'], 'TSV'=>['text',"\t",'"'], 'PIPES'=>['text','|','"'],
		'XML'=>['xml','table','thead','tbody','tr','td'], 
		'DOM'=>['dom','table','thead','tbody','tr','td'], 
		'HTML'=>['xml','table','thead','tbody','tr','td'],
		'XSQL'=>['xml','table','row','@col'],
		'JSON'=>['json','table','head','body']
	];
	var $frmt = NULL;  // (private) for current format. 

	/**
	 * Private. Split format declaration string into ameable parts, using defaults for omissions.
	 * Returns [name,ftype,sep,dlm] for CSV family, [name,ftype,tableTag,tbodyTag,etc.] for XML family
	 */
	function getFrmt($strFormat='') {
		$p = strpos(':',$strFormat);
		if ($strFormat && $p) {
			$name = substr($strFormat,0,$p);
			$fetc = substr($strFormat,$p+1);
		} else
			list($name,$fetc) = [$strFormat,''];
		$name = $name? strtoupper($name): 'CSV';
		$this->frmt = [$name];
		if (isset($this->stdFormats[$name])) {
			$parts = $this->stdFormats[$name];
			$sparts = explode(':',$fetc);
			for ($i=0; $i<count($parts); $i++) 
				$this->frmt[]=(isset($sparts[$i]) && $sparts[$i])? $sparts[$i]: $parts[$i];
		} else 
			die("\n-- ERROR: format '$name' unknow.\n");	
		return $this->frmt;
	}

	/**
	 * Generic load method, for CSV or any other textual standard format of $this->stdFormats.
	 */
	function load($file='',$funcGet=NULL,$format='') {
		//if (!$file) $file = $this->file_in;
		$frmt = $this->getFrmt($format? $format: $this->in_format);
		$fname = array_pop($frmt);
		die("\nformat = $fname");
		$tab_body = []; // FALTA TESTAR COM ponteiro externo
		$this->tab_head = NULL;
		switch ($fname) {
		case 'CSV':
		case 'TSV':
		case 'PIPES':
		default:
			// more input (future use) $testaLen=0,$checkCommentLines=0
			$testaLen=0;
			if ($funcGet===NULL) {
				$funcGet = function ($tmp,&$tab_body) { $tab_body[] = $tmp; }; 
			}
			if ((!$file && ($handle=STDIN)) || ($handle = fopen($file, "r")) !== FALSE) {
				$this->tab_head = fgetcsv($handle, $this->buffsize, $sep); // , $dlm
				while (($tmp = fgetcsv($handle, $this->buffsize, $sep)) !== FALSE)
				   if ( $funcGet!==NULL && (!$testaLen ||strlen($tmp[0])>$testaLen) )
					$funcGet($tmp,$tab_body);
				fclose($handle);
			} else
				die("\nFile ERROR: $file\n"); //return [];
			break;
		case 'HTML':
		case 'XML':
			die("\nload '$fname' under construction.\n");
			break;
		} // switch
		$this->tab_body = $tab_body; // REVISAR, cópia é desperdicio.
		return $this;
	}


	/**
	 * Generic save method, for CSV or other requested formats.
	 */
	function save($file='',$format='') {
		//if (!$file) $file = $this->file_out;
		$frmt = $this->getFrmt($format? $format: $this->out_format);
		$fname = array_shift($frmt);
		if ($frmt[0]=='dom') die("\n ERROR: request for non-textual format, DOM.\n");
		switch ($fname) {
		case 'CSV':
		case 'TSV':
		case 'PIPES':
		default:
			$fp = (!$file)? STDOUT: fopen($file, 'w');
			list($ftype,$sep,$dlm) = $frmt;
			//die("\nDEBUG FRMT $fname = $ftype,sep=$sep, dlm=$dlm\n");
			if ($this->tab_head!==NULL && count($this->tab_head)) 
				fputcsv($fp, $this->tab_head, $sep, $dlm);
			foreach($this->tab_body as $r)
				fputcsv($fp, $r, $sep, $dlm);
			return fclose($fp);

		case 'HTML':
		case 'XML':
			die("\n save '$fname' under construction.\n");
		} // switch
	}

	//// LOAD utilities for non-tabular data (need similar for JSON)

	/**
	 * (specialized) Load by DOMDocument and XPath query pointing "to line" elements 
	 * and head with attribute names.
	 */
	function load_byDomAttribs($dom,$xpathQuery,$head=NULL) {
		$xp = new DOMXpath($dom);
		$this->tab_head = $head; // if NULL error (scan condition in future)
		$this->tab_body  = [];
		$lst = $xp->query($xpathQuery);
		if ($lst->length) foreach($lst as $g) {
			$item = [];
			foreach($this->tab_head as $a) {
				$item[] = $g->getAttribute($a);
			}
			$this->tab_body[] =  $item;
		} // if for $g
		return $this;
	}

	/**
	 * (specialized) Load by DOMDocument and XPath query pointing "to line" elements.
	 */
	function load_byDomFunc($dom,$domFunc,$xpathQuery,$head=NULL) {
		$xp = new DOMXpath($dom);
		$this->tab_head = $head;
		$this->tab_body  = [];
		$lst = $xp->query($xpathQuery);
		if ($lst->length) foreach($lst as $g) {
			$item = $domFunc($xp,$g,$head);
			$this->tab_body[] =  $item;
		} // if for $g
		return $this;
	}

	// future load_byMongoDB, load_byPDO, .. 
	
	// future: like inner-join, left-join or right-join
	function joinWith(SimpleTable $other,$keyPairs,$addFields) {
		// join by ['thisKey1'=>'otherKey1'] or indexes [1=>2, 3=1]
		// transform this into a big table
		return $this;
	}

} // class
