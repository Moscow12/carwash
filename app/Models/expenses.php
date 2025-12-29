<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class expenses extends Model
{
    use HasUuids;

    protected $table = 'expenses';

    protected $fillable = [
        'reference_no',
        'expense_date',
        'carwash_id',
        'category_id',
        'subcategory_id',
        'total_amount',
        'tax_amount',
        'payment_due',
        'payment_status',
        'expense_for',
        'expense_for_id',
        'contact',
        'contact_id',
        'expense_note',
        'is_recurring',
        'recurring_interval',
        'recurring_count',
        'recurring_end_date',
        'added_by',
        'status',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'payment_due' => 'decimal:2',
        'is_recurring' => 'boolean',
        'recurring_end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function carwash()
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    public function category()
    {
        return $this->belongsTo(expense_category::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(expense_category::class, 'subcategory_id');
    }

    public function addedByUser()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function expenseForStaff()
    {
        return $this->belongsTo(staffs::class, 'expense_for_id');
    }

    public function contactSupplier()
    {
        return $this->belongsTo(suplier::class, 'contact_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopeForCarwash($query, $carwashId)
    {
        return $query->where('carwash_id', $carwashId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    // Accessors
    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'success',
            'partial' => 'warning',
            'pending' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'active' ? 'success' : 'secondary';
    }

    public function getFormattedAmountAttribute()
    {
        return 'TZS ' . number_format($this->total_amount, 2);
    }

    public function getFormattedTaxAttribute()
    {
        return 'TZS ' . number_format($this->tax_amount, 2);
    }

    public function getFormattedPaymentDueAttribute()
    {
        return 'TZS ' . number_format($this->payment_due, 2);
    }

    public function getExpenseForDisplayAttribute()
    {
        if ($this->expense_for_id && $this->expenseForStaff) {
            return $this->expenseForStaff->name;
        }
        return $this->expense_for ?? '-';
    }

    public function getContactDisplayAttribute()
    {
        if ($this->contact_id && $this->contactSupplier) {
            return $this->contactSupplier->name;
        }
        return $this->contact ?? '-';
    }

    public function getAddedByDisplayAttribute()
    {
        if ($this->addedByUser) {
            return $this->addedByUser->name;
        }
        return '-';
    }

    public function getRecurringDisplayAttribute()
    {
        if (!$this->is_recurring) return '-';

        $display = ucfirst($this->recurring_interval ?? 'Unknown');
        if ($this->recurring_count) {
            $display .= " ({$this->recurring_count}x)";
        }
        return $display;
    }

    // Generate reference number
    public static function generateReferenceNo($carwashId)
    {
        $year = date('Y');
        $count = self::where('carwash_id', $carwashId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return 'EP' . $year . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
