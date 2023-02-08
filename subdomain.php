<?php
function createSubdomain($nom)
{
$xmldoc = new DomDocument('1.0', 'UTF-8');
$xmldoc->formatOutput = true;
$packet = $xmldoc->createElement('packet');
$packet->setAttribute('version', '1.6.9.1');
$xmldoc->appendChild($packet);
$subdomain = $xmldoc->createElement('subdomain');
$packet->appendChild($subdomain);
$add = $xmldoc->createElement('add');
$subdomain->appendChild($add);
$parent = $xmldoc->createElement('parent', 'edgdevwork.com');
$add->appendChild($parent);
$name = $xmldoc->createElement('name', $nom);
$add->appendChild($name);
$add->appendChild($xmldoc->createElement('home', '/'.$nom.''));
$property = $xmldoc->createElement('property');
$property->appendChild($xmldoc->createElement('name', 'ssi'));
$property->appendChild($xmldoc->createElement('value', 'true'));
$add->appendChild($property);
return $xmldoc;

}
//And the main part :

$host = '143.198.207.123';
$login = 'root';
$password = 'd5F^JQ4ufd';

echo "<br/>";
$curl7 = curlInit($host, $login, $password);
$contenu = createSubDomain('bilawal')->saveXML();
echo htmlspecialchars($contenu);
$reponse = sendRequest($curl7, $contenu);
echo "<br/>";
echo htmlspecialchars($reponse);
echo "<br/>";
echo "check";

//The function curlInit and sendRequest are those used in the examples provided on this site, but I put them here just in case :

/**
* Prepares CURL to perform Plesk API request
* @return resource
*/
function curlInit($host, $login, $password)
{
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://{$host}:8443/enterprise/control/agent.php");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_HTTPHEADER,
array("HTTP_AUTH_LOGIN: {$login}",
"HTTP_AUTH_PASSWD: {$password}",
"HTTP_PRETTY_PRINT: TRUE",
"Content-Type: text/xml")
);

return $curl;
}
/**
* Performs a Plesk API request, returns raw API response text
*
* @return string
* @throws ApiRequestException
*/
function sendRequest($curl, $packet)
{
curl_setopt($curl, CURLOPT_POSTFIELDS, $packet);

$result = curl_exec($curl);

if (curl_errno($curl)) {
$errmsg = curl_error($curl);
$errcode = curl_errno($curl);
curl_close($curl);
throw new ApiRequestException($errmsg, $errcode);
}
curl_close($curl);
return $result;
}