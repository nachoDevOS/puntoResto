<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'table_number',
        'payment_method',
        'cash_amount',
        'qr_amount',
        'total',
        'ticket_date',
        'ticket_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cash_amount' => 'decimal:2',
            'qr_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'ticket_date' => 'date',
        ];
    }

    public static function nextTicketNumberForDate(Carbon $date): int
    {
        $lastTicketNumber = self::query()
            ->whereDate('ticket_date', $date->toDateString())
            ->lockForUpdate()
            ->max('ticket_number');

        return ((int) $lastTicketNumber) + 1;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }
}
