<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Check subscription expiry and send reminder emails daily at 08:00 WIB (01:00 UTC)
Schedule::command('theday:check-subscription-expiry')->dailyAt('01:00');

// Archive invitations for users whose grace period has fully expired (02:00 WIB / 19:00 UTC prev day)
Schedule::command('invitations:archive-expired')->dailyAt('01:30');

// Sweep abandoned (awaiting_payment >24h) and past-expiry pending gifts daily at 02:00
Schedule::command('gift:sweep-expired')->dailyAt('02:00');

// In-app notifications: time-based publishers
Schedule::command('notifications:check-subscriptions')->dailyAt('06:00');
Schedule::command('notifications:check-checklist-due')->dailyAt('07:00');
Schedule::command('notifications:check-wedding-countdown')->dailyAt('07:15');
Schedule::command('notifications:check-onboarding')->weekly()->mondays()->at('07:30');
Schedule::command('notifications:check-engagement')->weekly()->mondays()->at('07:45');
Schedule::command('notifications:dispatch-broadcasts')->everyMinute();
Schedule::command('notifications:cleanup')->dailyAt('03:00');

