<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simulations', function (Blueprint $table) {
            $table->unsignedInteger('purchase_price')->nullable()->after('property_id')->comment('物件価格（万円）');
            $table->integer('down_payment')->nullable()->after('purchase_price')->comment('頭金概算（万円）');
        });

        $rows = DB::table('simulations')
            ->leftJoin('properties', 'simulations.property_id', '=', 'properties.id')
            ->select('simulations.id', 'simulations.borrowing_amount', 'properties.price as property_price')
            ->get();

        foreach ($rows as $row) {
            $purchasePrice = $row->property_price !== null
                ? (int) $row->property_price
                : (int) $row->borrowing_amount;
            $downPayment = $purchasePrice - (int) $row->borrowing_amount;

            DB::table('simulations')->where('id', $row->id)->update([
                'purchase_price' => $purchasePrice,
                'down_payment' => $downPayment,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('simulations', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'down_payment']);
        });
    }
};
