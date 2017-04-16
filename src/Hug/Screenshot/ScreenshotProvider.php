<?php

namespace Hug\Screenshot;

/**
 *
 */
interface ScreenshotProvider
{

    /**
     *
     */
    public function shot($url, $image, $params = []);
}
