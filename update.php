<?php
require_once 'vendor/autoload.php';

#
# Helper function for REST calls
#
function get($url, $headers = [], $data = []) {

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    if (count($data) > 0) {
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
    ->default(5)
    ->describedAs('Timeout in minutes before checking');

#
# Check function
#
function check($zone, $domain, $username, $api_key) {
    #
    # Retrieve current DNS record
    #
    $cloudflareData = json_decode(get('https://api.cloudflare.com/client/v4/zones/'.$zone.'/dns_records?type=A&name='.$domain.'&page=1&per_page=20&order=type&direction=desc&match=all', [
        'X-Auth-Email: '.$username,
        'X-Auth-Key: '.$api_key,
        'Content-Type: application/json'
    ]));
    $cloudflareRecord = $cloudflareData->result[0]->id;
    $currentIp = $cloudflareData->result[0]->content;

    #
    # Retrieve current IP address
    #
    $externalData = get('http://checkip.dyndns.com/');
    preg_match("/((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?1)){3}/", $externalData, $matches);
    $outsideIp = $matches[0];


    #
    # Update IP when needed
    #
    if ($currentIp !== $outsideIp) {
        echo "IP change detected: {$currentIp} => {$outsideIp}".PHP_EOL;

        #
        # Push updated IP to cloudflare
        #
        $cloudflareResponse = json_decode(get('https://api.cloudflare.com/client/v4/zones/'.$zone.'/dns_records/'.$cloudflareRecord, [
            'X-Auth-Email: '.$username,
            'X-Auth-Key: '.$api_key,
            'Content-Type: application/json'
        ], (object) [
            'id' => $cloudflareRecord,
            'name' => $domain,
            'content' => $outsideIp,
        ]));

        if ($cloudflareResponse->success) {
            echo "Success".PHP_EOL;

            return;
        } else {
            echo "Failed".PHP_EOL;
            var_dump($cloudflareResponse);

            return;
        }
    } else {
        echo "IP correct".PHP_EOL;
    }

}

#
# Init
#
echo "Starting cloudflare-dyndns".PHP_EOL;
echo "Options: ".PHP_EOL;
var_dump($cli->getFlagValues());
echo PHP_EOL;

#
# Check loop
#
echo "Starting run".PHP_EOL;
while(True) {
    # Check
    check($cli['zone'], $cli['domain'], $cli['username'], $cli['api_key']);

    # Sleep
    $time = $cli['timeout'] * 60;
    echo "Sleeping ".$time." seconds.".PHP_EOL;
    sleep($time);
}
