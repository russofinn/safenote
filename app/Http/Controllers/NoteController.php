<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Carbon\Carbon;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {

            $duration = null;

            if ($validated["duration"] != 'immediately') {
                if ($validated["duration"] == 1) {
                    $duration = Carbon::now()->addHour()->toDateTimeLocalString();
                } else {
                    $duration = Carbon::now()->addHours($validated["duration"])->toDateTimeLocalString();
                }
            }

            if (App::environment(['local', 'staging'])) {
                $ciphertext = 'local:v1' . Crypt::encryptString($validated["text"]);
            } else {
                //$ciphertext = Vault::transit($validated->text);
            }

            if (!$ciphertext) {
                return '';
            }

            $note = Note::create([
                'data' => [
                    'ciphertext' => $ciphertext,
                    'self_destruction' => $validated["duration"],
                    'encryption_method' => App::environment(['local', 'staging']) ? 'local' : 'vault'
                ],
                'destruction_time' => $duration
            ]);

            return response()->json([
                'success'   => true,
                'message'   => '',
                'data'      => new NoteResource($note)
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        $current = Carbon::now();

        if ($note->destruction_time) {
            $destruction = Carbon::parse($note->destruction_time);

            if ($destruction->lessThan($current)) {
                return '';
            }
        }

        if ($note->data->encryption_method == 'local') {
            $plaintext = Crypt::decryptString($validated->data->ciphertext);
        } else {
            $plaintext = Vault::transit_decrypt($validated->data->ciphertext);
        }

        if ($note->data->self_destruction == 'immediately') {
            $note->delete();
        }

        return $plaintext;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        //
    }
}
