<?php
namespace SiteMaster\Core\Registry;

use SiteMaster\Core\Config;

class GroupHelper
{
    const DEFAULT_GROUP_NAME = 'default';

    public function getPrimaryGroup($base_url)
    {
        $groups_config = Config::get('GROUPS');
        
        foreach ($groups_config as $group_name=>$details) {
            $patterns = $details['MATCHING'];
            
            foreach($patterns as $pattern) {
                if (preg_match($pattern, $base_url, $matches)) {
                    return $group_name;
                }
            }
        }

        //Nothing was found, so return the default group
        return self::DEFAULT_GROUP_NAME;
    }

    /**
     * @param $group_name
     * @return array
     */
    public function getConfigForGroup($group_name)
    {
        $groups_config = Config::get('GROUPS');
        
        if (isset($groups_config[$group_name])) {
            return $groups_config[$group_name];
        }
        
        return $groups_config[self::DEFAULT_GROUP_NAME];
    }

    /**
     * Sanitize the groups config as set by Config::SET('GROUPS')
     * 
     * @param array $groups_config
     * @return array
     */
    public function sanitizeGroupsConfig(array $groups_config)
    {
        $default_config = [
            'MATCHING' => [],
            'SITE_PASS_FAIL' => Config::get('SITE_PASS_FAIL'),
            'SCAN_PAGE_LIMIT' => Config::get('SCAN_PAGE_LIMIT'),
            'METRICS' => [],
        ];
        
        $metrics_config = Config::get('PLUGINS');
        
        foreach ($groups_config as $key=>$value) {
            $groups_config[$key] = array_replace_recursive($default_config, $groups_config[$key]);
            
            //sanitize the metric configuration (merge the default config for the metric)
            foreach ($groups_config[$key]['METRICS'] as $metric_key=>$metric_value) {
                if (!isset($metrics_config[$metric_key])) {
                    continue;
                }
                
                $default_metric_config = $metrics_config[$metric_key];
                
                //Update it
                $groups_config[$key]['METRICS'][$metric_key] = array_replace_recursive($default_metric_config, $metric_value);
            }
        }
        
        if (!isset($groups_config[self::DEFAULT_GROUP_NAME])) {
            //Make sure that the default group is set.
            $groups_config[self::DEFAULT_GROUP_NAME] = $default_config;
        }

        return $groups_config;
    }

    /**
     *
     * @param $domain
     * @return string
     */
    public static function generateDomainRegex($domain)
    {
        return '/^(https?:\/\/)([a-z0-9-]*\.)*'.preg_quote($domain).'(:[0-9]+)?(\/.*)?$/i';
    }

    /**
     * Determine if a group exists
     *
     * A group exists if it has an entry in the GROUPS configuration
     *
     * @param $group_name
     * @return bool
     */
    public function groupExists($group_name)
    {
        return (bool)Config::getForGroup($group_name, 'MATCHING');
    }
}
