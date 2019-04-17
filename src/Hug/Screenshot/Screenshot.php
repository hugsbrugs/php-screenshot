<?php

namespace Hug\Screenshot;

use Hug\Http\Http as Http;

use Hug\Screenshot\Provider\Manet as Manet;
use Hug\Screenshot\Provider\Apercite as Apercite;
use Hug\Screenshot\Provider\PagePeeker as PagePeeker;

/**
 * This class manages different screenshot providers in order to always get a thumb if a provider fails
 */
class Screenshot
{
    public $config;

    public $url;

    public $cache = true;

    /**
     *
     */
    function __construct($cache = true)
    {
        $this->cache = $cache;

        # Check Provider file constant exists
        if(defined('HUG_SCREENSHOT_PROVIDERS'))
        {
            $this->config = json_decode(file_get_contents(HUG_SCREENSHOT_PROVIDERS));
            if(count((array)$this->config)===0)
            {
                throw new \Exception("No Screenshot Proviers", 1);
            }
        }
        else
        {
            # get default config with free screenshots
            throw new \Exception("Missing HUG_SCREENSHOT_PROVIDERS constant", 1);
            
        }

        if(defined('HUG_SCREENSHOT_SAVE_PATH'))
        {
            if(!is_dir(HUG_SCREENSHOT_SAVE_PATH) || !is_writable(HUG_SCREENSHOT_SAVE_PATH))
            {
                throw new \Exception("Hug::Screenshot ".HUG_SCREENSHOT_SAVE_PATH." does not exist or is not writable", 1);
            }
        }
        else
        {
            # Define System temporary directory
            define('HUG_SCREENSHOT_SAVE_PATH', sys_get_temp_dir());
        }

        if(!defined('HUG_SCREENSHOT_CACHE'))
        {
            # Define System temporary directory
            define('HUG_SCREENSHOT_CACHE', 'P1D');
        }
        
    }

    /**
     *
     */
    public function shot($url, $widths = ['1024', '768', '480'])
    {
        $response = ['status' => 'error', 'message' => '', 'images' => [], 'details' => []];

        $this->url = $url;
        
        # Loop Over Services Until Response
        foreach ($widths as $key => $width)
        {
            foreach ($this->config as $key => $provider)
            {
                $url_image_name = Screenshot::url_2_image($url, $width);
                $path = HUG_SCREENSHOT_SAVE_PATH.$url_image_name;

                # Check for Cache Image
                $file_last_mod = \DateTime::createFromFormat('Y-m-d H:i:s', FileSystem::file_last_mod($path));
                $a_week_ago = new \DateTime('now');
                $a_week_ago = $a_week_ago->sub(new \DateInterval(HUG_SCREENSHOT_CACHE));
                
                if($this->cache && file_exists($path) && $file_last_mod > $a_week_ago)
                {
                        $response['status'] = 'success';
                        $response['images'][$width] = $url_image_name;
                        break;
                }
                else
                {
                    $provider_name = 'Hug\Screenshot\Provider\\'.$provider->provider;
                    $screenshot_provider = new $provider_name($provider, $width);

                    $res = $screenshot_provider->shot($url, $url_image_name);
                    
                    if($res['status']==='success')
                    {
                        $response['status'] = 'success';
                        $response['images'][$width] = $res['data'];
                        break;
                    }
                    else
                    {
                        $response['details'][$provider->provider] = $res['message'];
                    }
                }
            }
        }

        return $response;
    }


    /**
     *
     */
    public static function url_2_image($url, $width, $height = 'auto', $extension = 'jpg')
    {
        $url = Http::extract_domain_from_url($url);
        $url .= '-' . $width;
        $url .= 'x' . $height;
        // use file_last_mod to know when screenshot has been taken 
        // $date = new \DateTime();
        // $url .= '-' . $date->getTimestamp();
        $url .= '.'.$extension;

        return $url;
    }

}
