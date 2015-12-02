while ($owl->read())
{
switch ($state)
{
	case "OUT":
		if ($owl->nodeType==XMLReader::ELEMENT&&$owl->name=="owl:ObjectProperty")
		{
			$state = "IN";
		}
		break;
	case "IN":
		if ($owl->nodeType==XMLReader::END_ELEMENT&&$owl->name=="owl:ObjectProperty")
		{
			$state = "OUT";
		}
		else if ($owl->nodeType==XMLReader::ELEMENT)
		{
			
			
			switch($owl->name)
			{
				case "rdfs:seeAlso":
					for ($n=0;$n<$owl->attributeCount;$n++)
					{
						$owl->moveToAttributeNo($n);
						print $owl->name . ' = "' . $owl->value . "\"\n";
						print "this is print 1";
					}
					break;
				case "rdfs:subPropertyOf":
					for ($n=0;$n<$owl->attributeCount;$n++)
					{
						$owl->moveToAttributeNo($n);
						print $owl->name . ' = "' . $owl->value . "\"\n";
						print "this is print 2";
					}					
					break;
			}
		}
		$owl->read();
		print  $owl->value ."\"\n";
		print "this is print 3";
					break;
}
	
