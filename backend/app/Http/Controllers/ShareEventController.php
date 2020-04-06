<?php

namespace App\Http\Controllers;

use App\ShareEvent;
use Illuminate\Http\Request;

class ShareEventController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->success(ShareEvent::where('user_id',auth('api')->user()->id)->with('entry')->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ShareEvent  $shareEvent
     * @return \Illuminate\Http\Response
     */
    public function show(ShareEvent $shareEvent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ShareEvent  $shareEvent
     * @return \Illuminate\Http\Response
     */
    public function edit(ShareEvent $shareEvent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ShareEvent  $shareEvent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShareEvent $shareEvent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ShareEvent  $shareEvent
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShareEvent $shareEvent)
    {
        //
    }
}
