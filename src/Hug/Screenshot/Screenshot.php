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

    /**
     *
     */
    function __construct()
    {
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
    }

    /**
     *
     */
    public function shot($url, $widths = ['1024', '768', '480'])
    {
        $response = ['status' => 'error', 'message' => '', 'images' => [], 'details' => []];

        $this->url = $url;

        # Check for Cache Image
        
        # Loop Over Services Until Response
        foreach ($widths as $key => $width)
        {
            foreach ($this->config as $key => $provider)
            {
                $url_image_name = Screenshot::url_2_image($url, $width);

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
        $date = new \DateTime();
        $url .= '-' . $date->getTimestamp();
        $url .= '.'.$extension;

        return $url;
    }

}
