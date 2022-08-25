<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

        // relationship dengan model User   one-to-many
        public function user(){
            return $this->belongsTo('App\Models\User');
        }
        // relationship antara model Book dengan model Order
        public function books(){
            // mengambil field yang berada di tabel pivot yaitu field quantity dengan method     withPivot('quantity')
            return $this->belongsToMany('App\Models\Book')->withPivot('quantity');;
        }

        // Dynamic property gunanya menjumlahkan quantity yang diambil dari tabel pivot hasilnya menjadi nilai dari dynamic property
        // dynamic property menggunakan format get dan diakhiri Attribute. nama functionnya yaitu getTotalQuantityAttribute
        // sehingga dapat di akses dengan $order->totalQuantity         (tanpa get maupun Attribute)
        public function getTotalQuantityAttribute(){
            $total_quantity = 0;

            foreach($this->books as $book){
            $total_quantity += $book->pivot->quantity;
            }
            return $total_quantity;
        }
    
}
