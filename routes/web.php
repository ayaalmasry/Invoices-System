<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});


Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');

Route::resource('invoices','InvoicesController');
Route::resource('sections','SectionsController');
Route::resource('products','ProductsController');
Route::get('/section/{id}','InvoicesController@getproducts');
Route::get('/InvoicesDetails/{id}','InvoicesDetailsController@edit');
Route::get('/View_file/{invoice_number}/{file_name}','InvoicesDetailsController@open_file');
Route::resource('InvoiceAttachments','InvoicesAttachmentsController');
Route::get('download/{invoice_number}/{file_name}', 'InvoicesDetailsController@get_file');

//Route::get('View_file/{invoice_number}/{file_name}', 'InvoicesDetailsController@open_file');

Route::post('delete_file', 'InvoicesDetailsController@destroy')->name('delete_file');
Route::get('/edit_invoice/{id}','InvoicesController@edit');
Route::get('/Status_Show/{id}','InvoicesController@show')->name('Status_Show');
Route::post('/Status_Update/{id}','InvoicesController@status_update')->name('Status_Update');
Route::get('Invoice_Paid','InvoicesController@invoice_paid');
Route::get('Invoice_UnPaid','InvoicesController@UnPaid');
Route::get('Invoice_Partial','InvoicesController@Partial');
Route::resource('Archive', 'InvoiceArchiveController');
Route::get('Print_invoice/{id}','InvoicesController@Print_invoice');
Route::get('export_invoices', 'InvoicesController@export');
Route::group(['middleware' => ['auth']], function() {
Route::resource('roles','RoleController');
Route::resource('users','UserController');

});
Route::get('invoices_report','InvoicesReport@index');
Route::post('Search_invoices','InvoicesReport@search_invoices');
Route::get('customer_report','Customer_Report@index');
Route::post('Search_customers','Customer_Report@search_customers');
Route::get('MarkASRead','InvoicesController@markAsRead')->name('MarkASRead');



Route::get('/{page}', 'AdminController@index');




Auth::routes();



