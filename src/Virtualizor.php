<?php


namespace BlackPanda\Virtualizor;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class Virtualizor
 *
 * This class provides an interface to interact with the Virtualizor API, allowing for
 * the management of virtual servers, IP pools, storage, plans, and OS templates.
 * It includes methods for controlling VPS actions such as start, stop, restart,
 * suspend, and bandwidth reset, as well as methods for creating, rebuilding, and
 * deleting VPS instances.
 *
 * The class uses curl to send requests and handle responses from the Virtualizor API.
 *
 * Usage:
 *  - Initialize the class with the server IP, port, API key, and API secret.
 *  - Use available methods to manage VPS, retrieve information, or perform actions.
 *
 * Example:
 * ```php
 * $virtualizor = new Virtualizor('127.0.0.1', 4085, 'your_api_key', 'your_api_secret');
 * $vpsList = $virtualizor->VPSes();
 * ```
 *
 * @package BlackPanda\Virtualizor
 * @link https://github.com/B14ckP4nd4/virtualizor GitHub Repository
 * @link https://packagist.org/packages/blackpanda/virtualizor Packagist Page
 * @author
 * @link https://github.com/B14ckP4nd4 Author GitHub Profile
 */
class Virtualizor
{
    public $ip;
    public $port;
    public $api_key;
    public $api_sec;


    /**
     * Virtualizor constructor.
     *
     * Initializes the Virtualizor API connection settings with the required server details.
     *
     * @param string $ip The IP address of the Virtualizor server.
     * @param int $port The port to connect to Virtualizor, default is 4085.
     * @param string $api_key The API key provided by Virtualizor for authentication.
     * @param string $api_sec The API secret provided by Virtualizor for authentication.
     */
    public function __construct(string $ip, int $port, string $api_key, string $api_sec)
    {
        $this->ip = $ip;
        $this->port = $port ?? 4085;
        $this->api_key = $api_key;
        $this->api_sec = $api_sec;
    }

    /**
     * Retrieve a collection of IP pools from the Virtualizor server.
     *
     * This method fetches a list of IP pools that are available on the server.
     * The result is returned as a Laravel Collection, allowing for convenient
     * manipulation and iteration over the IP pool data.
     *
     * @param int $page The page number for pagination (default is 1).
     * @param int $itemPerPage The number of items to retrieve per page (default is 50).
     * @param array $params Optional additional parameters for filtering the IP pools.
     *
     * @return \Illuminate\Support\Collection A collection containing the IP pools,
     * or an empty collection if no IP pools are found or an error occurs.
     *
     * Usage:
     * $virtualizor = new Virtualizor('your_ip', your_port, 'your_api_key', 'your_api_secret');
     * $ipPools = $virtualizor->IPPools();
     * foreach ($ipPools as $ipPool) {
     *     // Process each IP pool
     * }
     */
    public function IPPools(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->fetchResources('ippool');
        return ($list && isset($list->ippools)) ? new Collection($list->ippools) : new Collection();
    }

    /**
     * Retrieve a collection of IP addresses from the Virtualizor server.
     *
     * This method fetches a list of IP addresses available on the server, allowing
     * administrators to view and manage the IPs assigned or available for use.
     * The results are returned as a Laravel Collection, facilitating further
     * operations such as filtering or transforming the data.
     *
     * @param int $page The page number for pagination (default is 1).
     * @param int $itemPerPage The number of items to retrieve per page (default is 50).
     * @param array $params Optional parameters for filtering or searching IPs, such as IP type or status.
     *
     * @return \Illuminate\Support\Collection A collection of IP addresses,
     * or an empty collection if no IPs are found or an error occurs.
     *
     * Usage:
     * $virtualizor = new Virtualizor('your_ip', your_port, 'your_api_key', 'your_api_secret');
     * $ips = $virtualizor->IPs();
     * foreach ($ips as $ip) {
     *     // Process each IP address
     * }
     */
    public function IPs(int $page = 1, int $itemPerPage = 50)
    {
        $list = $this->fetchResources('ips');
        return ($list && isset($list->ips)) ? new Collection($list->ips) : new Collection();
    }

    /**
     * Retrieve a collection of storage details from the Virtualizor server.
     *
     * This method fetches a list of storage options configured on the server,
     * which can include various types like LVM, File, etc. The returned Laravel
     * Collection allows for convenient manipulation and analysis of the storage data.
     *
     * @param int $page The page number for pagination (default is 1).
     * @param int $itemPerPage The number of items to retrieve per page (default is 50).
     * @param array $params Optional parameters to refine the search, such as storage type or status.
     *
     * @return \Illuminate\Support\Collection A collection of storage data,
     * or an empty collection if no storages are found or an error occurs.
     *
     * Usage:
     * $virtualizor = new Virtualizor('your_ip', your_port, 'your_api_key', 'your_api_secret');
     * $storages = $virtualizor->Storages();
     * foreach ($storages as $storage) {
     *     // Process each storage entry
     * }
     */
    public function Storages(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->fetchResources('storage');
        return ($list && isset($list->storage)) ? new Collection($list->storage) : new Collection();
    }

    /**
     * Retrieves a list of available plans from the Virtualizor server.
     *
     * Plans define the resource configurations (e.g., CPU, RAM, Disk) available for
     * virtual servers. This method allows you to retrieve all available plans,
     * which can be useful when creating or managing VPS instances.
     *
     * @param int $page        The page number for pagination, default is 1.
     * @param int $itemPerPage The number of items per page, default is 50.
     * @param array $params    Additional query parameters for filtering or refining the request.
     *                         Example: ['name' => 'basic-plan'] to filter by plan name.
     *
     * @return Collection      A collection of plans, each containing details like resources,
     *                         pricing, and availability. Returns an empty collection if no plans are found.
     *
     * Usage:
     * $plans = $virtualizor->Plans();
     */
    public function Plans(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->fetchResources('plans');
        return ($list && isset($list->plans)) ? new Collection($list->plans) : new Collection();
    }

    /**
     * Retrieves a list of available OS templates from the Virtualizor server.
     *
     * OS templates are pre-configured operating system images that can be used to
     * quickly deploy virtual servers with specific OS configurations. This method
     * fetches all available templates, which can be useful when creating or rebuilding VPS instances.
     *
     * @param int $page        The page number for pagination, default is 1.
     * @param int $itemPerPage The number of items per page, default is 50.
     * @param array $params    Additional query parameters for filtering or refining the request.
     *                         Example: ['type' => 'linux'] to filter by OS type.
     *
     * @return Collection      A collection of OS templates, each containing details like OS version,
     *                         architecture, and template size. Returns an empty collection if no templates are found.
     *
     * Usage:
     * $osTemplates = $virtualizor->OSTemplates();
     */
    public function OSTemplates(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->fetchResources('ostemplates');
        return ($list && isset($list->ostemplates)) ? new Collection($list->ostemplates) : new Collection();
    }

    /**
     * Retrieves the list of available Operating Systems (OS) templates from Virtualizor.
     *
     * This method interacts with the Virtualizor API to fetch all OS templates that
     * can be used for creating or managing virtual servers (VPS). The retrieved data
     * can include various OS distributions like Linux, Windows, and other custom OS templates
     * configured in the Virtualizor control panel.
     *
     * @return array An associative array of OS templates including details like OS name, version,
     *               and any other relevant attributes provided by the Virtualizor API.
     *
     * @throws Exception If the API call fails or returns an error, an exception is thrown
     *                   indicating the failure reason.
     *
     * Example Usage:
     * ```php
     * $virtualizor = new Virtualizor();
     * $osTemplates = $virtualizor->VirtualizorOSes();
     * foreach ($osTemplates as $template) {
     *     echo $template['name'];
     * }
     * ```
     */
    public function VirtualizorOSes(int $page = 1, int $itemPerPage = 50, array $params = [])
    {
        $list = $this->fetchResources('ostemplates');
        return ($list && isset($list->oses)) ? new Collection($list->oses) : new Collection();
    }

    /**
     * Retrieves a list of Virtual Private Servers (VPS) from the Virtualizor panel.
     *
     * This method fetches all VPS instances currently managed by the Virtualizor, including
     * their statuses, resource allocations, and other relevant details. It provides essential
     * information for managing VPS instances such as starting, stopping, or modifying configurations.
     *
     * @return array An array of VPS data, including details such as VPS ID, hostname, IP addresses,
     *               resource usage, and current state (running, stopped, etc.).
     *
     * @throws Exception If the request to the Virtualizor API fails or encounters errors, an
     *                   exception is thrown with an error message explaining the issue.
     *
     * Example Usage:
     * ```php
     * $virtualizor = new Virtualizor();
     * $vpsList = $virtualizor->VPSes();
     * foreach ($vpsList as $vps) {
     *     echo "VPS ID: " . $vps['id'] . " - Status: " . $vps['status'];
     * }
     * ```
     */
    public function VPSes(array $search_params = [])
    {
        $list = $this->fetchResources('vs');
        return ($list && isset($list->vs)) ? new Collection($list->vs) : new Collection();
    }

    /**
     * Fetches a list of resources from the Virtualizor server, such as VPSs, OS templates, storages, and other resources.
     *
     * This private method sends a request to the Virtualizor API to retrieve a paginated list of specified resources.
     * It supports additional query parameters to filter or modify the request as needed.
     *
     * @param string $item The type of resource to fetch, e.g., 'vps', 'os', 'storage'.
     * @param int $page The page number for pagination (default is 1).
     * @param int $itemPerPage The number of items per page (default is 50).
     * @param array $params Optional. Additional parameters to include in the API request.
     *
     * @return array The list of resources returned from the Virtualizor API.
     *
     * @throws Exception If the API request fails or encounters an error.
     *
     * Example Usage:
     * ```php
     * $vpsList = $this->fetchResources('vps');
     * $osTemplates = $this->fetchResources('os', 1, 20, ['status' => 'active']);
     * ```
     */
    private function fetchResources(string $item, int $page = 1, int $itemPerPage = 50, array $params = [])
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

    /**
     * Starts a specified VPS on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to start the VPS identified by the given VPS ID.
     *
     * @param int $vpsId The ID of the VPS to be started.
     * @return array The response from the Virtualizor API after attempting to start the VPS.
     *
     * @throws Exception If the API request fails or the VPS cannot be started.
     *
     * Example Usage:
     * ```php
     * $response = $this->start(1234);
     * ```
     */
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

    /**
     * Stops a specified VPS on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to stop the VPS identified by the given VPS ID.
     *
     * @param int $vpsId The ID of the VPS to be stopped.
     * @return array The response from the Virtualizor API after attempting to stop the VPS.
     *
     * @throws Exception If the API request fails or the VPS cannot be stopped.
     *
     * Example Usage:
     * ```php
     * $response = $this->stop(1234);
     * ```
     */
    public function Stop(int $vpsid): bool
    {
        return $this->initServerActions([
                'action' => 'stop',
                'vpsid' => $vpsid
            ])->done ?? false;
    }

    /**
     * Restarts a specified VPS on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to restart the VPS identified by the given VPS ID.
     *
     * @param int $vpsId The ID of the VPS to be restarted.
     * @return array The response from the Virtualizor API after attempting to restart the VPS.
     *
     * @throws Exception If the API request fails or the VPS cannot be restarted.
     *
     * Example Usage:
     * ```php
     * $response = $this->restart(1234);
     * ```
     */
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

    /**
     * Powers off a specified VPS on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to power off the VPS identified by the given VPS ID.
     *
     * @param int $vpsId The ID of the VPS to be powered off.
     * @return array The response from the Virtualizor API after attempting to power off the VPS.
     *
     * @throws Exception If the API request fails or the VPS cannot be powered off.
     *
     * Example Usage:
     * ```php
     * $response = $this->powerOff(1234);
     * ```
     */
    public function PowerOff(int $vpsid): bool
    {
        return $this->initServerActions([
                'action' => 'poweroff',
                'vpsid' => $vpsid
            ])->done ?? false;
    }

    /**
     * Suspends a specified VPS on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to suspend the VPS identified by the given VPS ID.
     *
     * @param int $vpsId The ID of the VPS to be suspended.
     * @return array The response from the Virtualizor API after attempting to suspend the VPS.
     *
     * @throws Exception If the API request fails or the VPS cannot be suspended.
     *
     * Example Usage:
     * ```php
     * $response = $this->suspend(1234);
     * ```
     */
    public function Suspend(int $vpsid): bool
    {
        return $this->initServerActions([
                'suspend' => $vpsid,
            ])->done ?? false;
    }

    /**
     * Unsuspends a specified VPS on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to unsuspend the VPS identified by the given VPS ID.
     *
     * @param int $vpsId The ID of the VPS to be unsuspended.
     * @return array The response from the Virtualizor API after attempting to unsuspend the VPS.
     *
     * @throws Exception If the API request fails or the VPS cannot be unsuspended.
     *
     * Example Usage:
     * ```php
     * $response = $this->unsuspend(1234);
     * ```
     */
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

    /**
     * Suspends the network for a specified VPS on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to suspend the network of the VPS identified by the given VPS ID.
     *
     * @param int $vpsId The ID of the VPS whose network will be suspended.
     * @return array The response from the Virtualizor API after attempting to suspend the network.
     *
     * @throws Exception If the API request fails or the network cannot be suspended.
     *
     * Example Usage:
     * ```php
     * $response = $this->suspendNetwork(1234);
     * ```
     */
    public function SuspendNetwork(int $vpsid): bool
    {
        return $this->initServerActions([
                'suspend_net' => $vpsid,
            ])->done ?? false;
    }

    /**
     * Unsuspends the network for a specified VPS on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to unsuspend the network of the VPS identified by the given VPS ID.
     *
     * @param int $vpsId The ID of the VPS whose network will be unsuspended.
     * @return array The response from the Virtualizor API after attempting to unsuspend the network.
     *
     * @throws Exception If the API request fails or the network cannot be unsuspended.
     *
     * Example Usage:
     * ```php
     * $response = $this->unsuspendNetwork(1234);
     * ```
     */
    public function UnSuspendNetwork(int $vpsid): bool
    {
        return $this->initServerActions([
                'unsuspend_net' => $vpsid,
            ])->done ?? false;
    }

    /**
     * Resets the bandwidth usage for a specified VPS on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to reset the bandwidth usage of the VPS identified by the given VPS ID.
     *
     * @param int $vpsId The ID of the VPS for which the bandwidth will be reset.
     * @return array The response from the Virtualizor API after attempting to reset the bandwidth.
     *
     * @throws Exception If the API request fails or the bandwidth cannot be reset.
     *
     * Example Usage:
     * ```php
     * $response = $this->resetBandwidth(1234);
     * ```
     */
    public function ResetBandwidth(int $vpsid): bool
    {
        return $this->initServerActions([
                'bwreset' => $vpsid,
            ])->done ?? false;
    }

    /**
     * Retrieves the status of a specified VPS or multiple VPS instances on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to get the status information of the VPS identified by the given VPS ID or an array of IDs.
     *
     * @param int|array $vpsId The ID or array of IDs of the VPS instances whose status will be retrieved.
     * @return array The response from the Virtualizor API containing the status details of the specified VPS instances.
     *
     * @throws Exception If the API request fails or the status information cannot be retrieved.
     *
     * Example Usage:
     * ```php
     * $status = $this->getVPSesStatus(1234); // For a single VPS
     * $statusList = $this->getVPSesStatus([1234, 5678]); // For multiple VPSes
     * ```
     */
    public function getVPSesStatus(array $VPSesID)
    {
        return $this->initServerActions([
            'vs_status' => $VPSesID,
        ]);
    }

    /**
     * Locks a specified VPS on the Virtualizor server to prevent any operations.
     *
     * This method sends a request to the Virtualizor API to lock the VPS identified by the given VPS ID, restricting any operations until unlocked.
     *
     * @param int $vpsId The ID of the VPS that will be locked.
     * @return bool Returns true if the VPS was successfully locked, false otherwise.
     *
     * @throws Exception If the API request fails or the VPS cannot be locked.
     *
     * Example Usage:
     * ```php
     * $isLocked = $this->lock(1234);
     * if ($isLocked) {
     *     echo "VPS locked successfully.";
     * } else {
     *     echo "Failed to lock VPS.";
     * }
     * ```
     */
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

    /**
     * Unlocks a specified VPS on the Virtualizor server, allowing operations to be performed.
     *
     * This method sends a request to the Virtualizor API to unlock the VPS identified by the given VPS ID, permitting any subsequent operations.
     *
     * @param int $vpsId The ID of the VPS that will be unlocked.
     * @return bool Returns true if the VPS was successfully unlocked, false otherwise.
     *
     * @throws Exception If the API request fails or the VPS cannot be unlocked.
     *
     * Example Usage:
     * ```php
     * $isUnlocked = $this->unlock(1234);
     * if ($isUnlocked) {
     *     echo "VPS unlocked successfully.";
     * } else {
     *     echo "Failed to unlock VPS.";
     * }
     * ```
     */
    public function UnLock(int $vpsid)
    {
        return $this->initServerActions([
                'action' => 'unlock',
                'vpsid' => $vpsid,
            ])->vsop->id == $vpsid ?? false;
    }

    /**
     * Creates a new VPS (Virtual Private Server) on the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to create a new VPS with the specified parameters,
     * including server configuration, operating system, resources, and other settings.
     *
     * @param array $params An associative array of parameters required for creating the VPS, including:
     *                      - 'hostname' (string): The hostname of the VPS.
     *                      - 'rootpass' (string): The root password for the VPS.
     *                      - 'osid' (int): The operating system ID for the VPS.
     *                      - 'serverid' (int): The server ID where the VPS will be created.
     *                      - 'plid' (int): The plan ID that defines the resources (RAM, CPU, Disk, etc.) for the VPS.
     *                      - 'ips' (int): The number of IP addresses to assign to the VPS.
     *                      - 'space' (int): Disk space in GB allocated to the VPS.
     *                      - 'ram' (int): The amount of RAM in MB allocated to the VPS.
     *                      - 'bandwidth' (int): The bandwidth limit in GB for the VPS.
     *                      - 'cores' (int): The number of CPU cores allocated to the VPS.
     *                      - 'network_speed' (int): The network speed in Mbps for the VPS.
     *                      - Additional parameters may include specific virtualization options as supported by Virtualizor.
     * @return array Returns an array containing the details of the newly created VPS, including status, VPS ID, and any relevant information.
     *
     * @throws Exception If the VPS creation fails or the required parameters are invalid.
     *
     * Example Usage:
     * ```php
     * $vpsParams = [
     *     'hostname' => 'example-vps',
     *     'rootpass' => 'securepassword123',
     *     'osid' => 123,
     *     'serverid' => 1,
     *     'plid' => 2,
     *     'ips' => 1,
     *     'space' => 50,
     *     'ram' => 2048,
     *     'bandwidth' => 1000,
     *     'cores' => 2,
     *     'network_speed' => 100
     * ];
     *
     * $vpsDetails = $this->create_vps($vpsParams);
     * if ($vpsDetails['status'] === 'success') {
     *     echo "VPS created successfully with ID: " . $vpsDetails['vpsid'];
     * } else {
     *     echo "Failed to create VPS: " . $vpsDetails['error'];
     * }
     * ```
     */
    public function create_vps(array $params)
    {
        $params['addvps'] = 1;
        return $this->sendRequest('addvs', $params, [], [], 3000);
    }

    /**
     * Rebuilds an existing VPS with a new operating system or template.
     *
     * This method sends a request to the Virtualizor API to rebuild a VPS with a specified OS ID or template,
     * effectively reinstalling the operating system. The existing data on the VPS will be erased during this process.
     *
     * @param int $vpsId The ID of the VPS that needs to be rebuilt.
     * @param int $osId The ID of the new operating system to install on the VPS.
     * @param string $rootPassword The new root password for the VPS after the rebuild.
     * @param array $params Optional parameters for additional customization during the rebuild, such as:
     *                      - 'hostname' (string): New hostname for the VPS.
     *                      - 'ips' (array): List of IP addresses to assign or reassign during rebuild.
     *                      - 'disable_email' (bool): Whether to disable sending an email notification after rebuild.
     *                      - Any additional parameters supported by Virtualizor for customization.
     * @return array Returns an array with the status of the rebuild operation, including success or failure messages.
     *
     * @throws Exception If the rebuild operation fails or the VPS ID and OS ID are invalid.
     *
     * Example Usage:
     * ```php
     * $rebuildParams = [
     *     'vpsId' => 1234,
     *     'osId' => 567,
     *     'rootPassword' => 'newsecurepassword123',
     * ];
     *
     * $rebuildStatus = $this->rebuild($rebuildParams['vpsId'], $rebuildParams['osId'], $rebuildParams['rootPassword']);
     * if ($rebuildStatus['status'] === 'success') {
     *     echo "VPS rebuilt successfully.";
     * } else {
     *     echo "Failed to rebuild VPS: " . $rebuildStatus['error'];
     * }
     * ```
     */
    public function rebuild(array $params)
    {
        $params['reos'] = 1;
        return $this->sendRequest('rebuild', $params, [], [], 3000);
    }

    /**
     * Deletes an existing VPS from the Virtualizor server.
     *
     * This method sends a request to the Virtualizor API to permanently delete a VPS identified by its ID.
     * All data associated with the VPS will be erased, and the VPS will be removed from the server.
     *
     * @param int $vpsId The ID of the VPS to be deleted.
     * @return bool Returns true if the VPS was deleted successfully, or false if the deletion failed.
     *
     * @throws Exception If the deletion process fails or the VPS ID is invalid.
     *
     * Example Usage:
     * ```php
     * $vpsId = 1234;
     *
     * if ($this->delete_vps($vpsId)) {
     *     echo "VPS deleted successfully.";
     * } else {
     *     echo "Failed to delete VPS.";
     * }
     * ```
     */
    public function delete_vps(int $vid)
    {
        $params = [
            'delete' => $vid,
        ];
        return $this->sendRequest('vs', $params, [], [], 3000);
    }

    /**
     * Initializes and performs specified server actions on a Virtualizor VPS instance.
     *
     * This private method is responsible for handling multiple server actions such as starting, stopping,
     * or other maintenance tasks on a VPS by sending a request to the Virtualizor API. It provides a unified
     * approach to execute various VPS management actions with customizable parameters.
     *
     * @param array $actions An array of action keys that define the operations to be performed on the VPS.
     *                       Actions may include:
     *                       - 'start': Start the VPS.
     *                       - 'stop': Stop the VPS.
     *                       - 'restart': Restart the VPS.
     *                       - 'suspend': Suspend the VPS, disabling its functionality temporarily.
     *                       - 'unsuspend': Re-enable the VPS after suspension.
     *                       - 'suspend_network': Disable network access for the VPS.
     *                       - 'unsuspend_network': Re-enable network access for the VPS.
     *                       - 'reset_bandwidth': Reset the bandwidth statistics of the VPS.
     * @param array $post Optional. An array of POST parameters that can be sent along with the actions
     *                    to customize the request. These parameters may include:
     *                    - 'vpsid' (int): The ID of the VPS on which the actions are performed.
     *                    - Additional fields that provide context or customization for the server action.
     * @return array The response from the Virtualizor API, containing the result of the action request.
     *               The response typically includes status, error messages, or success confirmations
     *               based on the actions executed.
     *
     * @throws Exception If the request fails or encounters an unexpected error while performing actions.
     *
     * Example Usage:
     * ```php
     * // Initialize multiple actions on a VPS
     * $actions = ['start', 'reset_bandwidth'];
     * $post = ['vpsid' => 1234];
     * $response = $this->initServerActions($actions, $post);
     *
     * // Check if the actions were successful
     * if ($response['status'] === 'success') {
     *     echo "Actions performed successfully.";
     * } else {
     *     echo "Failed to perform actions: " . $response['error'];
     * }
     * ```
     */
    private function initServerActions(array $actions, array $post = [])
    {
        // Sends a request to perform the specified actions on the VPS with a timeout of 3000 milliseconds.
        return $this->sendRequest('vs', $post, $actions, [], 3000);
    }

    /**
     * Sends an HTTP request to the Virtualizor server to perform a specified action using the cURL library.
     *
     * This protected method is the core function used to interact with the Virtualizor API, allowing various
     * actions to be performed on virtual servers, storages, or other resources. It constructs a URL with
     * query parameters, sets up cURL options, and manages cookies, timeouts, and request payloads.
     *
     * @param string $action The action to be performed on the Virtualizor server. This determines the API
     *                       endpoint to be accessed, such as 'vs' for virtual server actions.
     * @param array $params Optional. An array of POST parameters sent along with the request. Typically used
     *                      to provide data for actions that modify server state, such as starting or stopping a VPS.
     *                      Example: ['vpsid' => 123, 'do' => 'start']
     * @param array $GET Optional. An array of query parameters added to the URL. Default values for action type,
     *                   API format, and API key are merged with this array to construct the final request URL.
     * @param array $COOKIES Optional. An array of cookies to be sent with the request. Cookies are formatted and
     *                       included in the cURL session if provided.
     * @param int $timeout Optional. The timeout for the cURL request in milliseconds. Default is 9000 ms (9 seconds).
     *                     Adjust this value based on the expected response time of the Virtualizor server.
     * @return array Returns an array containing the parsed response from the Virtualizor API. The response includes
     *               details about the status of the action performed, error messages, or other relevant data.
     * @throws \Exception If the server responds with an HTTP status code other than 200, an exception is thrown,
     *                    indicating a connection issue or failed request.
     *
     * Example Usage:
     * ```php
     * // Sending a request to start a VPS
     * $response = $this->sendRequest('vs', ['vpsid' => 123, 'do' => 'start']);
     *
     * // Checking the response
     * if (isset($response['status']) && $response['status'] === 'success') {
     *     echo "VPS started successfully.";
     * } else {
     *     echo "Failed to start VPS: " . $response['error'];
     * }
     * ```
     */
    protected function sendRequest(
        string $action,
        array $params = [],
        array $GET = [],
        array $COOKIES = [],
        int $timeout = 9000
    ) {
        // Initialize cURL session
        $ch = curl_init();

        // Merge default query parameters with additional GET parameters
        $GET = array_merge(
            [
                'act' => $action,
                'api' => 'json',
                'apikey' => rawurlencode($this->generateAPIKey()),
            ],
            $GET
        );

        // Set cURL options, including URL, SSL verification, timeouts, and user agent
        curl_setopt($ch, CURLOPT_URL, 'https://' . $this->ip . ':' . $this->port . '/index.php?' . http_build_query($GET));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'BlackPanda Virtualizor');

        // Handle cookies if provided
        if (!empty($COOKIES)) {
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($COOKIES, '', '; '));
        }

        // Set POST fields if params are not empty
        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Execute the cURL request and capture the response
        $response = curl_exec($ch);

        // Get HTTP response code to handle errors
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for non-200 response codes and log errors
        if ($http_code != 200) {
            Log::alert("Virtualizor connection refused: {$http_code}");
            throw new \Exception("Failed to get results. cURL status code {$http_code}");
        }

        // Close cURL session
        curl_close($ch);

        // Handle and return the parsed response
        return $this->handleResponse($response);
    }

    /**
     * Generates an API key for authenticating requests to the Virtualizor server.
     *
     * This private method creates a unique API key by combining a random string with a hashed value derived
     * from the API secret. The generated key is used in API requests to ensure secure communication between
     * the client and the Virtualizor server.
     *
     * @return string Returns a string representing the generated API key. The key consists of an 8-character
     *                random string concatenated with an MD5 hash of the API secret and the random string.
     *
     * Example Usage:
     * ```php
     * // Generate a new API key
     * $apiKey = $this->generateAPIKey();
     * echo "Generated API Key: " . $apiKey;
     * ```
     */
    private function generateAPIKey()
    {
        // Generate an 8-character random string
        $key = Str::random(8);

        // Return the random string concatenated with an MD5 hash of the API secret and the random string
        return $key . md5($this->api_sec . $key);
    }

    /**
     * Handles the response from the Virtualizor server by attempting to decode the JSON string.
     *
     * This private method takes the raw JSON response from the Virtualizor API and attempts to decode it into
     * a PHP object. If the decoding is successful and no errors are detected, the parsed JSON object is returned.
     * Otherwise, it returns `false`, indicating that the response was not valid JSON.
     *
     * @param string $json The raw JSON string received from the Virtualizor server. This string is expected to
     *                     contain data about the action performed, such as status, error messages, or result details.
     * @return mixed Returns the decoded JSON object if the response is valid JSON; otherwise, returns `false`.
     *
     * Example Usage:
     * ```php
     * // Handle a JSON response from an API request
     * $response = $this->handleResponse('{"status": "success", "message": "VPS started successfully."}');
     *
     * // Check if the response was decoded successfully
     * if ($response !== false) {
     *     echo "Response Status: " . $response->status;
     * } else {
     *     echo "Failed to decode response.";
     * }
     * ```
     */
    private function handleResponse(string $json)
    {
        // Decode the JSON string into a PHP object
        $json = json_decode($json);

        // Check for JSON decoding errors
        if (json_last_error() == JSON_ERROR_NONE) {
            return $json;
        }

        // Return false if the response is not valid JSON
        return false;
    }

}
