<?php

namespace App;
use App\sections;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class invoices extends Model
{
    use SoftDeletes;
    //
    protected $guarded=[];
    protected $dates=['deleted_at'];
      public function section()
   {
   return $this->belongsTo('App\sections');
   }

}
