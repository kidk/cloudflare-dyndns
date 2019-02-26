<?php
require_once 'vendor/autoload.php';

#
# Helper function for REST calls
#
function get($url, $headers = [], $data = []) {

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    if (is_object($data) || (is_array($data) && count($data) > 0)) {
        $headers[] = 'X-HTTP-Method-Override: PUT';
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    return curl_exec($curl);
}

#
# Command line arguments
#
$cli = new Commando\Command();
$cli->option('u')
    ->aka('username')
    ->require()
    ->describedAs('Cloudflare username');
$cli->option('k')
    ->aka('api_key')
    ->require()
    ->describedAs('Cloudflare API key');
$cli->option('z')
    ->aka('zone')
    ->require()
    ->describedAs('Cloudflare zone');
$cli->option('d')
    ->aka('domain')
    ->require()
    ->describedAs('Domain name');
$cli->option('t')
    ->aka('timeout')
    ->default(60)
    ->describedAs('Timeout in minutes before checking');

#
# Init
#
echo "Starting cloudflare-dyndns".PHP_EOL;
echo "Options: ".PHP_EOL;
var_dump($cli->getFlagValues());
echo PHP_EOL;

$key = new Cloudflare\API\Auth\APIKey($cli['username'], $cli['api_key']);
$adapter = new Cloudflare\API\Adapter\Guzzle($key);
$zones = new \Cloudflare\API\Endpoints\Zones($adapter);


#
# Check loop
#
echo "Starting run".PHP_EOL;
while(True) {
    // Retrieve latest record from Cloudflare
    $dns = new \Cloudflare\API\Endpoints\DNS($adapter);
    $data = $dns->listRecords($cli['zone'], 'A', $cli['domain']);
    $currentIp = $data->result[0]->content;
    $recordId = $data->result[0]->id;

    // Retrieve remote IP from server
    $externalData = get('http://checkip.dyndns.com/');
    preg_match("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/", $externalData, $matches);
    $outsideIp = $matches[0];

    // Compare and update if needed
    if ($currentIp !== $outsideIp) {
        echo "IP change detected: {$currentIp} => {$outsideIp}".PHP_EOL;
        $dns->updateRecordDetails($cli['zone'], $recordId, [
            'type' => 'A',
            'name' => $cli['domain'],
            'content' => $outsideIp,
        ]);
        echo "IP has been updated".PHP_EOL;
    } else {
        echo "IP is up to date".PHP_EOL;
    }

    // Sleep before next check
    $time = $cli['timeout'] * 60;
    echo "Sleeping ".$time." seconds.".PHP_EOL;
    sleep($time);
}


