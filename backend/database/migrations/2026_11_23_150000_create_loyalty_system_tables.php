<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Loyalty Cards
        Schema::create('loyalty_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained('customers')->cascadeOnDelete();
            $table->unsignedInteger('current_stamps')->default(0);
            $table->unsignedInteger('lifetime_stamps')->default(0);
            $table->unsignedInteger('card_cycle')->default(1);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'current_stamps']);
        });

        // 2. Loyalty Stamp Logs (Transaction audit history)
        Schema::create('loyalty_stamp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_card_id')->constrained('loyalty_cards')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('action'); // booking_completed, manual_add, manual_deduct, reward_redeemed, cycle_reset
            $table->integer('change'); // e.g. +1, -1
            $table->string('reason')->nullable();
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['loyalty_card_id', 'created_at']);
        });

        // 3. Loyalty Reward Tiers Configuration
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('stamps_required'); // 3, 6, 8
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('reward_type')->default('perk'); // perk, discount, free_service
            $table->unsignedInteger('discount_percentage')->nullable();
            $table->string('icon')->default('sparkles'); // wine, sparkles, crown
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        // 4. Customer Earned Rewards & Vouchers
        Schema::create('customer_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('loyalty_reward_id')->constrained('loyalty_rewards')->cascadeOnDelete();
            $table->string('voucher_code')->unique();
            $table->string('status')->default('unclaimed'); // unclaimed, redeemed, expired
            $table->timestamp('unlocked_at');
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('redeemed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('redeemed_at_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('voucher_code');
        });

        // Default Loyalty Rewards Seed
        $now = now();
        DB::table('loyalty_rewards')->insert([
            [
                'stamps_required'     => 3,
                'title'               => 'Complimentary Wine & Hand Scrub',
                'description'         => 'Enjoy a chilled glass of wine or mocktail paired with an organic exfoliating hand scrub ritual.',
                'reward_type'         => 'perk',
                'discount_percentage' => null,
                'icon'                => 'wine',
                'is_active'           => true,
                'sort_order'          => 1,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'stamps_required'     => 6,
                'title'               => '20% Off Any Treatment or Set',
                'description'         => 'Receive 20% discount off your entire service total on your next appointment.',
                'reward_type'         => 'discount',
                'discount_percentage' => 20,
                'icon'                => 'sparkles',
                'is_active'           => true,
                'sort_order'          => 2,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'stamps_required'     => 8,
                'title'               => 'Free Deluxe Gel Set or Pedicure',
                'description'         => 'Congratulations on completing your Sanctuary Stamp Card! Enjoy a complimentary deluxe manicure or pedicure on the house.',
                'reward_type'         => 'free_service',
                'discount_percentage' => null,
                'icon'                => 'crown',
                'is_active'           => true,
                'sort_order'          => 3,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_rewards');
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_stamp_logs');
        Schema::dropIfExists('loyalty_cards');
    }
};
