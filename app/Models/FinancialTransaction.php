<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An invoice/payment (type=payment) or a refund (type=refund) in whole rupiah.
 *
 * - `amount` is read as an integer number of rupiah (column decimal(15,2)).
 * - `payment_method` is nullable until the payment is made.
 * - "Overdue" is never stored: it is a pending payment whose due_date is past
 *   (see isOverdue(), display_status and scopeOverdue()).
 * - A refund row has type=refund, status=paid and refund_of_id = the payment;
 *   the refunded payment gets status=refunded.
 * Revenue rule: gross = payments with status paid; refunds = refund rows with
 * status paid; net = gross - refunds (use scopePayments/scopeRefunds/scopeSettled).
 *
 * @property int $amount
 * @property string $type payment|refund
 * @property string $status pending|paid|failed|refunded|cancelled
 */
class FinancialTransaction extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_PAYMENT = 'payment';

    public const TYPE_REFUND = 'refund';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_CANCELLED = 'cancelled';

    /** Pseudo status computed for pending payments past their due date. */
    public const STATUS_OVERDUE = 'overdue';

    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_id',
        'payer_id',
        'refund_of_id',
        'invoice_number',
        'amount',
        'discount_amount',
        'tax_amount',
        'type',
        'status',
        'payment_method',
        'transaction_date',
        'due_date',
        'paid_date',
        'description',
        'transaction_reference',
        'notes',
        'proof_path',
        'recorded_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'discount_amount' => 'integer',
        'tax_amount' => 'integer',
        'transaction_date' => 'datetime',
        'due_date' => 'date',
        'paid_date' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Defaults that the database cannot express portably.
     */
    protected static function booted(): void
    {
        static::creating(function (FinancialTransaction $transaction) {
            $transaction->transaction_date ??= now();
            $transaction->type ??= self::TYPE_PAYMENT;
            $transaction->status ??= self::STATUS_PENDING;
        });
    }

    /**
     * Indonesian labels for the statuses (including the computed "overdue").
     *
     * Returns:
     *     array<string, string>: status => label.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu pembayaran',
            self::STATUS_OVERDUE => 'Jatuh tempo',
            self::STATUS_PAID => 'Lunas',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_REFUNDED => 'Dikembalikan',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    /**
     * Indonesian labels for the payment methods.
     *
     * Returns:
     *     array<string, string>: method => label.
     */
    public static function paymentMethods(): array
    {
        return [
            'transfer' => 'Transfer bank',
            'virtual_account' => 'Virtual account',
            'qris' => 'QRIS',
            'ewallet' => 'E-wallet',
            'card' => 'Kartu debit/kredit',
            'cash' => 'Tunai',
        ];
    }

    /** Get the student for this transaction. */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** The parent/guardian who pays (null for adult students). */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    /** Get the course for this transaction. */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** The enrollment this invoice is for. */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** For a refund row: the payment it refunds. */
    public function refundOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'refund_of_id');
    }

    /** For a payment: its refund rows (scopeRefunds() filters refund rows instead). */
    public function refundTransactions(): HasMany
    {
        return $this->hasMany(self::class, 'refund_of_id');
    }

    /** Staff member who recorded the transaction. */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Finance staff who verified the payment. */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** Scope to get only paid transactions. */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('financial_transactions.status', self::STATUS_PAID);
    }

    /** Scope to get only pending transactions. */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('financial_transactions.status', self::STATUS_PENDING);
    }

    /** Pending payments whose due date is before today. */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('financial_transactions.status', self::STATUS_PENDING)
            ->where('financial_transactions.type', self::TYPE_PAYMENT)
            ->whereNotNull('financial_transactions.due_date')
            ->whereDate('financial_transactions.due_date', '<', today()->toDateString());
    }

    /** Payment rows (not refunds). */
    public function scopePayments(Builder $query): Builder
    {
        return $query->where('financial_transactions.type', self::TYPE_PAYMENT);
    }

    /** Refund rows. */
    public function scopeRefunds(Builder $query): Builder
    {
        return $query->where('financial_transactions.type', self::TYPE_REFUND);
    }

    /** Rows whose money actually moved (status paid). */
    public function scopeSettled(Builder $query): Builder
    {
        return $query->where('financial_transactions.status', self::STATUS_PAID);
    }

    /** Transactions of one student or of all children of a parent. */
    public function scopeForStudents(Builder $query, array $studentIds): Builder
    {
        return $query->whereIn('financial_transactions.student_id', $studentIds);
    }

    /** Whether this is a pending payment past its due date. */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->type !== self::TYPE_REFUND
            && $this->due_date !== null
            && $this->due_date->lt(today());
    }

    /** Status for display: "overdue" when isOverdue(), otherwise the stored status. */
    public function getDisplayStatusAttribute(): string
    {
        return $this->isOverdue() ? self::STATUS_OVERDUE : (string) $this->status;
    }

    /** Indonesian label of display_status. */
    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->display_status] ?? (string) $this->status;
    }

    /** Indonesian label of the payment method (null when not set). */
    public function getPaymentMethodLabelAttribute(): ?string
    {
        return $this->payment_method ? (self::paymentMethods()[$this->payment_method] ?? $this->payment_method) : null;
    }

    /** Get the formatted amount, e.g. "Rp 1.500.000". */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->amount, 0, ',', '.');
    }
}
