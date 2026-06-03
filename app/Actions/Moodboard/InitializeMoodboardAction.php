<?php

declare(strict_types=1);

namespace App\Actions\Moodboard;

use App\Models\Moodboard;
use App\Models\User;

final class InitializeMoodboardAction
{
    public function execute(User $user): Moodboard
    {
        $moodboard = Moodboard::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'Moodboard Pernikahan']
        );

        return $moodboard->load('items');
    }
}
