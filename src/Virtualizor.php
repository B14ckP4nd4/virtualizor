<?php


namespace BlackPanda\Virtualizor;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Virtualizor
{
    public $ip;
    public $port;
    public $api_key;
    public $api_sec;

    public function __construct(string $ip, int $port, string $api_key, string $api_sec)
    {
        $this->ip = $ip;
        $this->port = $port ?? 4085;
        $this->api_key = $api_key;
        $this->api_sec = $api_sec;
    }

    // list of ip pools
    public function ippools(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        return $this->getItems('ippool');
    }

    // list of IPs
    public function IPs(int $page = 1, int $itemPerPage = 50){
        return $this->getItems('ips');
    }

    // list of shortages
    public function Storages(int $page = 1, int $itemPerPage = 50 , array $params = []){
        return $this->getItems('storage');
    }

    // list of plans
    public function Plans(int $page = 1, int $itemPerPage = 50 , array $params = [])
    {
        return $this->getItems('plans');
    }

    // list of OsTemplates
    public function OSTemplates(int $page = 1, int $itemPerPage = 50 , array $params = [])
    {
        return $this->getItems('ostemplates');
    }

    // list of OSes ( all OSes in Virtualizor )
    public function virtualizorOSes(int $page = 1, int $itemPerPage = 50 , array $params = [])
    {
        if (empty($params)) {
            $list = $this->sendRequest('ostemplates', ['page' => $page, 'reslen' => $itemPerPage]);

        } else {
            $list = $this->sendRequest('ostemplates', array_merge(['page' => $page, 'reslen' => $itemPerPage], $params));
        }

        return ($list && isset($list->oses)) ? new Collection($list->oses) : new Collection();
    }

    // list of virtual servers
    public function listvps(array $search_params = [])
    {
        return $this->getItems('vs');
    }

    // get list of items from server like VPSs, Storages and etc.
    private function getItems(string $item , int $page = 1, int $itemPerPage = 50 , array $params = []){
        if (empty($params)) {
            $list = $this->sendRequest($item, ['page' => $page, 'reslen' => $itemPerPage]);

        } else {
            $list = $this->sendRequest($item, array_merge(['page' => $page, 'reslen' => $itemPerPage], $params));
        }

        return ($list && isset($list->{$item})) ? new Collection($list->{$item}) : new Collection();
    }

    // Generate and init a curl request to server
    protected function sendRequest(string $action, array $params = [], array $GET = [], array $COOKIES = [])
    {

        $ch = curl_init();
        $GET = array_merge(
            [
                'act' => $action,
                'api' => 'json',
                'apikey' => rawurlencode($this->generateAPIKey()),
            ],
            $GET
        );

        curl_setopt($ch, CURLOPT_URL, 'https://' . $this->ip . ':' . $this->port . '/index.php?' . http_build_query($GET));
        // Time OUT
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 9000);
        // UserAgent
        curl_setopt($ch, CURLOPT_USERAGENT, 'BlackPanda Virtualizor');
        // Cookies
        if (!empty($cookies)) {
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($cookies, '', '; '));
        }
        // Params
        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Get Response
        $response = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code != 200) {
            Log::alert("virtualizor connection refused: {$http_code}");
            throw new \Exception("There is a problem to get results. curl Status code {$http_code}");
        }
        curl_close($ch);


        return $this->decryptResults($response);
    }

    // Generate API key
    private function generateAPIKey()
    {
        $key = Str::random(8);
        return $key . md5($this->api_sec . $key);
    }

    // Json decode the result if it was json
    private function decryptResults(string $json)
    {
        $json = json_decode($json);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $json;
        }
        return false;
    }

}