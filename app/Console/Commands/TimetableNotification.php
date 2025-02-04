<?php
 
namespace App\Console\Commands;
 
use Illuminate\Console\Command;
use Carbon\CarbonImmutable;
 
 
use Illuminate\Support\Facades\Http;

use App\Mail\Timetable;
use Illuminate\Support\Facades\Mail;
 
class TimetableNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:timetable-notification';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
 
    /**
     * Execute the console command.
     */
    public function handle()
{
    //https://tahvel.edu.ee/hois_back/timetableevents/timetableByGroup/38?from=2023-10-30T00:00:00Z&studentGroups=5901&thru=2023-11-05T00:00:00Z
    $response = Http::get('https://tahvel.edu.ee/hois_back/timetableevents/timetableByGroup/38', [
        'from' => now()->startOfWeek()->toIsoString(),
        'thru' => now()->endOfWeek()->toIsoString(),
        'studentGroups' => '7596',
    ]);
 
    $data = collect($response->json()['timetableEvents'])
        ->groupBy(fn($entry) => CarbonImmutable::parse(data_get($entry, 'date'))->format('Y-m-d'))
        ->transform(function($group){
            $entries = $group->map(fn($entry) => [
                'name' => data_get($entry, 'nameEt'),
                'date' => CarbonImmutable::parse(data_get($entry, 'date'))->format('Y-m-d'),
                'room' => data_get($entry, 'rooms.0.roomCode'),
                'teacher' => data_get($entry, 'teachers.0.name'),
                'time_start' => data_get($entry, 'timeStart'),
                'time_end' => data_get($entry, 'timeEnd'),
            ]);
            return $entries->sortBy('time_start')
            ->values();
        });
        
        //return $data;

        Mail::to('test@test.ee')->send(new Timetable($data));
}
 
}