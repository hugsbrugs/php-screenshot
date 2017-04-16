<?php

namespace Hug\Screenshot\Provider;

use Hug\Screenshot\ScreenshotProvider as ScreenshotProvider;

use Hug\Http\Http as Http;

/**
 * https://github.com/vbauer/manet
 */
class Manet implements ScreenshotProvider
{
    # Manet Provider Parameters
    public $config;

    # Screenshot Size
    public $width = '1024';
    # Full Page
    public $height = null;

    /**
     * Manet constructor
     *
     * @param string $config
     * @param string $width
     * @param string $height
     */
    function __construct($config, $width = '1024', $height = null)
    {
        $this->config = $config;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Take Screenshot
     *
     * @param string $url
     * @param string $image
     * @param array $params
     * @return array $response 
     */
    public function shot($url, $image, $params = [])
    {
        $response = ['status' => 'error', 'message' => '', 'data' => null];
        try
        {
            # Get image final URL 
            $redirect_url = Http::get_redirect_url($url);
            if($redirect_url!==FALSE && $redirect_url!=='')
            {
                $url = $redirect_url;
            }

            # Override width height params if missing
            if(!isset($params['width']))
            {
                $params['width'] = $this->width;
            }
            if(!isset($params['height']) && $this->height!==null)
            {
                $params['height'] = $this->height;
            }

            # Build Manet API URL to call
            $call_api = $this->build_api_url($url, $params);

            # Image path to save
            $image_path = HUG_SCREENSHOT_SAVE_PATH.'/'.$image;
            
            # Create tmp file
            $tmp_file = tempnam(sys_get_temp_dir(), 'manet-');
            
            $ch = curl_init($call_api);
            $fp = fopen($tmp_file, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 400);
            curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);
            // error_log('http_status : ' . $http_status);

            if($http_status===200)
            {
                if(is_file($tmp_file) && is_readable($tmp_file))
                {
                    if(copy($tmp_file, $image_path))
                    {
                        $response['data'] = $image;
                        $response['status'] = 'success';

                        # Delete tmp file
                        if(is_file($tmp_file))
                        {
                            unlink($tmp_file);
                        }
                    }
                    else
                    {
                        $response['message'] = 'ERROR_COPYING_IMAGE';
                    }
                }
                else
                {
                    $response['message'] = 'ERROR_RETRIEVING_IMAGE';
                }
            }
            else
            {
                $response['message'] = 'MANET_REMOTE_ERROR';
            }
        }
        catch(Exception $e)
        {
            $response['message'] = 'MANET_UNDEFINED_ERROR';
            error_log('Manet::shot : ' . $e->getMessage());
        }

        return $response;
    }



    /**
     * Build Manet API URL to call for screenshot generation
     *
     * @param string $website
     * @param array $params
     * @return string $url
     */
    public function build_api_url($website, $params = [])
    {
        # Scheme
        $url = 'http://';
        if(isset($this->config->scheme))
        {
            $url = $this->config->scheme . '://';
        }

        # Basic Auth
        if(isset($this->config->basic_auth_user) && isset($this->config->basic_auth_pass))
        {
            $url .= $this->config->basic_auth_user.':'.$this->config->basic_auth_pass.'@';
        }

        # Server
        $url .= $this->config->host;

        # Port
        if(isset($this->config->port))
        {
            $url .= ':'.$this->config->port;            
        }
        
        # Webpage to capture
        $url .= '/?url='.$website;

        # Manet Extra Params
        foreach ($this->config->params as $key => $value)
        {
            # Overrride config params by argument params
            if(isset($params[$key]))
            {
                $url .= '&'.$key.'='.$params[$key];
            }
            else
            {
                $url .= '&'.$key.'='.$value;
            }
        }

        // error_log('Manet::build_api_url : ' . $url);

        return $url;
    }

}
