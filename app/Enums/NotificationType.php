<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    case GuestMessageCreated         = 'guest_message.created';
    case RsvpCreated                 = 'rsvp.created';
    case GuestImportCompleted        = 'guest_import.completed';

    case TransactionPaid             = 'transaction.paid';
    case TransactionFailed           = 'transaction.failed';
    case SubscriptionExpiringSoon    = 'subscription.expiring_soon';
    case SubscriptionExpired         = 'subscription.expired';

    case GiftReceived                = 'gift.received';
    case GiftClaimed                 = 'gift.claimed';
    case GiftExpired                 = 'gift.expired';

    case ChecklistTaskDueSoon        = 'checklist.task_due_soon';
    case WeddingCountdown            = 'wedding.countdown';
    case InvitationViewMilestone     = 'invitation.view_milestone';

    case ProfileIncomplete           = 'profile.incomplete';
    case InvitationUnpublishedNearDday = 'invitation.unpublished_near_dday';
    case QuotaNearLimit              = 'quota.near_limit';
    case TrialEnding                 = 'trial.ending';

    case EngagementInactive          = 'engagement.inactive';
    case PlanUpgradeSuggest          = 'plan.upgrade_suggest';

    case SystemBroadcast             = 'system.broadcast';

    public function category(): NotificationCategory
    {
        return match ($this) {
            self::GuestMessageCreated, self::RsvpCreated, self::GuestImportCompleted
                => NotificationCategory::Guest,
            self::TransactionPaid, self::TransactionFailed,
            self::SubscriptionExpiringSoon, self::SubscriptionExpired
                => NotificationCategory::Payment,
            self::GiftReceived, self::GiftClaimed, self::GiftExpired
                => NotificationCategory::Gift,
            self::ChecklistTaskDueSoon, self::WeddingCountdown, self::InvitationViewMilestone
                => NotificationCategory::Reminder,
            self::ProfileIncomplete, self::InvitationUnpublishedNearDday,
            self::QuotaNearLimit, self::TrialEnding
                => NotificationCategory::Onboarding,
            self::EngagementInactive, self::PlanUpgradeSuggest
                => NotificationCategory::Engagement,
            self::SystemBroadcast
                => NotificationCategory::System,
        };
    }

    public function translationKey(): string
    {
        return 'notifications.types.' . $this->value;
    }
}
