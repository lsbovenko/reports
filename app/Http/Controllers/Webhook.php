<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 28.08.2017
 * Time: 18:02
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Webhook extends Controller
{
    public function receive(Request $request, \App\Service\Webhook $service)
    {
        $service->receive($request->getContent());
    }
}
