<?php

namespace App\Http\Controllers;

use App\Services\CardReader;
use Inertia\Inertia;
use Inertia\Response;

class CardController extends Controller
{
    public function __construct(private CardReader $cardReader) {}

    public function index(): Response
    {
        $cardState = $this->cardReader->read();
        $diff = $this->cardReader->diff();

        return Inertia::render('Card/Index', [
            'card' => $cardState,
            'diff' => $diff,
        ]);
    }
}
