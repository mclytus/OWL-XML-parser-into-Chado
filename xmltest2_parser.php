<?php
/**
 * @files
 * add file OWl header data
 */

// This file is the code for using the switch statement for each section of OWL XML reader

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
			if ($owl->name==""&&$owl->nodeType==XMLReader::ELEMENT)
			{
				for ($n=0;$n<$owl->attributeCount;$n++)
				{
					$owl->moveToAttributeNo($n);
					print $owl->name . ' = "' . $owl->value . "\"\n";
				}
				$owl->read();
				print $owl->value . "\n";
			}
		
				switch ($owl->name)
				{
					case 'foaf:homepage':
						for ($n=0;$n<$owl->attributeCount;$n++)
						{
							$owl->moveToAttributeNo($n);
							print $owl->name . ' = "' . $owl->value . "\"\n";
						}
						break;
					case 'dc:title':
						for ($n=0;$n<$owl->attributeCount;$n++)
						{
							$owl->moveToAttributeNo($n);
							print $owl->name . ' = "' . $owl->value . "\"\n";
						}
						break;
					case 'dc:description':
						for ($n=0;$n<$owl->attributeCount;$n++)
						{
							$owl->moveToAttributeNo($n);
							print $owl->name . ' = "' . $owl->value . "\"\n";
						}
						break;
					case 'dc:source':
						for ($n=0;$n<$owl->attributeCount;$n++)
						{
							$owl->moveToAttributeNo($n);
							print $owl->name . ' = "' . $owl->value . "\"\n";
						}	
						break;
					case 'owl:imports':
						for ($n=0;$n<$owl->attributeCount;$n++)
						{
							$owl->moveToAttributeNo($n);
							print $owl->name . ' = "' . $owl->value . "\"\n";
						}
						break;
					case 'owl:versionIRI':
						for ($n=0;$n<$owl->attributeCount;$n++)
						{
							$owl->moveToAttributeNo($n);
							print $owl->name . ' = "' . $owl->value . "\"\n";
						}
						break;
				}
				$owl->read();
				print $owl->value . "\n";
			}
		}
	}	
?>
