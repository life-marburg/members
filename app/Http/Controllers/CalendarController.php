<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class CalendarController extends Controller
{
    public const CalDAVUrl = 'https://cloud.kolaente.de/remote.php/dav/public-calendars/9K0D76LYSDQXUE8G?export';

    public function getCalDAVCalendarOutput()
    {
        return response(Http::get(self::CalDAVUrl)->body())
            ->header('Content-Type', 'text/calendar;charset=UTF-8');
    }
}
