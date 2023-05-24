<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    /*
        $table->datetime('check_in')->nullable();
            $table->datetime('check_out')->nullable();
            $table->datetime('sys_in');
            $table->datetime('sys_out');
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->double('delay')->default(0);
            $table->boolean('sus')->default(false);
            $table->string('ip')->nullable();
            $table->unsignedBigInteger('attachment_id');
     */
    protected $fillable = [
        'check_in',
        'check_out',
        'sys_in',
        'sys_out',
        'date',
        'user_id',
        'delay',
        'sus',
        'ip',
        'attachment_id'
    ];
    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function attachment_in()
    {
        return $this->belongsTo(Attachment::class, 'attachment_id', 'id');
    }
    public function attachment_out()
    {
        return $this->belongsTo(Attachment::class, 'attachment_out_id', 'id');
    }

    protected $casts = [
        'delay'=>'int'
    ];

}
