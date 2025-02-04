<x-mail::message>
    
    @foreach ($data as $date => $events)
        <div>
            <h2>
                {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
            </h2>
            <hr>
            <ul style="display: grid; gap: 25px;">
                @foreach ($events as $event)
                    <li>
                        <strong > {{ $event['name'] }} </strong>
                        <br>
                        <span>Room: {{ $event['room'] }}</span>
                        <br>
                        <span>Teacher: {{ $event['teacher'] }}</span>
                        <br>
                        <span>Time: 
                            {{ \Carbon\Carbon::parse($event['time_start'])->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($event['time_end'])->format('H:i') }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach

</x-mail::message>