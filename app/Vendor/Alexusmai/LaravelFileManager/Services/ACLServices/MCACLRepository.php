<?php

namespace App\Vendor\Alexusmai\LaravelFileManager\Services\ACLServices;

use Alexusmai\LaravelFileManager\Services\ACLService\ACLRepository;
use App\Person;

/**
 * Class ConfigACLRepository
 *
 * Get rules from file-manager config file - aclRules
 *
 * @package Alexusmai\LaravelFileManager\Services\ACLService
 */
class MCACLRepository implements ACLRepository
{
    /**
     * Get user ID
     *
     * @return mixed
     */
    public function getUserID()
    {
        return \Auth::id();
    }

    /**
     * Get rules from file-manger.php config file
     *
     * @return array
     */
    public function getRules(): array
    {
        // return config('file-manager.aclRules')[$this->getUserID()] ?? [];
        $currentPerson = Person::find(auth()->user()->id);
        $currentOrg    = $currentPerson->defaultOrg;
        $path          = getAllDirectoryPathFM($currentOrg);
        generateDirectoriesForOrg($currentOrg);
        if (\Auth::id() === 1) {
            return [
                ['disk' => 's3_media', 'path' => '*', 'access' => 2],
            ];
        }
        return [
            ['disk' => getDefaultDiskFM(), 'path' => '/', 'access' => 1], // only read
            ['disk' => getDefaultDiskFM(), 'path' => $path['orgPath'], 'access' => 1], // only read
            ['disk' => getDefaultDiskFM(), 'path' => $path['event'], 'access' => 1], // read and write
            ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'], 'access' => 1], // read and write
            ['disk' => getDefaultDiskFM(), 'path' => $path['event'] . '/*', 'access' => 2], // read and write
            ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'] . '/*', 'access' => 2], // read and write
        ];
    }
}
