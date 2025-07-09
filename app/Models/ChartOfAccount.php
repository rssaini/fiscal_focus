<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'account_subtype',
        'normal_balance',
        'parent_id',
        'level',
        'is_active',
        'allow_posting',
        'description',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_posting' => 'boolean',
        'level' => 'integer',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id')->orderBy('sort_order');
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class, 'chart_of_account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePostable($query)
    {
        return $query->where('allow_posting', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeMainAccounts($query)
    {
        return $query->where('level', 1)->orderBy('sort_order');
    }

    // Helper methods
    public function getFullAccountNameAttribute()
    {
        $names = collect([$this->account_name]);
        $parent = $this->parent;

        while ($parent) {
            $names->prepend($parent->account_name);
            $parent = $parent->parent;
        }

        return $names->implode(' > ');
    }

    public function getFormattedCodeAttribute()
    {
        return $this->account_code . ' - ' . $this->account_name;
    }

    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    public function getIndentedNameAttribute()
    {
        return str_repeat('— ', $this->level - 1) . $this->account_name;
    }
}
