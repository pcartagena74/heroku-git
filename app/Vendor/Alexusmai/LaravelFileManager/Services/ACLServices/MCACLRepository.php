<?php

namespace App\Vendor\Alexusmai\LaravelFileManager\Services\ACLServices;

use Alexusmai\LaravelFileManager\Services\ACLService\ACLRepository;
use App\Models\Person;
use Entrust;

/**
 * Class ConfigACLRepository
 *
 * Get rules from file-manager config file - aclRules
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
        $currentOrg = $currentPerson->defaultOrg;
        $path = getAllDirectoryPathFM($currentOrg);
        generateDirectoriesForOrg($currentOrg);
        if (\Auth::id() === 1) {
            return config('file-manager.aclRules')[$this->getUserID()] ?? [];
        }
        // 0 deny 1 read only 2 read and write
        if (Entrust::hasRole('Developer')) {
            return [
                ['disk' => getDefaultDiskFM(), 'path' => '/', 'access' => 1],
                ['disk' => getDefaultDiskFM(), 'path' => $path['orgPath'], 'access' => 1],
                ['disk' => getDefaultDiskFM(), 'path' => $path['orgPathFM'], 'access' => 1],
                ['disk' => getDefaultDiskFM(), 'path' => $path['event'], 'access' => 1],
                ['disk' => getDefaultDiskFM(), 'path' => $path['event'].'/*', 'access' => 2],
                ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'], 'access' => 1],
                ['disk' => getDefaultDiskFM(), 'path' => $path['campaign_thumb'], 'access' => 0],
                ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'].'/*', 'access' => 2],
            ];
        }
        $rule_array = [
            ['disk' => getDefaultDiskFM(), 'path' => '/', 'access' => 1],
            ['disk' => getDefaultDiskFM(), 'path' => $path['orgPath'], 'access' => 1],
            ['disk' => getDefaultDiskFM(), 'path' => $path['orgPathFM'], 'access' => 1],
            ['disk' => getDefaultDiskFM(), 'path' => $path['event'], 'access' => 1],
            ['disk' => getDefaultDiskFM(), 'path' => $path['event'].'/*', 'access' => 2],
        ];
        if (Entrust::hasRole('Marketing') || Entrust::hasRole('Admin')) {
            $rule_array[] = ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'], 'access' => 1];
            $rule_array[] = ['disk' => getDefaultDiskFM(), 'path' => $path['campaign_thumb'], 'access' => 0];
            $rule_array[] = ['disk' => getDefaultDiskFM(), 'path' => $path['campaign'].'/*', 'access' => 2];
        }

        return $rule_array;
    }
}
