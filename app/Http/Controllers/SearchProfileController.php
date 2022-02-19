<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSearchProfileRequest;
use App\Http\Requests\UpdateSearchProfileRequest;
use App\Models\SearchProfile;

class SearchProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSearchProfileRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSearchProfileRequest $request)
    {
        return SearchProfile::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SearchProfile  $searchProfile
     * @return \Illuminate\Http\Response
     */
    public function show(SearchProfile $searchProfile)
    {
        return $searchProfile;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSearchProfileRequest  $request
     * @param  \App\Models\SearchProfile  $searchProfile
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSearchProfileRequest $request, SearchProfile $searchProfile)
    {
        return $searchProfile->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SearchProfile  $searchProfile
     * @return \Illuminate\Http\Response
     */
    public function destroy(SearchProfile $searchProfile)
    {
        $searchProfile->delete();
        return response("Search Profile deleted successfully", '204');
    }
}
