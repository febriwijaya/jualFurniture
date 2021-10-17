<?php

namespace App\Http\Controllers;

use Exception;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Carts;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CheckOutRequest;

class FrontendController extends Controller
{
    public function index(Request $request) {
      $product = Product::with(['gallery'])->latest()->get();
      return view('pages.frontend.index', [
        'product' => $product
      ]);
    }

    public function details(Request $request, $slug) {
      $product = Product::with(['gallery'])->where('slug', $slug)->firstOrFail();
      $recomendations = Product::with(['gallery'])->inRandomOrder()->limit(4)->get();

      return view('pages.frontend.details', [
        'product' => $product,
        'recomendations' => $recomendations
      ]);
    }

    public function cartAdd(Request $request, $id)
    {
      Carts::create([
        'users_id' => Auth::user()->id,
        'products_id' => $id
      ]);

      return redirect('cart');
    }

    public function CartDelete(Request $request, $id)
    {
      $carts = Carts::findOrFail($id);

      $carts->delete();

      return redirect('cart');
    }

    public function cart(Request $request)
    { 
      $carts = Carts::with(['product.gallery'])->where('users_id', Auth::user()->id)->get();
      
      return view('pages.frontend.cart', [
        'carts' => $carts 
      ]);
    }

    public function checkout(CheckOutRequest $request)
    {
      $data = $request->all();

      // get data
      $carts = Carts::with(['product'])->where('users_id', Auth::user()->id)->get();

      // add transaction data
      $data['users_id'] = Auth::user()->id;
      $data['total_price'] = $carts->sum('product.price');

      // create transactions
      $transaction = Transaction::create($data);

      // create transaction item
      foreach ($carts as $cart) {
        $items[] = TransactionItem::create([
          'transactions_id' => $transaction->id,
          'users_id' => $cart->users_id,
          'products_id' => $cart->products_id
        ]);
      }

      // delete cart after transaction
      Carts::where('users_id', Auth::user()->id)->delete();

      // konfigurasi midtrans
      Config::$serverKey = config('services.midtrans.serverKey');
      Config::$isProduction = config('services.midtrans.isProduction');
      Config::$isSanitized = config('services.midtrans.isSanitized');
      Config::$is3ds = config('services.midtrans.is3ds');

      // setup variable midtrans
      $midtrans = [
        'transaction_details' => [
          'order_id' => 'LUX-' . $transaction->id,
          'gross_amount' => (int) $transaction->total_price
        ], 
        'customer_details' => [
          'first_name' => $transaction->name,
          'email' => $transaction->email,
        ],
        'enabled_payments' => ['gopay', 'bank_transfer'],
        'vtweb' => []
      ];

      // payment process
      try {
        // Get Snap Payment Page URL
        $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
       
        $transaction->payment_url = $paymentUrl;
        $transaction->save();
        
        return redirect($paymentUrl);
      }
      catch (Exception $e) {
        echo $e->getMessage();
      }
    }

    public function success(Request $request)
    {
      return view('pages.frontend.success');
    }
}
