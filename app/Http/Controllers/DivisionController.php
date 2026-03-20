<?php

namespace App\Http\Controllers;

use App\Model\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $divs = Division::query()->get();

        return view('division.index', ['divs' => $divs]);
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
     * @param  int  $alias
     * @return \Illuminate\View\View
     */
    public function show($alias)
    {
        $div = Division::firstWhere('divalias', $alias);

        if(!$div) {
            abort(404, 'Division not found.');
        }

        return view('division.show', ['div' => $div,
                                       'divlead' => $div->leader,
                                       'members' => $div->members,
                                       'officers' => $div->officers,
                                     ]);
    }

    /**
     * Display the complete member list.
     *
     * @param  string  $alias
     * @return \Illuminate\View\View
     */
    public function members($alias)
    {
        $div = Division::firstWhere('divalias', $alias);

        if(!$div) {
            abort(404, 'Division not found.');
        }

        $sort = request('sort', 'rname');
        $direction = request('direction', 'asc');

        $allowed_sorts = ['rname', 'dname', 'rnk', 'firstevent'];
        if (!in_array($sort, $allowed_sorts)) {
            $sort = 'rname';
        }
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        return view('division.members', [
            'div'       => $div,
            'sort'      => $sort,
            'direction' => $direction,
            'members'   => $div->members()->orderBy($sort, $direction)->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $rname
     * @return \Illuminate\Http\Response
     */
    public function edit($rname)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
