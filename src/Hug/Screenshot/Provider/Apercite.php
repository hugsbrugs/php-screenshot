<?php

namespace Hug\Screenshot\Provider;

use Hug\Screenshot\ScreenshotProvider as ScreenshotProvider;
use Hug\FileSystem\FileSystem as FileSystem;
use Hug\Http\Http as Http;

/**
 * https://www.apercite.fr
 */
class Apercite implements ScreenshotProvider
{
    public $config;

    // The size of the thumbnail
    public $width = '560';
    public $height = '420';
    public $size = '560x420';

    /** Available thumbs sizes */
    private $sizes = [
        // 4:3
        "80x60","100x75","120x90","160x120","180x135","240x180","320x240","560x420","640x480","800x600",
        // 16:10
        "80x50","120x75","160x100","200x125","240x150","320x200","560x350","640x400","800x500"
    ];

    // private $image_being_created = null;
    // private $image_not_found = null;

    /**
     *
     */
    function __construct($config, $width = '560', $height = '420')
    {
        $this->config = $config;
        $this->width = $width;
        $this->height = $height;

        if($this->width>800)
            $this->size = '800x600';
        elseif($this->width>640)
            $this->size = '640x480';
        elseif($this->width>560)
            $this->size = '560x420';
        elseif($this->width>320)
            $this->size = '320x240';
        else
            $this->size = '240x180';

        // $this->size = $width.'x'.$height;

        // $this->image_being_created = __DIR__ . '/apercite/apercite-image-en-cours-de-creation.jpg';

        // $this->image_not_found = __DIR__ . '/apercite/apercite-apercite.jpg';
    }

    /**
     *
     */
    public function shot($url, $image, $params = null)
    {
        $Response = ['status' => 'error', 'message' => '', 'data' => null];

        try
        {
            # Get image final URL 
            $redirect_url = Http::get_redirect_url($url);
            if($redirect_url!==false && $redirect_url!=='')
            {
                $url = $redirect_url;
            }

            # Build Apercite API URL to call
            //$call_api = 'https://www.apercite.fr/api/apercite/'.$this->size.'/yes/'.$url;
            $call_api = 'http://www.apercite.fr/api/apercite/'.$this->size.'/yes/'.urlencode($url);
            // error_log('call_api : ' . $call_api);
            
            #
            $image_path = HUG_SCREENSHOT_SAVE_PATH.'/'.$image;
            
            # Create tmp file
            $tmp_file = tempnam(sys_get_temp_dir(), 'apercite-');


            $go = true;
            $is_thumbs_available = false;
            $loops = 0;

            do {
                
                Http::grab_image($call_api, $tmp_file);

                sleep(30);

                Http::grab_image($call_api, $tmp_file);
                
                if(is_file($tmp_file) && is_readable($tmp_file))
                {
                    // error_log('size : ' . filesize($tmp_file));

                    $is_thumbs_available = true;
                    $go = false;

                    /*if(are_files_equal($tmp_file, $this->image_being_created)===true)
                    {
                        # EN ATTENTE
                        error_log('sleep');
                        sleep(10);
                        if($loops===4)
                        {
                            $Response['message'] = 'TIMEOUT';
                            $go = false;
                        }
                        
                    }
                    else
                    {
                        $res = are_files_equal($tmp_file, $this->image_not_found);
                        error_log("are_files_equal : " . var_dump($res));
                        if($res===true)
                        {
                            # THUMB GENERATION FAILED
                            
                            //$go = false;
                            sleep(10);
                            if($loops===4)
                            {
                                $Response['message'] = 'THUMB_GENERATION_FAILED';
                                $go = false;
                            }
                        }
                        else
                        {
                            $is_thumbs_available = true;
                            $go = false;
                        }
                    }*/
                }
                else
                {
                    $Response['message'] = 'ERROR_CALLING_POLL_API';
                    $go = false;
                }

                $loops++;


            } while($go);


            if($is_thumbs_available)
            {
                if(copy($tmp_file, $image_path))
                {
                    $Response['data'] = $image;
                    $Response['status'] = 'success';

                    if(is_file($tmp_file))
                    {
                        unlink($tmp_file);
                    }
                }
                else
                {
                    $Response['message'] = 'ERROR_COPYING_THUMB';
                }
            }
        }
        catch(Exception $e)
        {
            $Response['message'] = $e->getMessage();
        }

        return $Response;
    }


}
