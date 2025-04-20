<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmartCollectionItem extends Model
{
    protected $table = 'tbl_smart_collection_items';

    protected $fillable = [
        'smart_collection_id',
        'item_id',
        'item_type',
    ];

    protected $appends = ['item_data'];

    // /**
    //  * Polymorphic relation to the item (Ebook, Audio, etc.)
    //  */
    // public function item(): MorphTo
    // {
    //     return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    // }

    /**
     * Belongs to SmartCollection.
     */
    public function smartCollection(): BelongsTo
    {
        return $this->belongsTo(SmartCollection::class, 'smart_collection_id');
    }

    /**
     * Custom attribute to get item data based on type.
     */
    public function getItemDataAttribute()
    {
        try {
            switch ($this->item_type) {
                case 'e_book':
                    return \App\Models\EBook::with(['multipleEbooks'])->find($this->item_id) ?: null;

                case 'audio_book':
                    return \App\Models\Audio::find($this->item_id) ?: null;

                default:
                    return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
