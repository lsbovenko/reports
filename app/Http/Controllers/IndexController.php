<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{
    App,
    Auth,
    Log
};

use App\Http\Requests\IdeaRequest;
use App\Models\Categories\Status;
use Illuminate\Http\Request;

/**
 * Class IndexController
 * @package App\Http\Controllers
 */
class IndexController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {

        return view('index.index');
    }

    public function switchLanguage(string $lang, Request $request)
    {
        if (array_key_exists($lang, config('languages'))) {
            $request->session()->put(['locale' => $lang]);
            $request->session()->save();
        }

        return redirect()->back();
    }
}