<?php

namespace App\Libraries;

use Dompdf\Dompdf;

class Pdf extends DOMPDF
{
    function __construct()
    {
        parent::__construct();

        self::set_base_path(\App::get('configs')->get('settings.general.site_url').'/');
    }
}
