<?php

namespace App\Http\Controllers;

use App\invoices;
use Illuminate\Http\Request;
use App\sections;
use App\invoices_details;
use Illuminate\Support\Facades\DB;
use App\invoices_attachments;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InvoicePaid;
use Illuminate\Support\Facades\Notification;
use App\User;
use App\Exports\invoicesExport;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Storage;
class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invoices=invoices::all();
        return view('invoices.invoices',compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $sections=sections::all();
        return view('invoices.add_invoice',compact('sections'));
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
         invoices::create([
           'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
             'user' => (Auth::user()->name),
           ]);
        
         $invoice_id = invoices::latest()->first()->id;
         invoices_details::create([
            'id_Invoice' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => (Auth::user()->name),
        ]);
        
       if ($request->hasFile('pic')) {

            $invoice_id = invoices::latest()->first()->id;
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoices_number = $request->invoice_number;

            $attachments = new invoices_attachments();
            $attachments->file_name = $file_name;
            $attachments->invoice_number= $invoices_number;
            $attachments->Created_by = Auth::user()->name;
            $attachments->invoice_id = $invoice_id;
            $attachments->save();

            // move pic
            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move(public_path('Attachments/' . $invoices_number), $imageName);
        }
        $user=User::first();
        Notification::send($user,new InvoicePaid($invoice_id));
        
        
        $user = User::get();
        $invoices = invoices::latest()->first();
        Notification::send($user, new \App\Notifications\TaskComplete($invoices));

     

        
        session()->flash('Add','تم إضافة القسم بنجاح');
        return back();


        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
        $invoices= invoices::where('id',$id)->first();
        return view('invoices.status_update',compact('invoices'));
            }catch(\Exception $ex){
            return $ex;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoices=invoices::where('id',$id)->first();
        $sections=sections::all();
        return view('invoices.edit_invoice',compact('sections','invoices'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        //return $request;
        $invoices=invoices::findOrFail($request->invoice_id);
        $invoices->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
              'note' => $request->note,
                ]);
        
        session()->flash('edit','تم تعديل الفاتورة بنجاح');
        return back();
        
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //return $request;
        $id=$request->invoice_id;
        $invoices=invoices::where('id',$id)->first();
        $Deatails=invoices_attachments::where('invoice_id',$id)->first();
        $id_page=$request->id_page;
        if(!$id_page == 3){
            
        
        if(!empty($Deatails->invoice_number)){
            Storage::disk('public_uploads')->deleteDirectory($Deatails->invoice_number);
        }
        $invoices->forceDelete();
        session()->flash('delete_invoice');
        return redirect('/invoices');
        }
        else{
            $invoices->delete();
            session()->flash('archive_invoice');
            return redirect('Archive');
        }
    }
    
    public function getproducts($id){
        $products=DB::table('products')->where('section_id',$id)->pluck("Product_name","id");
        return json_encode($products);
    }
     public function status_update($id, Request $request)
    {
        $invoices = invoices::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            invoices_details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }

        else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            invoices_details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');

    }
    public function invoice_paid(){
        $invoices=invoices::where('Value_Status',1)->get();
        return view('invoices.invoices_paid',compact('invoices'));
    }
    public function UnPaid(){
         $invoices=invoices::where('Value_Status',2)->get();
        return view('invoices.invoices_unpaid',compact('invoices'));
   
    }
    public function Partial(){
         $invoices=invoices::where('Value_Status',3)->get();
        return view('invoices.invoices_partial',compact('invoices'));
   
        
    }
    public function Print_invoice($id)
    {
        $invoices = invoices::where('id', $id)->first();
        return view('invoices.Print_invoice',compact('invoices'));
    }
     public function export() 
    {
         //echo "fff";
        return Excel::download(new invoicesExport, 'invoices.xlsx');
    }
    public function markAsRead(Request $request){
        $usernotification = auth()->user()->unreadNotifications;
        
        if($usernotification){
            $usernotification -> markAsRead();
            return back();

        }
        
    }
    
    
}


