<?php

namespace Rezadaulay\FilamentWhatsappNotification\Enums;

enum WhatsappNotificationStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Sent = 'sent';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
