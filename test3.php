<?php

/**
 * @param string $xml
 * @param string $description
 * @return array
 */
function getTimestampsByDescription(string $xml, string $description) : array
{
    $timestamps = [];
    $xmlElement = simplexml_load_string($xml);
    foreach ($xmlElement->event as $event) {
        if ((string)$event->description === $description) {
            $timestamps[] = (int)$event['timestamp'];
        }
    }
    return $timestamps;
}
$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<log>
    <event timestamp="1614285589">
        <description>Intrusion detected</description>
    </event>
    <event timestamp="1614286432">
        <description>Intrusion ended</description>
    </event>
</log>
XML;
print_r(getTimestampsByDescription($xml, 'Intrusion ended'));