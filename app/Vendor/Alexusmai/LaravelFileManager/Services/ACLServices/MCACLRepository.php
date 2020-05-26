<?php

namespace App\Vendor\Alexusmai\LaravelFileManager\Services\ACLServices;

use Alexusmai\LaravelFileManager\Services\ACLService\ACLRepository;
use App\Person;
use Entrust;

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
            return config('file-manager.aclRules')[$this->getUserID()] ?? [];
        }
        if (Entrust::hasRole('Developer')) {
            return [
                ['disk' => getDefaultDiskFM(), 'path' => '/', 'access' => 1], // only read
                ['disk' => getDefaultDiskFM(), 'path' => $path['orgPath'], 'access' => 1], // only read
                ['disk' => getDefaultDiskFM(), 'path' => $path['orgPath'] . '/filemanager', 'access' => 1], // only read
                ['disk' => getDefaultDiskFM(), 'path' => $path['event'], 'access' => 1], // only read
                ['disk' => getDefaultDiskFM(), 'path' => $path['event'] . '/*', 'access' => 2], // read and write
                ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'], 'access' => 1], // read and write
                ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'] . '/*', 'access' => 2], // read and write
            ];
        }
        $rule_array = [
            ['disk' => getDefaultDiskFM(), 'path' => '/', 'access' => 1], // only read
            ['disk' => getDefaultDiskFM(), 'path' => $path['orgPath'] . '/filemanager', 'access' => 1], // only read
            ['disk' => getDefaultDiskFM(), 'path' => $path['event'], 'access' => 1], // read
            ['disk' => getDefaultDiskFM(), 'path' => $path['event'] . '/*', 'access' => 2], // read and write
        ];
        if (Entrust::hasRole('Marketing') || Entrust::hasRole('Admin')) {
            $rule_array[] = ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'], 'access' => 1]; // read
            $rule_array[] = ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'] . '/*', 'access' => 2]; // read and write
        }
        return $rule_array;
    }
}
