<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governorate_delivery_fees', function (Blueprint $table) {
            $table->id();
            $table->string('governorate_name', 100);
            $table->string('governorate_name_ar', 100)->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->integer('min_free_delivery_order')->default(0)->comment('Minimum order amount for free delivery. 0 = always charge.');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique('governorate_name');
            $table->index('is_active');
        });

        // Seed default Egyptian governorates
        $governorates = [
            ['governorate_name' => 'Cairo', 'governorate_name_ar' => 'القاهرة', 'delivery_fee' => 60, 'sort_order' => 1],
            ['governorate_name' => 'Giza', 'governorate_name_ar' => 'الجيزة', 'delivery_fee' => 70, 'sort_order' => 2],
            ['governorate_name' => 'Alexandria', 'governorate_name_ar' => 'الإسكندرية', 'delivery_fee' => 80, 'sort_order' => 3],
            ['governorate_name' => 'Aswan', 'governorate_name_ar' => 'أسوان', 'delivery_fee' => 120, 'sort_order' => 4],
            ['governorate_name' => 'Asyut', 'governorate_name_ar' => 'أسيوط', 'delivery_fee' => 110, 'sort_order' => 5],
            ['governorate_name' => 'Beheira', 'governorate_name_ar' => 'البحيرة', 'delivery_fee' => 95, 'sort_order' => 6],
            ['governorate_name' => 'Beni Suef', 'governorate_name_ar' => 'بني سويف', 'delivery_fee' => 100, 'sort_order' => 7],
            ['governorate_name' => 'Dakahlia', 'governorate_name_ar' => 'الدقهلية', 'delivery_fee' => 90, 'sort_order' => 8],
            ['governorate_name' => 'Damietta', 'governorate_name_ar' => 'دمياط', 'delivery_fee' => 90, 'sort_order' => 9],
            ['governorate_name' => 'Faiyum', 'governorate_name_ar' => 'الفيوم', 'delivery_fee' => 90, 'sort_order' => 10],
            ['governorate_name' => 'Gharbia', 'governorate_name_ar' => 'الغربية', 'delivery_fee' => 85, 'sort_order' => 11],
            ['governorate_name' => 'Ismailia', 'governorate_name_ar' => 'الإسماعيلية', 'delivery_fee' => 95, 'sort_order' => 12],
            ['governorate_name' => 'Kafr el-Sheikh', 'governorate_name_ar' => 'كفر الشيخ', 'delivery_fee' => 90, 'sort_order' => 13],
            ['governorate_name' => 'Luxor', 'governorate_name_ar' => 'الأقصر', 'delivery_fee' => 120, 'sort_order' => 14],
            ['governorate_name' => 'Matruh', 'governorate_name_ar' => 'مطروح', 'delivery_fee' => 130, 'sort_order' => 15],
            ['governorate_name' => 'Minya', 'governorate_name_ar' => 'المنيا', 'delivery_fee' => 105, 'sort_order' => 16],
            ['governorate_name' => 'Monufia', 'governorate_name_ar' => 'المنوفية', 'delivery_fee' => 85, 'sort_order' => 17],
            ['governorate_name' => 'New Valley', 'governorate_name_ar' => 'الوادي الجديد', 'delivery_fee' => 140, 'sort_order' => 18],
            ['governorate_name' => 'North Sinai', 'governorate_name_ar' => 'شمال سيناء', 'delivery_fee' => 150, 'sort_order' => 19],
            ['governorate_name' => 'Port Said', 'governorate_name_ar' => 'بورسعيد', 'delivery_fee' => 95, 'sort_order' => 20],
            ['governorate_name' => 'Qalyubia', 'governorate_name_ar' => 'القليوبية', 'delivery_fee' => 75, 'sort_order' => 21],
            ['governorate_name' => 'Qena', 'governorate_name_ar' => 'قنا', 'delivery_fee' => 115, 'sort_order' => 22],
            ['governorate_name' => 'Red Sea', 'governorate_name_ar' => 'البحر الأحمر', 'delivery_fee' => 140, 'sort_order' => 23],
            ['governorate_name' => 'Suez', 'governorate_name_ar' => 'السويس', 'delivery_fee' => 95, 'sort_order' => 24],
            ['governorate_name' => 'South Sinai', 'governorate_name_ar' => 'جنوب سيناء', 'delivery_fee' => 150, 'sort_order' => 25],
            ['governorate_name' => 'Sohag', 'governorate_name_ar' => 'سوهاج', 'delivery_fee' => 110, 'sort_order' => 26],
        ];

        DB::table('governorate_delivery_fees')->insert(array_map(fn($g) => array_merge($g, [
            'min_free_delivery_order' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]), $governorates));
    }

    public function down(): void
    {
        Schema::dropIfExists('governorate_delivery_fees');
    }
};
