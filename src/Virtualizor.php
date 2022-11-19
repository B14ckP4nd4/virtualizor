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

    /*
     * Get the information methods from server
     */
    // list of ip pools
    public function IPPools(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->getItems('ippool');
        return ($list && isset($list->ippools)) ? new Collection($list->ippools) : new Collection();
    }

    // list of IPs
    public function IPs(int $page = 1, int $itemPerPage = 50)
    {
        $list = $this->getItems('ips');
        return ($list && isset($list->ips)) ? new Collection($list->ips) : new Collection();
    }

    // list of shortages
    public function Storages(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->getItems('storage');
        return ($list && isset($list->storage)) ? new Collection($list->storage) : new Collection();
    }

    // list of plans
    public function Plans(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->getItems('plans');
        return ($list && isset($list->plans)) ? new Collection($list->plans) : new Collection();
    }

    // list of OsTemplates
    public function OSTemplates(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->getItems('ostemplates');
        return ($list && isset($list->ostemplates)) ? new Collection($list->ostemplates) : new Collection();
    }

    // list of OSes ( all OSes in Virtualizor )
    public function VirtualizorOSes(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->getItems('ostemplates');
        return ($list && isset($list->oses)) ? new Collection($list->oses) : new Collection();
    }

    // list of virtual servers
    public function VPSes(array $search_params = [])
    {
        $list = $this->getItems('vs');
        return ($list && isset($list->vs)) ? new Collection($list->vs) : new Collection();
    }

    // get list of items from server like VPSs, Storages and etc.
    private function getItems(string $item, int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        if (empty($params)) {
            $list = $this->sendRequest($item, ['page' => $page, 'reslen' => $itemPerPage]);

        } else {
            $list = $this->sendRequest($item, array_merge(['page' => $page, 'reslen' => $itemPerPage], $params));
        }

        return $list;
    }

    /*
     * Action methods for manage VPSes
     */

    // Start the VPS
    public function Start(int $vpsid): bool
    {
        /*
         * Try to send Start request
         * as usual this action take time and curl will be killed and throw an Exception
         */
        try {
            return $this->initServerActions([
                    'action' => 'start',
                    'vpsid' => $vpsid
                ])->done ?? false;
        } catch (\Exception $e) {
            return true;
        }
    }

    // Stop the VPS
    public function Stop(int $vpsid): bool
    {
        return $this->initServerActions([
                'action' => 'stop',
                'vpsid' => $vpsid
            ])->done ?? false;
    }

    // reStart the VPS
    public function Restart(int $vpsid): bool
    {
        /*
         * Try to send Restart request
         * as usual this action take time and curl will be killed and throw an Exception
         */
        try {
            return $this->initServerActions([
                    'action' => 'restart',
                    'vpsid' => $vpsid
                ])->done ?? false;
        } catch (\Exception $e) {
            return true;
        }
    }

    // PowerOff the VPS
    public function PowerOff(int $vpsid): bool
    {
        return $this->initServerActions([
                'action' => 'poweroff',
                'vpsid' => $vpsid
            ])->done ?? false;
    }

    // Suspend the VPS
    public function Suspend(int $vpsid): bool
    {
        return $this->initServerActions([
                'suspend' => $vpsid,
            ])->done ?? false;
    }

    // UnSuspend the VPS
    public function UnSuspend(int $vpsid): bool
    {
        /*
         * Try to send UnSuspend request
         * as usual this action take time and curl will be killed and throw an Exception
         */
        try {
            return $this->initServerActions([
                    'unsuspend' => $vpsid,
                ])->done ?? false;
        } catch (\Exception $e) {
            return true;
        }

    }

    // Suspend Network of the VPS
    public function SuspendNetwork(int $vpsid): bool
    {
        return $this->initServerActions([
                'suspend_net' => $vpsid,
            ])->done ?? false;
    }

    // UnSuspend Network of the VPS
    public function UnSuspendNetwork(int $vpsid): bool
    {
        return $this->initServerActions([
                'unsuspend_net' => $vpsid,
            ])->done ?? false;
    }

    // Reset Bandwidth Usage
    public function ResetBandwidth(int $vpsid): bool
    {
        return $this->initServerActions([
                'bwreset' => $vpsid,
            ])->done ?? false;
    }

    // Reset Bandwidth Usage
    public function getVPSesStatus(array $VPSesID)
    {
        return $this->initServerActions([
            'vs_status' => $VPSesID,
        ]);
    }

    // Lock the VPS
    public function Lock(int $vpsid, string $reason = 'Locked by admin')
    {
        return $this->initServerActions([
                'action' => 'lock',
                'vpsid' => $vpsid,
            ],
                [
                    'reason' => $reason
                ])->vsop->id == $vpsid ?? false;
    }

    // UnLock the VPS
    public function UnLock(int $vpsid)
    {
        return $this->initServerActions([
                'action' => 'unlock',
                'vpsid' => $vpsid,
            ])->vsop->id == $vpsid ?? false;
    }

    /**
     * create new vps on dedicated server
     * @param array $params
     * @return false|mixed
     * @throws \Exception
     */
    public function create_vps(array $params)
    {
        $params['addvps'] = 1;
        return $this->sendRequest('addvs', $params, [], [], 3000);
    }

    /**
     * Delete VPS by VPS vid
     * @param int $vid
     * @return false|mixed
     * @throws \Exception
     */
    public function delete_vps(int $vid)
    {
        $params = [
            'delete' => $vid,
        ];
        return $this->sendRequest('vs', $params, [], [], 3000);
    }

    // initialize Actions for VPSes like start , stop , restart and etc.
    private function initServerActions(array $actions, array $post = [])
    {
        return $this->sendRequest('vs', $post, $actions, [], 3000);
    }

    /*
     * Send Request and Get response methods
     */
    // Generate and init a curl request to server
    protected function sendRequest(string $action, array $params = [], array $GET = [], array $COOKIES = [], int $timeout = 9000)
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
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
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
