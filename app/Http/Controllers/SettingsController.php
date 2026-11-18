<?php

namespace App\Http\Controllers;

use App\Models\DatePart;
use App\Models\Milestone;
use App\Models\NamePart;
use App\Models\RecordLabel;
use App\Models\Relationship;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('file_settings.index', [
            'relationships' => Relationship::orderBy('id')->get(),
            'nameParts' => NamePart::orderBy('id')->get(),
            'milestones' => Milestone::orderBy('id')->get(),
            'dateParts' => DatePart::orderBy('id')->get(),
            'recordLabels' => RecordLabel::orderBy('id')->get(),
        ]);
    }
}
