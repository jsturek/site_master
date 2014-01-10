<?php
namespace SiteMaster\Registry;

use SiteMaster\InvalidArgumentException;

class Registry
{
    /**
     * An array of sites to be aliased in key=>value pairs.
     * the key is the base url to alias, the value is the base url that you want the system to return
     *
     * Note: it should be discouraged to use this in practice
     * 
     * array('from'=>'to');
     * @var array
     */
    public static $aliases = array();

    /**
     * Get an array of possible base uris for a given uri
     * 
     * @param $uri
     * @return array
     * @throws \SiteMaster\InvalidArgumentException
     */
    public function getPossibleSiteURIs($uri)
    {
        $uris = array();

        $parts = parse_url($uri);

        if (!isset($parts['host'])) {
            throw new InvalidArgumentException('Invalid url ' . $uri);
        }

        if (!isset($parts['path'])) {
            $parts['path'] = '/';
        }

        $parts['path'] = $this->trimFileName($parts['path']);

        $paths = explode('/',$parts['path']);

        $total_dirs = count($paths);

        //Loop over the paths (starting from the last path) to find the closest site.
        for ($i=$total_dirs-1; $i>=0; $i--) {
            $path = implode('/',$paths);
            
            //Add on a trailing slash if we need it.
            if (substr($path, -1) != '/') {
                $path .= '/';
            }

            $uris[] = 'http%://' . $parts['host'] . $path;

            unset($paths[$i]);
        }
        
        //Make sure that we only have unique values
        $uris = array_unique($uris);
        
        //Make sure that they are indexed correctly if array_unique removed any
        return array_values($uris);
    }

    /**
     * Trim a filename from a given path
     * 
     * @param $path
     * @return string - the path without a filename
     */
    public function trimFileName($path)
    {
        $parts = explode('/', $path);
        
        $filename = array_pop($parts);
        
        //if the last character of the path was '/', $filename will be empty.
        if ($filename == '') {
            //No filename to trim, so return early
            return $path;
        }
        
        $location = strrpos($path, $filename);
        
        return substr_replace($path, '', $location);
    }

    /**
     * Get the closest site for a given uri
     * 
     * @param $uri
     * @return bool|Site
     */
    public function getClosestSite($uri)
    {
        foreach ($this->getPossibleSiteURIs($uri) as $possible_uri) {
            if ($site = Site::getByBaseURL($possible_uri)) {
                return $site;
            }
        }
        
        return false;
    }
}
