<?php

namespace App;

use App\Scopes\InternshipClassScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternshipClass extends Model
{
    use SoftDeletes;

    public $table = 'internship_classes';

    const MODE_SELECT = [
        'online'  => 'Online',
        'offline' => 'Offline',
    ];

    const RECURRENCE_SELECT = [
        'none'   => 'One-time',
        'daily'  => 'Daily',
        'weekly' => 'Weekly (on selected days)',
    ];

    // ISO weekday numbers (Carbon::dayOfWeekIso)
    const DAYS = [
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
        7 => 'Sun',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'internship_id',
        'created_by',
        'title',
        'description',
        'mode',
        'meeting_link',
        'venue',
        'recurrence',
        'days_of_week',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new InternshipClassScope);
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDaysAttribute(): array
    {
        return array_values(array_filter(array_map('intval', explode(',', (string) $this->days_of_week))));
    }

    public function occursOn(Carbon $date): bool
    {
        if ($date->lt($this->start_date->copy()->startOfDay()) || $date->gt($this->end_date->copy()->endOfDay())) {
            return false;
        }

        switch ($this->recurrence) {
            case 'daily':
                return true;
            case 'weekly':
                return in_array($date->dayOfWeekIso, $this->days, true);
            default:
                return $date->isSameDay($this->start_date);
        }
    }

    /**
     * Class dates on or after $from (default today), at most $limit of them.
     *
     * @return Carbon[]
     */
    public function occurrences(Carbon $from = null, int $limit = 10): array
    {
        $date  = Carbon::parse($from ?: today())->startOfDay();
        $start = $this->start_date->copy()->startOfDay();
        $end   = $this->end_date->copy()->startOfDay();

        if ($date->lt($start)) {
            $date = $start;
        }

        $dates = [];

        while ($date->lte($end) && count($dates) < $limit) {
            if ($this->occursOn($date)) {
                $dates[] = $date->copy();
            }
            $date->addDay();
        }

        return $dates;
    }

    public function totalSessions(): int
    {
        return count($this->occurrences($this->start_date, PHP_INT_MAX));
    }

    public function getScheduleSummaryAttribute(): string
    {
        switch ($this->recurrence) {
            case 'daily':
                return 'Every day';
            case 'weekly':
                return implode(', ', array_map(function ($day) {
                    return self::DAYS[$day] ?? '';
                }, $this->days));
            default:
                return 'One-time';
        }
    }

    public function getTimeRangeAttribute(): string
    {
        return $this->formatTime($this->start_time) . ' - ' . $this->formatTime($this->end_time);
    }

    private function formatTime($time): string
    {
        return Carbon::createFromFormat(strlen($time) > 5 ? 'H:i:s' : 'H:i', $time)->format('h:i A');
    }

    /**
     * Plain-text WhatsApp message with the class details.
     */
    public function whatsappMessage(?string $studentName = null): string
    {
        $when = $this->recurrence === 'none'
            ? $this->start_date->format('D, d M Y')
            : $this->schedule_summary . ' (' . $this->start_date->format('d M Y') . ' to ' . $this->end_date->format('d M Y') . ')';

        $lines = [$studentName ? 'Hi ' . $studentName . ',' : 'Hello,', ''];
        $lines[] = 'Class details for your internship' . ($this->internship ? ' "' . $this->internship->name . '"' : '') . ':';
        $lines[] = '';
        $lines[] = 'Class: ' . $this->title;
        $lines[] = 'When: ' . $when;
        $lines[] = 'Time: ' . $this->time_range;
        $lines[] = 'Mode: ' . (self::MODE_SELECT[$this->mode] ?? $this->mode);

        if ($this->mode === 'online' && $this->meeting_link) {
            $lines[] = 'Join link: ' . $this->meeting_link;
        } elseif ($this->mode === 'offline' && $this->venue) {
            $lines[] = 'Venue: ' . $this->venue;
        }

        if ($this->description) {
            $lines[] = '';
            $lines[] = trim($this->description);
        }

        $lines[] = '';
        $lines[] = 'Regards,';
        $lines[] = trans('panel.site_title');

        return implode("\n", $lines);
    }

    /**
     * Students with a paid registration for this class's internship, with ready wa.me links.
     */
    public function whatsappRecipients(): array
    {
        $registrations = InternshipRegistration::with('user.studentProfile')
            ->where('internship_id', $this->internship_id)
            ->where('status', InternshipRegistration::STATUS_PAID)
            ->get();

        return $registrations->map(function ($registration) {
            $user  = $registration->user;
            $phone = optional($user->studentProfile)->whatsappPhone();

            return [
                'key'   => $registration->id,
                'name'  => $user->name,
                'phone' => $phone,
                'url'   => $phone
                    ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($this->whatsappMessage(explode(' ', trim($user->name))[0]))
                    : null,
            ];
        })->sortBy('name')->values()->all();
    }
}
