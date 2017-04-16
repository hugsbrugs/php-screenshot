<?php

namespace Hug\Screenshot\Provider;

use Hug\Screenshot\ScreenshotProvider as ScreenshotProvider;
use Hug\FileSystem\FileSystem as FileSystem;
use Hug\Http\Http as Http;

/**
 * http://pagepeeker.com/website-thumbnails-api/
 */
class PagePeeker implements ScreenshotProvider
{

    public $config;

    // The size of the thumbnail
    public $width = '480';
    public $height = '360';
    public $size = 'x';

    /** Available thumbs sizes */
    private $sizes = [
        't', //  Tiny, 90 x 68 pixels
        's', //  Small, 120 x 90 pixels
        'm', //  Medium, 200 x 150 pixels
        'l', //  Large, 400 x 300 pixels
        'x'  // Extra large, 480 x 360 pixels
    ];


    // private $entrypoint = 'free';
    
    # Your API key. This is optional and recommended to use only for server side calls. Client side usage does not require it, as we automatically show the thumbnails only for the domains added in your account.
    # For the free branded accounts, this parameter is ignored, as we provide the free branded service for anybody, free of charge.
    // protected $code = ''; 

    # This parameter forces the currently generated thumbnail to be regenerated. It is optional and will be ignored unless it contains the value 1
    // private $refresh = '';
    
    # If the thumbnail is not already generated and this parameter is present, the API will not return a placeholder image, but wait until the thumbnail is generated and then return the generated thumb. The value of the parameter is the maximum number of seconds we should wait before returning either the generated image or a placeholder.
    // private $wait = '';


    /**
     * $size = 'x', $refresh = '', $wait = ''
     */
    function __construct($config, $width = '480', $height = '360')
    {
        $this->config = $config;
        $this->width = $width;
        $this->height = $height;
        
        if($this->width>480)
            $this->size = 'x';
        elseif($this->width>400)
            $this->size = 'l';
        elseif($this->width>200)
            $this->size = 'm';
        elseif($this->width>120)
            $this->size = 's';
        else
            $this->size = 't';
    }

    /**
     *
     */
    public function shot($url, $image, $params = [])
    {
        $Response = ['status' => 'error', 'message' => '', 'data' => null];

        $image_path = HUG_SCREENSHOT_SAVE_PATH.'/'.$image;

        $call_api = $this->build_api_url($url);
        $poll_api = $this->build_poll_url($url);
        $download_thumb = $this->build_down_url($url);
                
        $go = true;
        $is_thumbs_available = false;
        do {
            sleep(4);
            
            $poll_answer = file_get_contents($poll_api);
            if($poll_answer!==false)
            {
                // error_log('poll_answer : ' . $poll_answer);

                $poll_answer = json_decode($poll_answer);
                if($poll_answer!==NULL)
                {
                    # Error   1 if there was an error creating the thumbnail, 0 otherwise.
                    # IsReady 1 if the screenshot is available and can be retrieved, 0 if not yet available.
                    if($poll_answer->IsReady==1 || ( isset($poll_answer->Error) && $poll_answer->Error==1) )
                    {
                        if( isset($poll_answer->Error) && $poll_answer->Error==1)
                        {
                            $Response['message'] = 'ERROR_GENERATING_THUMB';
                        }

                        if($poll_answer->IsReady==1)
                        {
                            $is_thumbs_available = true;
                        }
                        $go = false;
                    }
                }
                else
                {
                    $Response['message'] = 'ERROR_DECODING_JSON';
                    $go = false;
                }
            }
            else
            {
                $Response['message'] = 'ERROR_CALLING_POLL_API';
                $go = false;
            }

        } while($go);


        if($is_thumbs_available)
        {            
            if(false!==$thumb = file_get_contents($download_thumb))
            {
                if(FileSystem::force_file_put_contents($image_path, $thumb)!==false)
                {
                    $Response['data'] = $image;
                    $Response['status'] = 'success';
                }
                else
                {
                    $Response['message'] = 'ERROR_SAVING_THUMB';
                }
            }
            else
            {
                $Response['message'] = 'ERROR_DOWNLOADING_THUMB';
            }
        }

        return $Response;
    }


    /**
     *
     */
    public function build_api_url($website, $params = null)
    {
        $url = 'http://';

        $url .= $this->config->entrypoint;
        $url .= '.pagepeeker.com/v2/thumbs.php';
        $url .= '?size='.$this->config->size;
        
        $url .= '&code='.$this->config->code;
        $url .= '&refresh='.$this->config->refresh;
        $url .= '&wait='.$this->config->wait;
        $url .= '&url='.$website;

        return $url;
    }

    /**
     *
     */
    public function build_poll_url($website)
    {
        $url = 'http://';

        $url .= $this->config->entrypoint;
        $url .= '.pagepeeker.com/v2/thumbs_ready.php';
        $url .= '?size='.$this->config->size;
        $url .= '&url='.$website;

        return $url;
    }


    /**
     * 
     */
    public function build_down_url($website)
    {
        $url = 'http://';

        $url .= $this->config->entrypoint;
        $url .= '.pagepeeker.com/v2/thumbs.php';
        $url .= '?size='.$this->config->size;
        $url .= '&url='.$website;

        return $url;
    }

}
