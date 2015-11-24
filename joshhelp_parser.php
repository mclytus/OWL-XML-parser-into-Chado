<?php
/**
 * @files
 * @add file from OWL OBO header
 */

// Loading the XML file
$owl= new XMLReader();

// Open the OWL file for parsing.
$owl->open('ro.owl');
$section = array();
while ($owl->read()) 
{
	if ($owl->nodeType==XMLReader::ELEMENT&&$owl->name=="owl:Ontology")
	{
		while ($owl->nodeType!=XMLReader::END_ELEMENT||$owl->name!="owl:Ontology")
		{
			if ($owl->nodeType==XMLReader::ELEMENT)
			{
				switch ($owl->name)
				{
					case 'foaf:homepage':
						
						break;
					case 'dc:title':
						break;
					case 'dc:description':
						break;
				}
			}
			if ($owl->name=="foaf:homepage"&&$owl->nodeType==XMLReader::ELEMENT)
			{
				for ($n=0;$n<$owl->attributeCount;$n++)
				{
					$owl->moveToAttributeNo($n);
					print $owl->name . ' = "' . $owl->value . "\"\n";
				}
				$owl->read();
				print $owl->value . "\n";
			}
			$owl->read();
		}
	}
	if ($owl->nodeType==XMLReader::ELEMENT&&$owl->name=="rdf:Description")
	{
		while ($owl->nodeType!=XMLReader::END_ELEMENT||$owl->name!="rdf:Description")
		{
			if ($owl->nodeType==XMLReader::ELEMENT)
			{
				if ($owl->hasAttributes&&$owl->nodeType==XMLReader::ELEMENT)
				{
					for ($n=0;$n<$owl->attributeCount;$n++)
					{	
						$owl->moveToFirstAttribute();
						print $owl->name ."\n";
						print $owl->value ."\n";
					}
				}
			}
			$owl->read();
		}
	}
}
	//if ($owl->nodeType == XMLReader::ELEMENT) 
	//{
// Build the section array.

//if ($owl->hasAttributes&&$owl->nodeType==XMLReader::ELEMENT)
//{
	//for ($n=0;$n<$owl->attributeCount;$n++)
	//{
//	$owl->moveToFirstAttribute();
//	print $owl->name . "\n";
//		print $owl->value . "\n";
	//}
//}
// First, get the attributes.
				
// Second, get the value.
		
// Third, get the children.
			
// Deal with each section of OWL.
/*		switch ($section['name']) {
			case 'owl:AnnotationProperty':
				handleAnnotationProperty($section);
				break;
				
		}*/		
	//}	
//}

//print_r($section);

//$section = array();
//$section['attributes'][] = $owl->getAttribute();		

// /**
//  * 
//  */
// function sectionAttributes(&$section) 
// {
// 	$owl->getAttribute($section['attributes']);
// 	print_r($section['attributes']);
// 	exit();

// }

// /**
//  *
//  */
// function handleAnnotationProperty(&$section)
// {

// }
?>
