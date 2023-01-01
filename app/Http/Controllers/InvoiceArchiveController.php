<?php

namespace App\Http\Controllers;

use App\InvoiceArchive;
use Illuminate\Http\Request;
use App\invoices;

class InvoiceArchiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $invoices=invoices::onlyTrashed()->get();
        return view('invoices.Archieve_Invoices',compact('invoices'));
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
     * @param  \App\InvoiceArchive  $invoiceArchive
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceArchive $invoiceArchive)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\InvoiceArchive  $invoiceArchive
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceArchive $invoiceArchive)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\InvoiceArchive  $invoiceArchive
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
           {
        //
        $id = $request->invoice_id;
       $not=invoices::withTrashed()->where('id',$id)->restore();
      session()->flash('restore_invoice');
    return redirect('invoices');
 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\InvoiceArchive  $invoiceArchive
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $invoices=invoices::withTrashed()->where('id',$request->invoice_id)->first();
        $invoices->forceDelete();
        session()->flash('delete_invoice');
        return redirect('invoices');
        
    }
}
